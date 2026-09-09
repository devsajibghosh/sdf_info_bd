# Route Stability Audit

## Critical bug: duplicate `admin.dashboard` route name

**Reproduced:**
```
$ php artisan optimize
...
routes ........................................................ 13.86ms FAIL

LogicException
Unable to prepare route [panel22/dashboard] for serialization. Another route
has already been assigned name [admin.dashboard].
```

**Root cause** (`routes/admin.php`, inside the `AdminController` group, wrapped
by `RoutesHelper::setupAdminRoutes()` with prefix `panel22` and name `admin.`):

```php
Route::get('/', 'dashboard')->name('dashboard');
Route::get('/dashboard', 'dashboard')->name('dashboard');
```

Both the bare panel root (`panel22`) and `panel22/dashboard` point at the same
`AdminController@dashboard` action and were both given the name `dashboard`,
which resolves to `admin.dashboard` once the group's name prefix is applied.
Two distinct `Route` objects sharing one name serialize fine for normal
runtime routing (`route()` just resolves to whichever was registered last),
but `php artisan route:cache` (and therefore `optimize`) rejects it outright.

**Who references `admin.dashboard`:** `LoginController` (post-login redirect),
`RedirectIfAdmin` middleware, the admin sidebar (`route()` + `activeClass()`),
and one Livewire dashboard view lookup. All of these call `route('admin.dashboard')`
generically — none of them depend on which URI it resolves to — so the fix
below doesn't require touching any of them.

**Fix:**
```php
Route::get('/', 'dashboard')->name('root');       // -> admin.root
Route::get('/dashboard', 'dashboard')->name('dashboard'); // -> admin.dashboard (canonical)
```

`/dashboard` keeps the name every caller already expects. The bare root route
gets its own explicit name instead of being left anonymous — testing showed
that an anonymous route inside a `->name('admin.')` group still inherits the
bare group name (`admin.`) as its full name, which would have silently
collided with another already-anonymous route in the same group
(`POST panel22/login`). Giving it an explicit name sidesteps that entirely
rather than relying on that behavior.

**Verified:**
```
$ php artisan route:list --name=admin.dashboard
GET|HEAD   panel22/dashboard  admin.dashboard › Admin\AdminController@dashboard

$ php artisan route:list --name=admin.root
GET|HEAD   panel22            admin.root › Admin\AdminController@dashboard

$ php artisan tinker --execute="echo route('admin.dashboard'); echo route('admin.root');"
https://www.sdf.info.bd/panel22/dashboard
https://www.sdf.info.bd/panel22

$ php artisan optimize
config ........................................................ 13.70ms DONE
events ......................................................... 0.98ms DONE
routes ........................................................ 21.89ms DONE
views ........................................................ 215.32ms DONE
```

## Full duplicate-route audit

Ran `php artisan route:list --json` (228 routes before this pass, 227 after —
one dead debug route was removed, see below) and checked for:

- **Duplicate route names**: 0 (was 2 before the fix above: `admin.dashboard`
  used twice, plus a latent second collision on the bare `admin.` name — see
  above).
- **Duplicate URI + HTTP method combinations**: 0.
- **Wildcard/catch-all shadowing**: `routes/web.php` registers a single-segment
  catch-all (`Route::get('/{pageSlug}', ...)->name('site.page')`) inside the
  `site.` controller group. Every route registered after that group closes
  (`/donors/live/report`, `/donations/download/monthly`, etc.) is a multi-segment
  GET path or a POST, so none of them can ever be shadowed by the single-segment
  GET catch-all — verified by inspection, no fix needed.
- `routes/admin.php`: every `{id}`/`{key}` wildcard is scoped inside its own
  resource-style prefix group (e.g. `/users/{id}`, `/donation-categories/{id}`)
  with no bare catch-all in the file, so there's no shadowing risk there either.
- `routes/admin2.php` (a Livewire-based alternate admin route set) exists but
  `RoutesHelper::setupLivewireAdminRoutes()` — the only thing that would
  register it — is commented out in `RoutesHelper::setupRoutes()`. It is not
  currently live and was left untouched (enabling it is a feature decision,
  not a stability fix).

## Other route-level fixes made during this pass

- **Removed a dead debug route**: `Route::get('test', function() { dd('test'); })`
  in `routes/web.php` had no name, was referenced nowhere, and would `dd()`
  (dump-and-die) if ever hit in production. Deleted.
- **Hardened two unauthenticated maintenance routes**: `GET /sync` (enriches
  `site_visits` rows with geolocation via outbound `ipinfo.io` calls) and
  `GET /clear` (runs a full `optimize:clear`, including cache-store wipe, and
  explicitly bypasses maintenance mode) had no auth middleware at all — anyone
  who found either URL could trigger them repeatedly. Wrapped both in the
  existing `admin` middleware; behavior for legitimate admin use is unchanged.
- **Corrected a non-functional CSRF except-list entry**: `bootstrap/app.php`'s
  `validateCsrfTokens(except: [...])` matches URI patterns, not route names.
  `'payment.notify'` (a route *name*) could never match its real URI
  (`payment/notify/{key}`), so it was a silent no-op — the actual CSRF
  exemption was, and still is, provided by the route's own
  `->withoutMiddleware(VerifyCsrfToken::class)` call. Changed the entry to
  `'payment/notify/*'` so it's a correct, working backstop rather than dead
  configuration.

## Final state

- **Route count**: 227 (228 minus the one removed debug route).
- **`php artisan optimize`**: passes end-to-end (config, events, routes, views
  all cache successfully).
- **No public URL changed**: every existing reachable public/user/admin URI
  still resolves to the same controller action it did before. The only access
  changes are `/sync` and `/clear`, which now require admin auth (they were
  never linked from any UI and had no legitimate public use case).
