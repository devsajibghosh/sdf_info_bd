# Timezone Audit — Bangladesh (Asia/Dhaka) First

## Where the timezone is actually decided

1. `config/app.php` — `'timezone' => env('APP_TIMEZONE', 'Asia/Dhaka')`. Already
   defaulted to Asia/Dhaka; `.env` now sets `APP_TIMEZONE=Asia/Dhaka` explicitly
   too, so the intent is documented rather than implicit.
2. `app/Providers/AppServiceProvider.php` (`boot()`, inside `$this->app->booted()`)
   overrides that on every request with the admin-configurable
   `generalSetting('timezone')` value, via `date_default_timezone_set()` +
   `config(['app.timezone' => ...])`. This runs before the request is routed,
   so every `now()`, `Carbon::now()`, and Eloquent timestamp cast in that
   request uses the resolved timezone.

## Bug found and fixed

**Before:**
```php
$timezone = generalSetting('timezone') ?? 'UTC';
date_default_timezone_set($timezone);
```
If the `general_settings` row's `timezone` column were ever missing, blank, or
an empty string (`??` only catches `null`, not `''`), the app would silently
fall back to **UTC** — shifting every displayed date/time by 6 hours — or, for
an empty string specifically, call `date_default_timezone_set('')`, which PHP
accepts with only a warning while leaving the actual default timezone
unchanged, while `config('app.timezone')` would still get set to the empty
string, later crashing any code that constructs a `DateTimeZone` from it.

**After:**
```php
$timezone = generalSetting('timezone') ?: config('app.timezone', 'Asia/Dhaka');

if (!in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
    $timezone = 'Asia/Dhaka';
}

date_default_timezone_set($timezone);
config(['app.timezone' => $timezone]);
```
Falls back to Asia/Dhaka (via `config('app.timezone')`, itself now backed by
`APP_TIMEZONE` in `.env`) instead of UTC, and validates the value against the
real list of PHP timezone identifiers before using it, so a bad or missing DB
value can no longer silently corrupt every date on the site or crash Carbon.

**Verified live database state** (so the fix above is a safety net, not a
correction of already-wrong data): `general_settings.timezone` is currently
`'Asia/Dhaka'`, and comparing recent row timestamps (`donations`, `site_visits`,
`blog_posts`) against `now()` at the time of testing showed no UTC/local
mismatch — the database is consistently storing Asia/Dhaka wall-clock values,
not UTC. **No existing timestamp data was touched or converted.**

```
$ php artisan tinker
>>> config('app.timezone')
"Asia/Dhaka"
>>> now()
"2026-09-05 07:05:47"
>>> now()->timezone('Asia/Dhaka')
"2026-09-05 07:05:47"
```

## Real bug found in a manual-entry form (JavaScript double-conversion)

`resources/views/admin/donation/manual_submissions.blade.php` populates the
"created at" field of the manual-donation-entry modal with:
```js
$('#created_at').val(new Date().toISOString().slice(0,16));         // new entry
$('#created_at').val(new Date(created).toISOString().slice(0,16));  // editing
```
`toISOString()` always converts to **UTC**. A `datetime-local` input expects
the browser's local wall-clock string, so for an admin in Bangladesh (UTC+6)
this silently shifted both the "new" default value and the "edit" prefill
value back by 6 hours — e.g. entering a donation at 1:08 PM Dhaka time would
show 7:08 AM in the field, and resubmitting without noticing would record the
wrong `created_at`.

**Fix:** build the local `YYYY-MM-DDTHH:mm` string from the `Date` object's
local getters (`getFullYear`/`getMonth`/`getDate`/`getHours`/`getMinutes`) for
new entries, and for edits, reformat the server's `Y-m-d H:i:s` string
(already Asia/Dhaka wall-clock) directly via string manipulation instead of
routing it through `Date`/UTC at all. No timezone conversion happens now where
none should.

## Live clock widget pinned to Bangladesh time

`resources/views/admin/donation/list.blade.php` has a "real-time-clock"
widget built from `toLocaleDateString`/`toLocaleTimeString`, which use the
*viewer's device* timezone by default. Added `timeZone: 'Asia/Dhaka'` to both
`Intl` option objects so the panel clock stays authoritative regardless of an
individual admin's own device clock/timezone settings.

## Blog dates

Blog list and detail pages both already use the centralized
`System::getDateTime()` helper (`app/Helpers/SystemHelper.php`), which calls
`Carbon::parse($dateTime)->locale(app()->getLocale())->format($format)` with
no explicit timezone override — so once the app-level timezone is corrected
(above), blog publish dates render in Asia/Dhaka automatically, using the raw
database value with no additional conversion. Verified against a real post:

```
DB created_at: 2026-07-07 13:08:xx
Rendered on page: 07/07/2026 01:08:pm
```

**Note (not changed):** `->locale(app()->getLocale())` only affects
`translatedFormat()`/day-and-month-name output; the default format string used
here (`d/m/Y h:i:a`) is a plain `->format()` call, which always renders
ASCII digits regardless of locale. The numeric `07/07/2026 01:08:pm` style is
consistent with every other page using this same helper (donation receipts,
admin lists, etc.), and matches the numeric-date convention already used
elsewhere in the UI, so this was left as-is rather than switched to Bangla
numerals — that would be a visible design change, not a timezone bug fix.

## No hardcoded UTC found elsewhere

Searched the whole `app/` and `resources/views/` trees for `setTimezone`,
`->tz(`, and literal `'UTC'`/`"UTC"` strings: none found outside the one fixed
in `AppServiceProvider`. There is no scattered/duplicated timezone-formatting
logic to centralize beyond what `System::getDateTime()` already provides.

## Cache interaction

`generalSetting('timezone')` is served from the same `Cache::rememberForever('general-setting', ...)`
that Part 5 below addresses — saving General Settings now correctly invalidates
just that cache key, so a timezone change made in the admin panel takes effect
on the very next request (previously it also worked, just via a much more
expensive full `optimize:clear`).
