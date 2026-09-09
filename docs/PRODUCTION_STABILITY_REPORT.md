# Production Stabilization Report

Scope: stabilize and optimize the existing SDF Laravel application without
changing donation/payment/accounting business logic, public URL behavior, or
existing data. Full detail lives in the companion docs in this folder
(`ROUTE_STABILITY_AUDIT.md`, `TIMEZONE_AUDIT.md`, `TRANSLATION_AUDIT.md`,
`QUERY_OPTIMIZATION_REPORT.md`); this file is the executive summary.

A git repository was initialized at the start of this pass (none existed
before) specifically so every change here is committed incrementally and
reversible — see `git log` for the exact sequence and full commit messages.

## 1. Duplicate routes fixed
Two routes in `routes/admin.php` (bare `panel22` root and `panel22/dashboard`)
both carried the name `dashboard`, colliding on `admin.dashboard` once the
group's name prefix applied — this is what broke `php artisan optimize`.
Fixed by giving the root route its own name (`admin.root`) instead of relying
on Laravel's undocumented anonymous-route name inheritance, which testing
showed would have caused a second, latent collision. Full duplicate-route
audit across all 227 routes found zero remaining duplicate names or
duplicate URI+method pairs. See `ROUTE_STABILITY_AUDIT.md`.

## 2. Route serialization result
`php artisan optimize` now completes all four stages (config, events, routes,
views) successfully. Re-verified after every subsequent change in this pass.

## 3. Timezone fixes
`Asia/Dhaka` is now the authoritative fallback (previously silently fell back
to UTC if the DB setting was ever missing/blank), with validation against
real timezone identifiers. `.env` now sets `APP_TIMEZONE=Asia/Dhaka`
explicitly. Fixed a real UTC-double-conversion bug in the manual donation
entry form's JavaScript that shifted its date field by Bangladesh's +6 offset.
Confirmed the database already stores Asia/Dhaka wall-clock timestamps
consistently (no UTC/local mixing found) — no timestamp data was converted or
altered. See `TIMEZONE_AUDIT.md`.

## 4. Language/i18n fixes
`APP_LOCALE` changed to `bn` (Bangla-first default); `APP_FALLBACK_LOCALE`
kept as `en` so Laravel's built-in validation/auth strings (which only exist
as English array files) still render correctly instead of raw keys. See
`TRANSLATION_AUDIT.md`.

## 5. Translation coverage
96 keys used in code were missing from `lang/bn.json` entirely (now 0 missing);
227 more were missing from `lang/en.json` (now 0 missing, added as identity
entries — functionally these already rendered correct English via Laravel's
raw-key fallback, since the key *is* the English source text). All bn.json
JSON, en.json JSON, and hi.json JSON validated as syntactically correct
before and after. See `TRANSLATION_AUDIT.md`.

## 6. Blog reliability fixes
- Blog list queries pulled every column (including the full post `body` and
  `seo_content` JSON) via an implicit `SELECT *`; scoped to only the columns
  the list views render.
- Blog list/detail image tags rendered a broken-image icon when a post had no
  image or its file was missing; added a local `no-image.png` fallback plus
  an `onerror` handler in all three blog templates.
- No N+1 or missing-eager-load issue found in the blog controller itself
  (`blogs()` was already paginated with no relation access in the view).

## 7. 419 root causes/fixes
No live 419-causing bug was found in the CSRF configuration itself — CSRF
remains fully enforced everywhere except the two legitimate, already-exempted
webhook endpoints (`payment.notify`, `receive-sms-automation`), both verified
still CSRF-exempt after changes. One misconfiguration was found and corrected
regardless: `bootstrap/app.php`'s CSRF except-list had a route *name*
(`payment.notify`) where a URI pattern was required, making that particular
entry silently inert (harmless only because the route's own
`withoutMiddleware()` call already handled the exemption). Also added a
custom, branded, translated `resources/views/errors/419.blade.php` (previously
fell back to Laravel's generic default) with a "go back and resubmit" message
appropriate to what actually causes this error — an expired session on a
long-open form.

## 8. 500 root causes/fixes
The most severe finding of this entire pass: `storage/logs/laravel.log` had
grown to **~493MB** from a fatal `Cannot redeclare handleResize()` error
recurring **every 60 seconds**, confirmed via log timestamps spanning at least
06:24:43 to 07:19:43. Root cause: most functions in `app/helpers.php` were
already wrapped in `function_exists()` guards, but ~19 (including the one
being hit) were not, and something re-includes this file within the same PHP
process (reproduced directly — requiring the file twice in one process is
fatal without the guard, clean with it). Wrapped every remaining unguarded
function with the same existing guard convention already used elsewhere in
the file, and fixed two guard conditions that checked the wrong name entirely
(a second, related latent bug). Verified via direct reproduction (fatal before,
clean after) and by observing multiple real 60-second cycles pass with zero
further log entries after the fix. Also switched `LOG_STACK` from `single` to
`daily` (already-configured, 14-day retention) so a future recurrence rotates
instead of growing one file without bound. Added branded 429/503 error pages
alongside the 419 page above, matching the existing 404/500 visual style.

## 9. Query optimizations
AdminHome dashboard's donation-category breakdown N+1 fixed (21→17 total
queries with 5 categories, scaling with category count); blog list `SELECT *`
narrowed to needed columns; `generalSetting()` given a per-request memo on top
of its existing forever-cache. Full detail with before/after `EXPLAIN` output
in `QUERY_OPTIMIZATION_REPORT.md`.

