<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\SystemNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class SystemHelper
{
    private $gs = null;

    public function __construct()
    {
        $this->gs = generalSetting();
    }

    public static function getGeoLocationData(): array
    {
        $ip = getRealIP();
        $url = "http://www.geoplugin.net/xml.gp?ip=" . urlencode($ip);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            return [
                'country' => null,
                'city' => null,
                'area' => null,
                'code' => null,
                'long' => null,
                'lat' => null,
                'ip' => $ip,
                'time' => date('Y-m-d h:i:s A'),
                'error' => 'Unable to fetch data from GeoPlugin',
            ];
        }

        $xml = @simplexml_load_string($response);

        if (!$xml) {
            return [
                'country' => null,
                'city' => null,
                'area' => null,
                'code' => null,
                'long' => null,
                'lat' => null,
                'ip' => $ip,
                'time' => date('Y-m-d h:i:s A'),
                'error' => 'Invalid XML response from GeoPlugin',
            ];
        }

        return [
            'country' => (string) ($xml->geoplugin_countryName ?? null),
            'city'    => (string) ($xml->geoplugin_city ?? null),
            'area'    => (string) ($xml->geoplugin_areaCode ?? null),
            'code'    => (string) ($xml->geoplugin_countryCode ?? null),
            'long'    => (string) ($xml->geoplugin_longitude ?? null),
            'lat'     => (string) ($xml->geoplugin_latitude ?? null),
            'ip'      => $ip,
            'time'    => date('Y-m-d h:i:s A'),
        ];
    }


    /**
     * Notify by email for now against the `User` Model
     * @param \App\Models\User $user
     * @param string $subject
     * @param string $view
     * @param array $data
     * @return void
     */
    public static function notify($user, $subject, $view, $data = [])
    {
        try {
            Mail::to($user->email)->send(
                new SystemNotification($subject, $view, $data, $user)
            );
        } catch (\Throwable $th) {
            info($th->getMessage());
        }
    }

    /**
     * Get the dynamic sections array
     * @return array
     */
    public static function sections(): array
    {
        return json_decode(file_get_contents(resource_path(
            'json/sections.json'
        )), true);
    }

    /**
     * Get the list of all the currencies available
     * @param mixed $systemCurrency
     */
    public function currencies($systemCurrency = false)
    {
        $currenceis = json_decode(file_get_contents(resource_path(
            'json/currencies.json'
        )), true);

        if ($systemCurrency) {
            return collect($currenceis)->where('code', generalSetting('currency'))->first();
        }

        return $currenceis;
    }

    /**
     * Return the amount with symbol prexi & currency suffix
     * @param mixed $amount
     * @return string
     */
    public function amountWithCurrency($amount = 0, $prefix = true)
    {
        $symbol = $this->currencies(true)['symbol'];


        $formatted = Number::format($amount ?? 0, 2, 2, app()->getLocale());

        return (
            ($prefix ? html_entity_decode($symbol) : '' ).
            $formatted .
            ' ' . generalSetting('currency')
        );
    }

    /**
     * Clear and optimize the system
     * @return void
     */
    public function clearCache()
    {
        // Only the cached general-setting row needs invalidating here; a full
        // optimize:clear (config/route/view/compiled + whole cache store) on
        // every settings save is unnecessary and forces every subsequent
        // request to pay the cost of recompiling those caches from scratch.
        Cache::forget('general-setting');
    }

    /**
     * Get the logo iamge path of the system
     * @return string
     */
    public static function logo(): string
    {
        return Storage::url(generalSetting('site_logo'));
    }

    /**
     * Get the favicon path of the system
     * @return string
     */
    public static function favicon(): string
    {
        return Storage::url(generalSetting('site_favicon'));
    }


    public function googleCaptchaEnabled()
    {
        return generalSetting('google_recaptcha_enabled');
    }

    public function getDateTime($dateTime = null, $format = null)
    {
        if (!$dateTime) $dateTime = now();

        if (!$format) $format = 'd/m/Y h:i:a';

        return Carbon::parse($dateTime)->locale(app()->getLocale())->format($format);
    }
}
