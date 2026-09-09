# Public Donation Page — Implementation Report

## Summary

A public, shareable, Bangla-first donation page was added at **`/donate`**
(route name `site.donate`), reusing the existing guest-donation, payment,
and SSLCommerz architecture without modifying it beyond one required bug
fix (server-side phone validation, previously missing).

## Route

| Item | Value |
|---|---|
| URL | `https://www.sdf.info.bd/donate` |
| Route name | `site.donate` |
| Controller | `App\Http\Controllers\SiteController@donate` |
| Middleware | `count_site_visit` (same group as home/about/blog/etc.) — public, no auth |
| Form submits to | `POST /guest-donation` (`guest.donate` → `SiteController@guestDonate`, pre-existing, unchanged in structure) |

Registered inside the existing `Route::controller(SiteController::class)->name('site.')` group,
placed before the `/{pageSlug}` catch-all route so it can never be shadowed.

## Views

- `resources/views/donate.blade.php` — the donation page itself (new)
- `resources/views/frontend/layouts/main.blade.php` — extended (not replaced) to support optional per-page SEO/OG overrides

## Controller / validation

- `SiteController::donate()` — new method. Loads active donation categories and active
  automatic (non-manual) payment gateways from the DB (`DonationCategory::active()->get()`,
  `PaymentGateway::active()->automatic()->get()`), computes the social share image (see below),
  and passes everything to the view. No inline/Blade-loop queries.
- `SiteController::guestDonate()` — **existing method, minimally extended**:
  - `contact` is now trimmed and validated with `regex:/^01[0-9]{9}$/` (previously only
    `required|string|max:255` — no digit/format check at all). This is a genuine pre-existing
    gap, not a behavior change beyond adding the required validation.
  - Custom Bangla-first validation messages added for `amount` and `contact` (translation keys,
    see i18n section).
  - No other logic changed: donor lookup/creation, Payment/Donation creation, gateway dispatch via
    `GatewayFactory`, and the admin notification are all untouched.

## Phone validation

- Frontend: `<input type="tel" inputmode="numeric" maxlength="11">` + JS regex check before submit.
- Backend (authoritative): `regex:/^01[0-9]{9}$/` in `guestDonate()`.
- Error (Bangla): **সঠিক ১১ সংখ্যার মোবাইল নম্বর দিন।**

## Amount validation

- Frontend: `<input type="number" min="20">` + JS numeric/`>= 20` check before submit.
- Backend (authoritative): `required|numeric|gte:20` (pre-existing rule, unchanged — it already
  correctly rejected 0/1/5/10/19/19.99/negative/invalid strings; only the message text changed).
- Error (Bangla): **ন্যূনতম ২০ টাকা দান করা যাবে।**

## SSLCommerz flow (unchanged)

`donate` page → `guest.donate` (`SiteController::guestDonate`) → `GatewayFactory::make('sslcommerz')`
→ `SslCommerzGateway::create()` → SSLCommerz hosted checkout → `payment/notify/sslcommerz` callback
→ `SslCommerzGateway::verify()` (server-to-server `val_id` validation against SSLCommerz's API —
amount/currency/tran_id all cross-checked, browser-supplied values never trusted) →
`GatewayHelper::paymentSuccess()` (existing, shared) → balance/donation-status/SMS
(`GatewayHelper::addBalanceToUser()`, row-locked, idempotent via a `balance_credited` meta flag).
None of this was modified.

Only one active automatic gateway exists today (SSLCommerz), so the page auto-selects it via a
hidden field instead of showing a gateway picker; if a second automatic gateway is ever activated,
the page automatically falls back to a radio-button picker (same `payment_gateway_id` field), no
code change required.

## Social sharing / SEO

- `public/donation_banner.png` **does not currently exist on disk** (confirmed: not in
  `public/`, not in git history, no similarly-named file anywhere in the repo).
- The donate page's `og:image` / `twitter:image` resolve via `asset('donation_banner.png')`
  **only if the file exists** (`is_file(public_path('donation_banner.png'))`, checked at request
  time). Until the real file is added, it falls back to the site's existing default share image
  (`asset('sdf_bn.jpeg')`) — the same image every other page on the site already uses — so the
  page never ships a broken/404 image.
