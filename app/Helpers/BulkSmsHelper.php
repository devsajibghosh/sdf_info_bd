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

        // BulkSMSBD always answers with HTTP 200, even for a rejected request
        // (bad api_key, unapproved sender id, insufficient balance, ...); the
        // real outcome is the `response_code` field in the JSON body, where
        // 202 is the only value that means the SMS was actually accepted.
        $responseCode = $response->json('response_code');

        return [
            'success' => $response->successful() && (int) $responseCode === 202,
            'status'  => $response->status(),
            'body'    => $response->body(),
        ];
    }
}
