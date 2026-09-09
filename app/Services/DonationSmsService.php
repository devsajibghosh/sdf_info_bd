<?php

namespace App\Services;

use App\Helpers\BulkSmsHelper;
use App\Models\Donor;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Single source of truth for the "donation received" SMS: the payment-success
 * gateway flow (GatewayHelper::addBalanceToUser) and the manual/admin
 * approval flow (DonationController::processFinalApproval) both call this
 * instead of each formatting their own message, so the wording and donor-name
 * resolution can never drift apart between the two paths again.
 */
class DonationSmsService
{
    public function sendSuccessSms(?string $phone, float $amount, $recipient, int $contextId = 0): void
    {
        if (!$phone) {
            return;
        }

        $name = $this->resolveDonorName($recipient, $phone);

        $message = sprintf(
            'Dear %s, Your donation %s BDT has been successfully received by SDF gratefully. Download the payment slip by logging into www.sdf.info.bd/login',
            $name,
            number_format($amount, 2)
        );

        try {
            (new BulkSmsHelper())->send($phone, $message);
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