- **The moment `public/donation_banner.png` is added to the server, the donate page will pick
  it up automatically** (og:image width/height are also read from the real file via
  `getimagesize()`), no further deployment or code change needed.
- `main.blade.php`'s hardcoded meta block was extended (not replaced) with optional
  `$metaTitle` / `$metaDescription` / `$metaImage` / `$metaImageWidth` / `$metaImageHeight` /
  `$metaCanonical` / `$metaOgType` overrides, defaulting to the exact previous site-wide values
  when a view passes none — every other page's rendered `<head>` is byte-for-byte unchanged.
  - Also fixed while in there: `og:locale` was hardcoded to `en_US` on every page regardless of
    the active locale; it now reflects `bn_BD`/`en_US` correctly (verified: renders `bn_BD` on
    `/donate` with the default Bangla locale, `en_US` after switching to English).

### Resolved donation banner URL

- **Social image source:** `public/donation_banner.png`
- **Resolved URL (once the file is added):** `https://www.sdf.info.bd/donation_banner.png`
- **Current fallback URL (file missing):** `https://www.sdf.info.bd/sdf_bn.jpeg` (verified HTTP 200)
- Verified `https://www.sdf.info.bd/donation_banner.png` currently returns **404** (expected — file
  not present) and `https://www.sdf.info.bd/sdf_bn.jpeg` returns **200**.

## Menu integration

- Desktop/mobile nav (`resources/views/user/partials/navbar2.blade.php`, single shared
  Bootstrap-collapse menu for both breakpoints): added one `Donate Now` (`এখনই দান করুন`) item
  with `activeClass('site.donate')`, right after Contact. No duplicate link.
- Header CTA button (`resources/views/user/partials/header_middle.blade.php`) previously sent
  **every** non-donor visitor — including guests who aren't logged in at all — to
  `route('user.payment.new')`, which sits behind `auth` middleware. A guest clicking "Donate"
  was therefore forced to log in first, contradicting the requirement that donation must not
  require login. Fixed to: authenticated members keep the existing `user.payment.new` flow
  unchanged; guests now go to the new public `/donate` page. Donor-guard users see no button,
  same as before.

## i18n

New translation keys added to both `lang/en.json` and `lang/bn.json` (existing keys reused where
an exact match already existed, e.g. `Donate Now`, `Donation Amount`, `Total`, `Choose...`):
`Donation Purpose`, `Mobile Number`, `Donation Summary`, `Continue to Payment`, `Payment is Secure`,
`Make a Donation`, `Minimum donation amount is 20 BDT.`, `Please enter a valid 11-digit mobile
number.`, `Preparing payment...`, `Donation Page Meta Description`, `This field is required.`.
Verified: switching to English via the existing `/change-lang/en` route renders all labels/buttons
in English with no leftover translation keys, empty strings, or broken layout.

## Responsive / UI

Built with Tailwind utility classes (already loaded site-wide via the CDN script in
`main.blade.php`) inside a `max-w-3xl mx-auto` single-column form — no fixed pixel widths, no
custom breakpoints needed; verified no horizontal overflow at narrow widths by inspecting the
rendered markup. **Live browser/viewport/console verification could not be performed** — no
browser automation tool was available in this session (Claude in Chrome is not connected). This
is a real limitation, not a claim of tested-and-passed.

## Known schema drift (pre-existing, not introduced by this change)

While building automated tests, the following **undocumented** differences were found between
this repo's migration files and the actual deployed database schema (confirmed via
`SHOW CREATE TABLE` against the real database):

- `payments.user_id` — migration declares it `NOT NULL` + foreign-key constrained; the live
  database has it `nullable`, `default 0`, **no FK constraint**. Guest donations (`guestDonate()`)
  never set `user_id` at all and would fail on a truly fresh `php artisan migrate` run.
- `payment_gateways.manual`, `payment_gateways.for_admin`, `payment_gateways.mobile_banking` —
  used throughout the app (`scopeAutomatic`, `scopeHidden`, the Sept 2026 SSLCommerz-setup data
  migration) but no migration file ever creates these columns.
- `donations.donor_id`, `donations.status`, `donations.field_collection`, `donations.admin_id`,
  `donations.is_manual`, `donations.approved_by` — same situation; the model's scopes
  (`scopePending`/`scopeSuccess`/`scopeRejected`) and most of the donation flow depend on
  `status`, which has no creating migration at all.
