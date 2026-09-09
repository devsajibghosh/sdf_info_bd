<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonorController extends Controller
{
    
    public function liveReport(Request $request)
    {
        // ==========================================
        // VALIDATE REQUEST
        // ==========================================

        $validated = $request->validate([
            'phone_number' => [
                'required',
                'string',
                'max:30',
            ],
        ]);

        $phone = trim($validated['phone_number']);


        // ==========================================
        // FIND DONOR + DONATION SUMMARY
        // ==========================================

        $donor = DB::table('donors')
            ->leftJoin(
                'donations',
                'donors.id',
                '=',
                'donations.donor_id'
            )
            ->select(
                'donors.id',
                'donors.phone_number',
                'donors.name',

                // Total donation count
                DB::raw(
                    'COUNT(donations.id) AS total_count'
                ),

                // Total donation amount
                DB::raw(
                    'COALESCE(SUM(donations.amount), 0) AS total_amount'
                ),

                // Last donation date
                DB::raw(
                    'MAX(donations.created_at) AS last_date'
                ),

                // Last donation amount
                DB::raw('(
                    SELECT amount
                    FROM donations
                    WHERE donor_id = donors.id
                    ORDER BY created_at DESC
                    LIMIT 1
                ) AS last_amount')
            )
            ->where('donors.phone_number', $phone)
            ->groupBy(
                'donors.id',
                'donors.phone_number',
                'donors.name'
            )
            ->first();


        // ==========================================
        // DONOR NOT FOUND
        // ==========================================

        if (!$donor) {
            return response()->json([
                'success' => false,
                'message' => 'Donor not found.',
                'data' => null,
            ], 404);
        }


        // ==========================================
        // FORMAT AMOUNTS
        // ==========================================

        $totalAmount = number_format(
            (float) $donor->total_amount,
            2,
            '.',
            ''
        );

        $lastAmount = $donor->last_amount !== null
            ? number_format(
                (float) $donor->last_amount,
                2,
                '.',
                ''
            )
            : null;


        // ==========================================
        // SUCCESS RESPONSE
        // ==========================================

        return response()->json([
            'success' => true,
            'message' => 'Donor success.',
            'data' => [
                'phone' => $donor->phone_number,

                'name' => $donor->name,

                'total' => $totalAmount,

                'count' => (int) $donor->total_count,

                'last_date' => $donor->last_date,

                'last_amount' => $lastAmount,
            ],
        ], 200);
    }
    
    
// all donors report api
    
    
public function allDonorsReport(Request $request)
{
    // ==========================================
    // FIND ALL DONORS + DONATION SUMMARY
    // ==========================================

    $donors = DB::table('donors')
        ->leftJoin(
            'donations',
            'donors.id',
            '=',
            'donations.donor_id'
        )
        ->select(
            'donors.id',
            'donors.phone_number',
            'donors.name',

            // Total donation count
            DB::raw('COUNT(donations.id) AS total_count'),

            // Total donation amount
            DB::raw(
                'COALESCE(SUM(donations.amount), 0) AS total_amount'
            ),

            // Last donation date
            DB::raw('MAX(donations.created_at) AS last_date'),

            // Last donation amount
            DB::raw('(
                SELECT amount
                FROM donations
                WHERE donor_id = donors.id
                ORDER BY created_at DESC
                LIMIT 1
            ) AS last_amount')
        )
        ->groupBy(
            'donors.id',
            'donors.phone_number',
            'donors.name'
        )
        ->orderBy('donors.id', 'asc')
        ->get();


    // ==========================================
    // FORMAT DONOR DATA
    // ==========================================

    $data = $donors->map(function ($donor) {

        $totalAmount = number_format(
            (float) $donor->total_amount,
            2,
            '.',
            ''
        );

        $lastAmount = $donor->last_amount !== null
            ? number_format(
                (float) $donor->last_amount,
                2,
                '.',
                ''
            )
            : null;

        return [
            'phone' => $donor->phone_number,

            'name' => $donor->name,

            'total' => $totalAmount,

            'count' => (int) $donor->total_count,

            'last_date' => $donor->last_date,

            'last_amount' => $lastAmount,
        ];
    })->values();


    // ==========================================
    // SUCCESS RESPONSE
    // ==========================================

    return response()->json([
        'success' => true,
        'message' => 'All donors success.',
        'data' => $data,
    ], 200);
}
    
    
}