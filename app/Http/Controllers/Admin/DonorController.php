<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;




class DonorController extends Controller
{
    public function __construct()
    {
        goIfUserCan('manage-donors');
    }
    
    
    public function edit($donorId)
    {
        $title = __('Edit donor');

        $donor = Donor::findOrFail($donorId);

        $widget = [
            'total_collected_amount' => $donor->donations()->success()->where('field_collection', 1)->sum('amount'),
            'number_of_donations'     => $donor->donations()->success()->where('field_collection', 0)->count(),
            'number_of_field_donations'     => $donor->donations()->success()->where('field_collection', 1)->count(),
            'total_donation_amount'   => $donor->donations()->success()->where('field_collection', 0)->sum('amount'),
            'last_donation_amount'    => $donor->donations()->success()->latest()->first()?->amount ?? 0,
            'maximum_donation_amount' => $donor->donations()->success()->orderBy('amount', 'desc')->first()?->amount ?? 0,
            'max_donation_date'       => $donor->donations()->success()->orderBy('amount', 'desc')->first()?->created_at ?? null,
            'last_donation_date'      => $donor->donations()->success()->latest()->first()?->created_at ?? null,
        ];

        return view('admin.donor.form', compact('title', 'donor', 'widget'));
    }

    public function list()
    {
        $title = __('Donors');
        $donors = Donor::withSum(['donations as total_donations_amount' => function ($query) {
                $query->where('field_collection', 0);
            }], 'amount')
            ->withCount(['donations as total_donations_count' => function ($query) {
                $query->where('field_collection', 0);
            }])
            ->with('lastDonation')
            ->searching(['phone_number'])
            ->paginate();
            

        return view('admin.donor.list', compact('donors', 'title'));
    }

    public function save(Request $request, $id = null)
    {
        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'phone_number' => [
                'required',
                'string',
                'max:20',
                \Illuminate\Validation\Rule::unique('donors', 'phone_number')->ignore($id),
            ],
            'father_name'  => ['nullable', 'string', 'max:255'],
            'mother_name'  => ['nullable', 'string', 'max:255'],
            'address'      => ['nullable', 'string'],
        ]);

        $donor = $id ? Donor::findOrFail($id) : new Donor();

        $donor->fill($request->only([
            'name',
            'phone_number',
            'father_name',
            'mother_name',
            'address',
        ]));

        $donor->save();

        return to_route('admin.donor.list')->withSuccess(__('Donor information saved successfully.'));
    }
    
    
    
     // total donation amounut print
    public function downloadMaxDonors()
    {

    $donors = Donor::withSum(['donations as total_donations_amount' => function ($query) {
            $query->where('field_collection', 0);
        }], 'amount')
        ->withCount(['donations as total_donations_count' => function ($query) {
            $query->where('field_collection', 0);
        }])
        ->with('lastDonation')
        // Order by the ALIAS created in withSum
        ->orderBy('total_donations_amount', 'desc')
        ->take(20)
        ->get();

    $usersByPhone = \App\Models\User::whereIn('phone_number', $donors->pluck('phone_number'))->get()->keyBy('phone_number');

    $pdf = Pdf::loadView('admin.donor.reports', compact('donors', 'usersByPhone'));
    
    return $pdf->download('top-20donors(sdf).pdf');
   
    }
    
    
    // donor live view 
    
    
public function live_report(Request $request)
{
    // Search input field theke phone number nibe
    $search = $request->input('phone_number');

    $query = DB::table('donors')
        ->leftJoin('donations', 'donors.id', '=', 'donations.donor_id')
        ->select(
            'donors.id',
            'donors.phone_number',
            'donors.name', // Jodi name thake
            DB::raw('COUNT(donations.id) as total_count'),
            DB::raw('SUM(donations.amount) as total_amount'),
            DB::raw('MAX(donations.created_at) as last_date'),
            DB::raw('(SELECT amount FROM donations WHERE donor_id = donors.id ORDER BY created_at DESC LIMIT 1) as last_amount')
        );

    // Jodi search-e phone number thake, tobe filter korbe
    if (!empty($search)) {
        $query->where('donors.phone_number', 'LIKE', "%{$search}%");
    }

    $donorsData = $query->groupBy('donors.id', 'donors.phone_number', 'donors.name')
        ->orderBy('last_date', 'desc')
        ->paginate(30) // Proti page-e 20 ta data dekhabe
        ->withQueryString(); // Search keyword-ke pagination link-er shathe dhore rakhbe

    return view('admin.report.donor_report', compact('donorsData', 'search'));
}


// download csv format data


public function live_report_download(){

// 1. Data load kora (Optimized Query)
        $donorsData = DB::table('donors')
            ->leftJoin('donations', 'donors.id', '=', 'donations.donor_id')
            ->select(
                'donors.phone_number',
                'donors.name',
                DB::raw('COUNT(donations.id) as total_count'),
                DB::raw('SUM(donations.amount) as total_amount'),
                DB::raw('MAX(donations.created_at) as last_date'),
                DB::raw('(SELECT amount FROM donations WHERE donor_id = donors.id ORDER BY created_at DESC LIMIT 1) as last_amount')
            )
            ->groupBy('donors.id', 'donors.phone_number', 'donors.name')
            ->orderBy('total_amount', 'desc')
            ->get();

        // 2. CSV Streaming Response generate kora
        $response = new StreamedResponse(function () use ($donorsData) {
            $handle = fopen('php://output', 'w');
            
            // CSV Column Headers
            fputcsv($handle, [
                'Donor Name', 
                'Phone Number', 
                'Total Donations (Times)', 
                'Total Amount (TK)', 
                'Last Donation Amount', 
                'Last Donation Date'
            ]);

            // CSV Row-te data bishano
            foreach ($donorsData as $donor) {
                fputcsv($handle, [
                    $donor->name ?? 'N/A',
                    $donor->phone_number,
                    $donor->total_count,
                    $donor->total_amount ?? 0,
                    $donor->last_amount ?? 0,
                    $donor->last_date ?? 'No Record'
                ]);
            }

            fclose($handle);
        });

        // 3. Headers set kora file download force korar jonno
        $fileName = 'Donor_Report_' . date('Y-m-d_H-i') . '.csv';
        
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;

}
    
    
    
    
    
}
