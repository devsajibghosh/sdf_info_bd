<?php

namespace App\Services;

/**
 * Resolves a human-readable payment channel label and SDF's internal fee
 * classification (MFS vs Card) purely from the SSLCommerz validation data
 * that is already persisted on payments.meta->sslcz_validation (see
 * SslCommerzGateway::verify(), which stores the full validation API
 * response there). No new database column is introduced for this — it
 * only interprets data SSLCommerz already gave us at verification time.
 *
 * SSLCommerz's documented fields (https://developer.sslcommerz.com/doc/v4/):
 *  - card_brand: the payment network/category, e.g. VISA, MASTER, AMEX,
 *    IB (internet banking), MOBILEBANKING.
 *  - card_type: the specific bank/MFS gateway the customer selected,
 *    e.g. "BKASH-BKash", "VISA-Dutch Bangla".
 *
 * Historical payments made before this validation payload was captured
 * (or any brand SDF hasn't defined a rate for) have no reliable channel/
 * fee group — callers must show a safe "Unknown" label and must not
 * guess a fee for them.
 */
class SslCommerzChannel
{
    public const GROUP_MFS = 'mfs';
    public const GROUP_CARD = 'card';

    /** SDF's requested accounting rate for Mobile Financial Services (bKash/Nagad/Rocket/etc). */
    public const MFS_FEE_RATE = 0.025;

    /** SDF's requested accounting rate for card transactions (VISA/MASTER/AMEX). */
    public const CARD_FEE_RATE = 0.035;

    private const MFS_OPERATOR_NAMES = [
        'BKASH' => 'bKash',
        'NAGAD' => 'Nagad',
        'ROCKET' => 'Rocket',
        'UPAY' => 'Upay',
        'MCASH' => 'mCash',
        'OK' => 'OK Wallet',
        'TAP' => 'Tap',
    ];

    /**
     * Label to show after "<Gateway name> — ", e.g. "bKash", "VISA",
     * "MasterCard". Never fabricated: falls back to "Unknown" when the
     * validation payload is missing.
     */
    public static function label(?array $validation): string
    {
        if (!$validation) {
            return 'Unknown';
        }

        $brand = strtoupper(trim((string) ($validation['card_brand'] ?? '')));
        $type = (string) ($validation['card_type'] ?? '');

        return match (true) {
            $brand === 'VISA' => 'VISA',
            $brand === 'MASTER' => 'MasterCard',
            $brand === 'AMEX' => 'AMEX',
            $brand === 'IB' => 'Internet Banking',
            str_contains($brand, 'MOBILE') => self::mfsOperatorLabel($type),
            $type !== '' => $type,
            default => 'Unknown',
        };
    }

    /**
     * SDF's internal fee bucket for this transaction. Null means the
     * channel can't be confidently classified into either bucket (missing
     * validation data, or a brand SDF hasn't assigned a rate to such as
     * Internet Banking) — callers must treat that as "fee not calculated"
     * rather than guessing MFS or Card.
     */
    public static function feeGroup(?array $validation): ?string
    {
        if (!$validation) {
            return null;
        }

        $brand = strtoupper(trim((string) ($validation['card_brand'] ?? '')));

        return match (true) {
            in_array($brand, ['VISA', 'MASTER', 'AMEX'], true) => self::GROUP_CARD,
            str_contains($brand, 'MOBILE') => self::GROUP_MFS,
            default => null,
        };
    }

    public static function feeRateForGroup(?string $group): ?float
    {
        return match ($group) {
            self::GROUP_MFS => self::MFS_FEE_RATE,
            self::GROUP_CARD => self::CARD_FEE_RATE,
            default => null,
        };
    }

    /**
     * SQL CASE expression usable inside a ->select()/->groupBy() to bucket
     * payments.meta->sslcz_validation.card_brand into 'mfs' / 'card' /
     * 'unknown' at the database level, so Account Summary never has to
     * pull every donation row into PHP just to classify it.
     */
    public static function feeGroupSqlExpression(string $metaColumn = 'payments.meta'): string
    {
        $brand = "UPPER({$metaColumn}->>'$.sslcz_validation.card_brand')";

        return "CASE
            WHEN {$brand} IN ('VISA', 'MASTER', 'AMEX') THEN '" . self::GROUP_CARD . "'
            WHEN {$brand} LIKE '%MOBILE%' THEN '" . self::GROUP_MFS . "'
            ELSE 'unknown'
        END";
    }

    private static function mfsOperatorLabel(string $cardType): string
    {
        $prefix = strtoupper(trim(explode('-', $cardType)[0] ?? ''));

        if ($prefix === '') {
            return 'Mobile Banking';
        }

        return self::MFS_OPERATOR_NAMES[$prefix] ?? ucfirst(strtolower($prefix));
    }
}
