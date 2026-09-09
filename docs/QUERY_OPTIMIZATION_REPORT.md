# Query Optimization Report

All findings below were verified against the live database schema and real
`EXPLAIN` output — nothing here is a blind guess.

## 1. AdminHome dashboard: donation-category N+1 (fixed)

**File:** `app/Livewire/AdminHome.php`

**Before:**
```php
$donationChart = DB::table('donations')
    ->select('donation_category_id', DB::raw('SUM(amount) as total'))
    ->groupBy('donation_category_id')
    ->get()
    ->map(function ($row) {
        $category = DonationCategory::find($row->donation_category_id); // 1 query per row
        return ['label' => $category?->name ?? 'Category ' . $row->donation_category_id, 'total' => $row->total];
    });
```
One query per distinct `donation_category_id` inside the `map()` — classic
N+1, running on every dashboard load (and Livewire may re-render this
multiple times per page interaction).

**After:** batch-fetch every needed category name in one `whereIn()` +
`pluck('name', 'id')` call before mapping. Same output shape and values,
verified via tinker (category names resolved correctly, not the
`'Category N'` fallback).

**Measured:** with `DB::enableQueryLog()`, the whole `AdminHome::render()`
call dropped from **21 to 17** total queries with the current 5 distinct
donation categories in the database (the category-lookup section alone went
from `1 + 5 = 6` queries to `1 + 1 = 2`). This scales with the number of
donation categories — the gap only grows as more are added.

## 2. `donations`: missing filesort-avoiding composite index (fixed)

**Query pattern:** `Donation::success()->latest()->paginate()` (and the
`pending()`/`rejected()` equivalents) — used by the admin approved/pending/
rejected donation list pages. Translates to
`WHERE status = ? ORDER BY created_at DESC LIMIT n`.

**Before** (existing single-column index only):
```
EXPLAIN SELECT * FROM donations WHERE status = 1 ORDER BY created_at DESC LIMIT 15;
type: ref | key: donations_status_index | rows: 3392 | Extra: Using filesort
```
MySQL can find the ~3.4k matching rows via the status index but still has to
sort all of them by `created_at` afterward.

**Fix:** additive migration
(`database/migrations/2026_09_05_010000_add_performance_indexes_for_reports_and_dashboard.php`)
adds a composite `(status, created_at)` index and drops the now-redundant
single-column `donations_status_index` (any query that only filters on
`status` is still fully served by the composite's leftmost column, so keeping
both would just be a second index to maintain on every write for no benefit).

**After:**
```
type: ref | key: donations_status_created_at_index | rows: 3392 | Extra: Backward index scan
```
No filesort — MySQL walks the index in already-sorted order.

## 3. `site_visits`: dashboard visit counts had no usable index (fixed)

**Query pattern** (`AdminController`, dashboard "today/this week/this month/
this year" visit widgets): `WHERE visit_date = ?` / `WHERE visit_date BETWEEN ? AND ?`.

**Before:** the only index touching `visit_date` was composite `(ip, visit_date)`,
which is unusable as a seek when `ip` isn't part of the filter:
```
EXPLAIN SELECT COUNT(*) FROM site_visits WHERE visit_date = CURDATE();
type: index | key: site_visits_ip_visit_date_index | rows: 34416 | Extra: Using where; Using index
```
`type: index` here means a full scan of the entire index (all ~34k rows at
the time of testing, and growing with every page view) — not a seek.

**Fix:** the same migration adds a standalone `visit_date` index. The `(ip,
visit_date)` composite is **kept**, unchanged — `SiteVisitMiddleware` runs
`SiteVisit::where('ip', $ip)->where('visit_date', $today)->exists()` on every
single page view to dedupe visits, and that access pattern still needs the
composite.

**After:**
```
type: ref | key: site_visits_visit_date_index | rows: 1 | Extra: Using index
```

Both `whereMonth()`/`whereYear()` calls in the same widget (used for the
month/year counters) wrap the column in a function and therefore still cannot
use any index on `visit_date` directly — this is a MySQL/SQL limitation
(function-wrapped columns aren't sargable), not something an index can fix.
Rewriting those two specific counters to use `whereBetween()` date-range
bounds instead would make them index-friendly too, but that changes the
literal calculation boundaries (calendar-month vs. rolling — a business-logic
nuance), so it was left as-is rather than risk changing what "this month"
means without being asked.

## 4. `generalSetting()` re-reads the cache store on every call (fixed)

`app/helpers.php`'s `generalSetting()` already avoided hitting the database
via `Cache::rememberForever('general-setting', ...)`, but on the `file` cache
driver (this project's configured `CACHE_STORE`) that still means a disk read
on *every single call* — and it's called 50+ places across controllers,
`AppServiceProvider::boot()`, and Blade views, often several times in one
request. Added a per-request `static` memo on top of the existing cache call,
so the cache store is consulted at most once per request regardless of how
many times `generalSetting()` is invoked. Verified no stale-value regression:
each HTTP request is a fresh PHP process (no Octane/persistent workers in
this stack), so the static resets naturally every request and still observes
a settings change made via `Cache::forget('general-setting')` in the request
that saved it.

## 5. Verified as already correct — no change made

- **`DonorController::list()` / `downloadMaxDonors()`**: uses
  `withSum()`/`withCount()`/`with('lastDonation')` — all properly eager-loaded,
  no N+1. `Donor::lastDonation()` uses Laravel's `latestOfMany()`, a single
  correlated-subquery relation, not a per-row query.
- **`Donor::donations()` joins on `phone_number` while `lastDonation()` joins
  on the default `donor_id` foreign key** — different keys, but both are
  reliable: checked live data, `donations.donor_id` is populated on all 3,400
  rows (0 nulls). Not a bug; left untouched since changing a working
  relationship without a verified defect is explicitly out of scope.
- **`ReportController::accountSummary()`**: runs roughly six separate
  aggregate queries (gateway sums, mobile-banking total, expenses, SDF loan,
  bank balance, cash donations) to compute the account summary. This is not
  N+1 — it's a small, fixed number of queries, not one per row — and it feeds
  directly into real financial totals (`netBalance`, `finalB`, etc.).
  Consolidating these into fewer queries was considered and rejected: the
  performance gain would be marginal (a handful of fast aggregate queries),
  while the risk of subtly changing which rows get summed into which total is
  exactly the kind of accounting-logic risk this pass was told to avoid.
- **Admin sidebar counts** (`AppServiceProvider::boot()`, `View::composer`
  for `admin.partials.sidebar`): two lightweight `COUNT()` queries
  (`emailUnverifiedUsers`, `newUsers`) run once per response, not per sidebar
  item — no loop, no N+1 found in the sidebar Blade itself.

## 6. Blog list pages: implicit `SELECT *` (fixed)

`SiteController::blogs()` and the homepage's `frontend/sections/blog.blade.php`
widget both ran `BlogPost::...->paginate()`, pulling every column — including
`body` (the full rich-text post content) and `seo_content` (a JSON blob) — for
every row on every page of the list, even though the list views only render
`id`, `title`, `slug`, `image`, and `created_at`. Scoped both queries to
`select(['id', 'title', 'slug', 'image', 'created_at'])`. No visual or
functional change; verified both pages still render correctly.

## Migration summary

`database/migrations/2026_09_05_010000_add_performance_indexes_for_reports_and_dashboard.php`
— additive and reversible (`down()` restores the prior index layout exactly):
- `donations`: add `(status, created_at)`, drop `status`-only.
- `site_visits`: add `visit_date`-only (composite with `ip` untouched).

No data was migrated, copied, or altered — index-only DDL.
