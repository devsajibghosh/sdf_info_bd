<?php

use App\Helpers\SystemHelper;
use App\Models\BlogPost;
use App\Models\GeneralSetting;
use App\Models\Page;
use App\Models\PageView;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Silber\Bouncer\BouncerFacade as Bouncer;

if (!function_exists('handleResize')) {
    function handleResize($key)
    {
        $sizeStr          = uploadImageSize($key);

        if(!$sizeStr) return null;

        [$width, $height] = explode('x', $sizeStr);
        $resize           = ['width' => (int)$width, 'height' => (int)$height];

        return $resize;
    }
}

if (!function_exists('diffForHumans')) {
    function diffForHumans($date, $fallback = 'N/A')
    {
        if (!$date) {
            return $fallback;
        }

        return Carbon::parse($date)->diffForHumans();
    }
}

if (!function_exists('hexToHsl')) {
    function hexToHsl($hex)
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) == 3) {
            $r = hexdec($hex[0] . $hex[0]);
            $g = hexdec($hex[1] . $hex[1]);
            $b = hexdec($hex[2] . $hex[2]);
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = $s = $l = ($max + $min) / 2;

        if ($max == $min) {
            $h = $s = 0; // achromatic
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
                    break;
            }

            $h /= 6;
        }

        return [
            round($h * 360, 1),
            round($s * 100, 1) . '%',
            round($l * 100, 1) . '%'
        ];
    }
}

if (!function_exists('goIfUserCan')) {
    function goIfUserCan($ability, $guard = 'admin')
    {
        if(admin()->id == 1) return true;

        if (!userCan($ability, $guard)) {
            abort(403, __('You have no access'));
        }
    }
}


if (!function_exists('userCan')) {
    function userCan($ability, $guard = 'admin')
    {
        if(admin()?->id == 1) return true;

        $user = auth($guard)->user();

        return $user && $user->can($ability);
    }
}

if (!function_exists('get_seo_content')) {
    function get_seo_content(string $slug, bool $isBlog = false)
    {
        if ($isBlog) {
            $seoContent = BlogPost::where('slug', $slug)
                ->pluck('seo_content')->first();
        } else {
            $seoContent = Page::where('slug', $slug)
                ->pluck('seo_content')->first();
        }

        if (!$seoContent) return null;

        $keywords = join(',', array_map(function ($val) {
            return $val->value;
        }, array_values(json_decode($seoContent?->meta_keywords ?? '') ?? [])));

        $seoContent->meta_keywords = $keywords;

        return $seoContent;
    }
}

if (!function_exists('filePath')) {
    function filePath($key)
    {
        $sizes = fileSizes($key);
        return $sizes['path'] ?? null;
    }
}

if (!function_exists('uploadImageSize')) {
    function uploadImageSize($key)
    {
        $sizes = fileSizes($key);
        return $sizes['size'] ?? null;
    }
}



if (!function_exists('imageSrc')) {
    function imageSrc($image)
    {
        if (!$image) {
            return asset('no-image.png');
        }

        // Always resolve through the "public" disk so the URL respects
        // filesystems.php (disk root/URL prefix) instead of hardcoding a
        // path. Storage::url() produces "<APP_URL>/storage/<path>", never
        // the internal "storage/app/public/..." disk path, which is not
        // web-accessible and was the cause of 403s on missing/legacy files.
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($image)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($image);
        }

        return asset('no-image.png');
    }
}



if (!function_exists('imageSize')) {
    function imageSize($key) {
        $size = config('files.' . $key . '.size', null);
        if(!$size) return '';
        return 'Recomended image size ' . $size;
    }
}

if (!function_exists('fileSizes')) {
    function fileSizes($key = null)
    {
        $data = config('files');

        if ($key) {
            return $data[$key] ?? [];
        }

        return $data;
    }
}


if (!function_exists('software')) {
    function software()
    {
        return new SystemHelper();
    }
}

if (!function_exists('getRealIP')) {
    function getRealIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ipList[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }

        return $ip;
    }
}


if (!function_exists('generalSetting')) {
    /**
     * Get the general setting information
     * @param mixed $key
     */
    function generalSetting($key = null)
    {
        // generalSetting() is called dozens of times per request (settings screens,
        // AppServiceProvider boot, Blade views). The Cache::rememberForever() call
        // below already avoids re-querying the database, but on the file cache
        // driver it still re-reads the cache file from disk on every call; this
        // static holds the resolved value for the lifetime of the request.
        static $generalSetting = null;
        static $resolved = false;

        if (!$resolved) {
            $generalSetting = Cache::rememberForever('general-setting', function () {
                return GeneralSetting::first();
            });
            $resolved = true;
        }

        if ($key) return $generalSetting?->$key;

        return $generalSetting;
    }
}