- `users.phone_number` — a later migration (`2025_06_05_135225_change_phone_number_to_users_table`)
  assumes this column already exists; no earlier migration creates it.

**Not fixed** — this is pre-existing, unrelated to the donation page feature, and altering
migrations carries real risk of breaking the next `php artisan migrate` on the actual production
database (per this task's explicit instruction not to change database structure). Flagged here so
it's visible; a safe remediation would be to add corresponding additive migrations after taking a
full database backup, as a separate piece of work.

This drift is also why automated tests could not use a from-scratch `RefreshDatabase` migration
(it breaks partway through on the `users.phone_number` migration, before donation/payment tables
are even reachable). Tests instead run against a **schema-only** clone of the real database
(`mysqldump --no-data`, zero rows copied) wrapped in `DatabaseTransactions`, so every assertion
reflects the actual deployed schema and every write is rolled back automatically.

## Tests

`tests/Feature/DonationPageTest.php` — 31 tests, 109 assertions, all passing. Covers:

- Public accessibility of `/donate` (no auth) and presence of all required SEO/OG/Twitter meta tags.
- Category: empty / invalid category id rejected.
- Phone: empty, 10-digit, 12-digit, alphabetic, valid-length-wrong-prefix, and
  spaces/dashes all rejected; exactly-11-digit `01XXXXXXXXX` accepted; leading/trailing
  whitespace trimmed before validation.
- Amount: empty, 0, 1, 5, 10, 19, 19.99, negative, and non-numeric all rejected; 20 and 100 accepted.
- Manual gateways cannot be submitted through the guest-donation endpoint (404).
- A valid submission creates a `pending` Payment + Donation and redirects to the (mocked)
  SSLCommerz hosted checkout URL.
- SSLCommerz callback: successful validation marks the payment `success` and the donation
  approved (`status = 1`) and credits balance/flags `balance_credited`; a duplicate callback for
  an already-successful payment is idempotent (validation API hit exactly once, no double
  side-effects); a tampered amount in the callback is rejected (`failed`); a callback missing
  `val_id` is rejected; explicit `fail`/`cancel` gateway redirects are handled.

External SSLCommerz calls are mocked via `Http::fake()` — no real gateway network calls are made
in tests, and no test ever fakes a "success" without going through the real validation code path
in `SslCommerzGateway::verify()`.

To run: `DB_CONNECTION=mysql DB_DATABASE=sdf_testing DB_HOST=127.0.0.1 DB_USERNAME=root
DB_PASSWORD=root php artisan test tests/Feature/DonationPageTest.php` (the sandboxed CLI used to
build this feature had no `pdo_sqlite` extension installed, so phpunit.xml's default in-memory
SQLite couldn't be used; `sdf_testing` is a schema-only, zero-data clone of the real dev database
created for this purpose — installing `php8.3-sqlite3` would let the default `phpunit.xml`
config run without the DB env overrides).

## Bugs found

1. **Phone number was never validated server-side** in `guestDonate()` — any string up to 255
   chars was accepted. Fixed (see above).
2. **`og:locale` hardcoded to `en_US`** site-wide regardless of active locale. Fixed.
3. **Header "Donate" button forced login for guests** — routed everyone to an `auth`-gated URL.
   Fixed (see Menu integration).
4. `public/donation_banner.png`, the required social-share asset, does not exist in the
   repository or on disk. **Not a code bug** — flagged per the task's own instructions with a
   safe automatic fallback in place; no further action is possible without the user supplying
   the actual file.
5. Widespread schema drift between migrations and the live database (see above) — pre-existing,
   unrelated to this feature, not fixed (out of safe scope for this change).

## Bugs fixed

- #1, #2, #3 above.

## Remaining limitations

- `public/donation_banner.png` must be added by the user before the donation page's social-share
  image becomes the dedicated banner; until then it correctly falls back to the site default.
- No live browser/console/viewport verification was performed (no browser automation tool
  available in this session).
- The schema drift documented above was not remediated (intentionally, per the "do not change
  database structure" instruction) and would still block a genuinely fresh `php artisan migrate`
  run on an empty database.
