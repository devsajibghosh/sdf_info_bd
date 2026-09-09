# Translation / i18n Audit

## Architecture (as found, not changed)

- Laravel's JSON translation system (`lang/bn.json`, `lang/en.json`,
  `lang/hi.json`), where the **key is the literal English source string**
  used in `__()`/`@lang()` calls and the **value is that locale's translation**
  (for `en.json`, the value is normally identical to the key — an "identity"
  translation). A separate `lang/en/*.php` directory holds Laravel's own
  array-based framework strings (`auth.php`, `validation.php`, `passwords.php`,
  `pagination.php`) — there is no `lang/bn/` equivalent of these.
- Active locale is resolved per-request in `App\Http\Middleware\SetLocale`:
  `Session::get('locale', config('app.locale'))`, then `App::setLocale($locale)`.
  This is a single, existing mechanism — no parallel i18n system was built.
- Switching language is `GET /change-lang/{lang}` → `SiteController::changeLang()`
  → `session(['locale' => $lang])` → `back()`, preserving the page the user was
  on. This already worked correctly and was not changed.

## Bangla-first change

- **`.env`: `APP_LOCALE` changed from `en` to `bn`.** This is the default when
  no session locale is set yet (a fresh visitor), making Bangla the site's
  actual default language as required.
- **`APP_FALLBACK_LOCALE` stays `en`** (already was). This is deliberate, not
  an oversight: Laravel's built-in validation/auth/pagination strings only
  have English array files (`lang/en/*.php`) — there is no `lang/bn/` version.
  With `bn` as the fallback too, a missing bn.json key would fall back to
  itself and still render the raw key; with `en` as the fallback, a missing
  Bangla translation degrades to readable English instead of a raw key or
  Laravel's internal string ID (e.g. `validation.required`). Verified:
  ```
  >>> app()->setLocale('bn');
  >>> trans('validation.required', ['attribute' => 'name']);
  "The name field is required."   // correct English fallback, not "validation.required"
  ```

## Coverage audit

Extracted every `__()` and `@lang()` call across `app/` and `resources/`
(819 keys, including calls with `:placeholder` parameters, not just the
simple-string calls the project's own `extract_translations.php` script
catches) and diffed against `lang/bn.json` and `lang/en.json`.

| | before | after |
|---|---|---|
| Keys used in code but missing from **bn.json** | 96 | **0** |
| Keys used in code but missing from **en.json** | 227 | **0** |
| `bn.json` total keys | 748 | 854 |
| `en.json` total keys | 627 | 863 |

**Why "missing from en.json" isn't the same severity as "missing from
bn.json":** because the JSON key *is* the English source text, a missing
`en.json` entry still renders correctly — Laravel returns the untranslated key
verbatim, which for the English locale is already correct English. A missing
**bn.json** entry, by contrast, shows raw English text on an otherwise-Bangla
page, which is the real "Bangla-first" defect. All 96 of those were fixed with
context-appropriate Bangla translations (this is a donation/NGO platform —
donor lists, committees, expense/finance reports, manual payment gateways,
etc. — translations were chosen to match that domain). The 227 English-side
entries were backfilled as identity entries (`value == key`) purely for
completeness/explicitness; this changes no rendered output.

A few of the 96 keys carried source typos baked into the `__()` call itself
(e.g. `__('Descriptoin')`, `__('Committe Deleted')`, `__('Data deleted succesfully')`).
The JSON **key** has to match the literal string used in code, so those typos
were kept as keys, but the **English value** was given the corrected spelling
(`"Descriptoin": "Description"`), which actually fixes a real, previously
untranslated (and therefore literally-typo'd-on-screen) piece of English UI
text — with zero code changes.

One key could never have been translated at all: `admin/website/pages/edit.blade.php`
had `@lang('Drag sections from the right panel here and arrange them in the\n<130 spaces>desired order.')` —
a literal newline plus ~130 spaces of indentation baked into the translatable
string. Normalized it to a single-line string in the Blade file (whitespace
only, no wording change) so it can actually be matched and translated going
forward, and added its translation.

## New user-facing strings introduced by this pass

A handful of new strings were added because of other fixes in this pass (the
419/429/503 error pages, and a new client-side error message for the manual
payment form's stuck-UI fix) — all given both Bangla and English entries at
the same time they were introduced, not left for a future audit.

## Explicitly out of scope

- **Hindi (`lang/hi.json`)**: only exposed in the admin navbar's language
  switcher, not part of the Bangla/English requirement this pass targets, and
  was already at 748 keys (comprehensive) before this pass. Left untouched.
- **Database content** (donor names, blog post bodies, notice text, etc.) is
  user-generated content, not UI chrome — per instructions, this was never
  translated or altered.
- One pre-existing translation choice was noticed but deliberately left alone:
  `"Home": "বাড়ি"` (literally "house/residence" rather than a more typical
  website-nav "হোম"/"প্রচ্ছদ"). This key already had a value before this pass;
  changing an existing, previously-reviewed translation is a wording/design
  judgment call, not a gap-filling fix, so it was left as-is.
