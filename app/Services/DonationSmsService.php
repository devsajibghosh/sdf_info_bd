<?php

namespace App\Services;

use App\Helpers\BulkSmsHelper;
use App\Models\Donor;
use App\Models\SmsTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Single sender for the "donation received" SMS: the payment-success gateway
 * flow (GatewayHelper), manual donation entry and pending-donation approval
 * (DonationController) all call this. The wording comes from the admin-
 * editable SmsTemplate for $templateKey (Settings > SMS Settings), and the
 * SMS is skipped when that template or the master SMS switch is off.
 */
class DonationSmsService
{
    public function sendSuccessSms(?string $phone, float $amount, $recipient, int $contextId = 0, string $templateKey = 'donation_approved', ?string $trx = null): void
    {
        if (!$phone) {
            return;
        }

        $message = SmsTemplate::render($templateKey, [
            'name'   => $this->resolveDonorName($recipient, $phone),
            'amount' => number_format($amount, 2),
            'trx'    => $trx ?? '',
            'date'   => now()->format('d M Y'),
        ]);

        if (!$message) {
            return;
        }

        try {
            $result = (new BulkSmsHelper())->send($phone, $message);

            if (empty($result['success'])) {
                Log::error('Donation success SMS rejected by gateway.', [
                    'donation_id' => $contextId,
                    'status' => $result['status'] ?? null,
                    'body' => $result['body'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            // SMS delivery is best-effort and must never break the payment/approval flow.
            Log::error('Donation success SMS failed to send.', [
                'donation_id' => $contextId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * The linked recipient (payment->user, donation->user, or donation->donor)
     * may already have a name; if not, look the phone number up directly in
     * the donors and users tables in case a matching record with a name
     * already exists there. Falls back to the static English "Donor" — never
     * the translated "__('Donor')" — because that resolves to the Bengali
     * "দাতা" under the bn locale, and the SMS greeting must stay in English.
     */
    public function resolveDonorName($recipient, string $phone): string
    {
        if (!empty($recipient?->name)) {
            return $recipient->name;
        }

        $name = Donor::where('phone_number', $phone)->value('name')
            ?: User::where('phone_number', $phone)->value('name');

        return $name ?: 'Donor';
    }
}