if (!function_exists('admin')) {
    /**
     * Get the admin user
     */
    function admin()
    {
        return auth('admin')->user();
    }
}

if (!function_exists('generateTransactionId')) {
    function generateTransactionId(string $prefix = 'txn_', int $length = 10): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $random = '';
        $max = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $random .= $characters[random_int(0, $max)];
        }

        return $prefix . $random;
    }
}

if (!function_exists('amount')) {
    function amount($amount = 0)
    {
        return $amount;
    }
}

if (!function_exists('getClassesInNamespace')) {
    function getClassesInNamespace(string $namespace): array
    {
        $classes = [];
        foreach (get_declared_classes() as $class) {
            if (Str::startsWith($class, $namespace)) {
                $classes[] = class_basename($class);
            }
        }

        $classMap = require base_path('vendor/composer/autoload_classmap.php');


        foreach ($classMap as $class => $path) {
            if (Str::startsWith($class, $namespace) && class_exists($class)) {
                $reflection = new ReflectionClass($class);
                if (!$reflection->isAbstract()) {
                    $classes[] = class_basename($class);
                }
            }
        }

        return array_unique($classes);
    }
}

if (!function_exists('countries')) {
    function countries()
    {
        return json_decode(file_get_contents(resource_path(
            'json/countries.json'
        )), true);
    }
}

if (!function_exists('addressData')) {
    function addressData()
    {
        return json_decode(file_get_contents(resource_path(
            'json/address_data.json'
        )), true);
    }
}

if (!function_exists('divisions')) {
    function divisions()
    {
        return json_decode(file_get_contents(resource_path(
            'json/divisions.json'
        )), true);
    }
}

if (!function_exists('districts')) {
    function districts()
    {
        return json_decode(file_get_contents(resource_path(
            'json/districts.json'
        )), true);
    }
}

if (!function_exists('upazilas')) {
    function upazilas()
    {
        return json_decode(file_get_contents(resource_path(
            'json/upazilas.json'
        )), true);
    }
}

if (!function_exists('postcodes')) {
    function postcodes()
    {
        return json_decode(file_get_contents(resource_path(
            'json/postcodes.json'
        )), true);
    }
}

if (!function_exists('dhakaCities')) {
    function dhakaCities()
    {
        return json_decode(file_get_contents(resource_path(
            'json/dhaka.json'
        )), true);
    }
}


if (! class_exists('Setting')) {
    class_alias(\App\Facades\System::class, 'Setting');
}


if (!function_exists('activeClass')) {
    /**
     * Returns 'active' if the current route name and parameters match.
     *
     * @param string $routeName
     * @param array|string|null $params
     * @return string
     */
    function activeClass(string $routeName, $params = null): string
    {
        if (!request()->routeIs($routeName)) {
            return '';
        }

        if (is_null($params)) {
            return 'active';
        }

        // If a single parameter (like a slug) is passed
        if (!is_array($params)) {
            $params = [$params];
        }

        $currentParams = request()->route()->parameters();

        foreach (array_values($params) as $index => $value) {
            if (!isset(array_values($currentParams)[$index]) || array_values($currentParams)[$index] != $value) {
                return '';
            }
        }

        return 'active';
    }
}


if (!function_exists('bytesToHumanReadable')) {
    function bytesToHumanReadable($bytes, $precision = 2)
    {
        $units  = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $bytes  = max($bytes, 0);
        $pow    = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow    = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));                             // $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

/**
 * This will count page visit but won't count same in 1 hour
 */
if (!function_exists('count_page_view')) {
    function count_page_view($slug = '/')
    {
        $key = 'visited_page_' . $slug . '_' . request()->ip();
        if (!cache()->has($key)) {
            PageView::updateOrCreate(
                ['slug' => $slug],
                ['views' => DB::raw('views + 1')]
            );
            cache()->put($key, true, now()->addMinutes(60));
        };
    }
}

if (!function_exists('viewShare')) {
    function viewShare($theView, array $vars = [])
    {
        view()->composer($theView, function ($view) use ($vars) {
            foreach ($vars as $key => $value) {
                $view->with($key, $value);
            }
        });
    }
}