## 10. Index optimizations
One additive, reversible migration: `donations` gets a composite
`(status, created_at)` index (replacing a now-redundant single-column
`status` index) eliminating a filesort on the approved/pending/rejected
donation list pages; `site_visits` gets a standalone `visit_date` index
(kept the existing `(ip, visit_date)` composite, which a different, still-live
query needs) eliminating a full ~34k-row index scan on the admin dashboard's
visit-count widgets. Both changes verified against live `EXPLAIN` output
before and after.

## 11. Cache optimizations
`SystemHelper::clearCache()` — called on every general/notification/
configuration settings save — ran a full `Artisan::call('optimize:clear')`
(wiping config, route, view, and compiled caches plus the entire cache store)
just to invalidate one cached settings row. Narrowed to
`Cache::forget('general-setting')`, so a settings save no longer forces every
subsequent request on the site to recompile config/routes/views from cold.

## 12. Asset/network fixes
Found and fixed: one hardcoded `http://` self-link in the user-area footer
(now `https://`, avoiding a needless downgrade off an HTTPS page); two
unauthenticated GET routes (`/sync`, `/clear`) that performed outbound HTTP
calls / full cache invalidation with no access control, now behind the
existing `admin` auth middleware; `sitemap.xml` and the real (but corrupted
and misplaced) `robots.txt` were sitting in the project root instead of
`public/`, so neither was actually being served in production — both are now
correctly served from `public/`, and the corrupted robots.txt (which had a
full sitemap XML dump accidentally appended after its directives) was
rewritten with correct syntax.

## 13. JS/jQuery fixes
Fixed a real stuck-UI bug in the manual payment-gateway confirmation form
(`user/manual_gateway.blade.php`): it hid the form and showed "processing,
you will be redirected shortly" immediately on submit, then assumed the
response would always be parseable JSON. The server's validation-error paths
return an HTML redirect instead, which threw inside the JSON parse and was
only logged to the browser console — a real user hitting a validation error
here was left staring at "processing" forever with no visible feedback. Fixed
client-side only (no server/business-logic change): checks content-type
before parsing, and on any failure restores the form and shows a translated,
visible error message. Also fixed the timezone-conversion bug in the manual
donation modal (see item 3) and pinned the admin dashboard's live clock
widget to Asia/Dhaka (see `TIMEZONE_AUDIT.md`).

## 14. SEO compatibility checks
Verified after all changes: canonical tags, Open Graph tags, and page titles
on the blog detail page render correctly and unchanged; no public route URI
changed (only `/sync` and `/clear`, neither linked from any page nor plausibly
indexed, gained an auth requirement); `robots.txt`/`sitemap.xml` fixed to
actually be reachable (see item 12) — this is a net SEO improvement, not a
regression risk, since they were previously unreachable/wrong. Noted for the
team, not changed: the sitemap's URLs use the bare domain
(`https://sdf.info.bd/...`) while `generalSetting('app_url')` is configured as
`https://www.sdf.info.bd` — worth confirming which is canonical and that a
redirect exists between them, which is a DNS/server-config decision outside
this pass.

## 15. Tests
No functional test suite exists in this codebase beyond Laravel's default
scaffold (`tests/Unit/ExampleTest.php`, `tests/Feature/ExampleTest.php` — two
trivial stock tests, no SDF-specific coverage). Running `php artisan test`:
the Unit example passes; the Feature example fails with
`could not find driver (Connection: sqlite, ...)` because the `pdo_sqlite`
PHP extension is not installed on this machine (`phpunit.xml` configures the
test environment to use an in-memory SQLite database, which is standard for
Laravel but requires that extension). This is a pre-existing environment gap,
unrelated to any change made in this pass — confirmed by checking
`php -m | grep sqlite` (empty) and that installing it requires
`sudo apt-get install php8.3-sqlite3`, which needs a password this session
does not have. **Documented as an exact, named blocker rather than marked as
a false pass.** No application code change can fix a missing system PHP
extension.

## 16. Build
`npm install` + `npm run build` (Vite) both succeed cleanly (58 modules
transformed, ~505ms). Note: the built Vite/Tailwind assets are only
referenced by Laravel's unused stock `welcome.blade.php` — the site's real
pages (`home.blade.php` and friends) use a separate, pre-existing static
`assets/` directory with plain `<link>`/`<script>` tags, not `@vite`. This is
an existing architectural choice (a purchased/downloaded HTML template's
asset pipeline), not something broken by this pass or something this pass
changed.

## 17. Remaining risks / recommendations for the team
- **No application-specific automated test coverage exists.** Every
  verification in this pass was done via `tinker`, `EXPLAIN`, direct HTTP
  smoke-testing against a local `php artisan serve`, and manual reproduction
  of the fatal-error bug — real, but not regression-proof going forward.
  Recommend the team add feature tests for at least the donation/payment flow
  once the `pdo_sqlite` extension gap is closed.
- **`pdo_sqlite` PHP extension missing** on this machine — blocks the one
  real Feature test that exists. Fix: `sudo apt-get install php8.3-sqlite3`
  (or point `phpunit.xml`'s test `DB_CONNECTION` at a dedicated MySQL test
  database instead of sqlite, if sqlite isn't desired in this environment).
- **Sitemap/app_url domain mismatch** (`sdf.info.bd` vs `www.sdf.info.bd`) —
  confirm canonical domain and DNS/redirect setup; not fixable from inside
  the application.
- **`ReportController::accountSummary()`** runs several sequential aggregate
  queries computing real financial totals; deliberately left unconsolidated
  given the risk of an arithmetic mistake for a marginal performance gain —
  flagged for the team's awareness, not fixed.
- **A source-code-only Bangla comment corruption** (mojibake / double-encoded
  UTF-8) was found in `resources/views/home.blade.php` inside a JS comment.
  Zero functional impact (comments aren't executed or displayed), left
  untouched rather than guess-reconstruct the original text.
