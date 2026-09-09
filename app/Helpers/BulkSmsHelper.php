<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class BulkSmsHelper
{
    protected string $apiKey;
    protected string $senderId;
    protected string $apiUrl = 'https://bulksmsbd.net/api/smsapi';

    public function __construct()
    {
        $this->apiKey   = generalSetting('sms_api_key');
        $this->senderId = generalSetting('sms_sender_id');
    }

    /**
     * Send SMS using BulkSMSBD
     *
     * @param string $to      Receiver's mobile number (e.g., "8801XXXXXXXXX")
     * @param string $message SMS message text
     * @return array          Response data
     */
    public function send(string $to, string $message): array
    {
        // if(app()->isLocal()) {
        //     return [
        //         'success' => true
        //     ];
        // }

        $response = Http::asForm()->post($this->apiUrl, [
            'api_key'  => $this->apiKey,
            'senderid' => $this->senderId,
            'number'   => $to,
            'message'  => $message,
        ]);

        return [
            'success' => $response->successful(),
            'status'  => $response->status(),
            'body'    => $response->body(),
        ];
    }
}
