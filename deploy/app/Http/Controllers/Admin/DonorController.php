<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donor;
use Illuminate\Http\Request;

class DonorController extends Controller
{
    public function edit($donorId)
    {
        $title = __('Edit donor');

        $donor = Donor::findOrFail($donorId);

        return view('admin.donor.form', compact('title', 'donor'));
    }

    public function list()
    {
        $title = __('Donors');
        $donors = Donor::withSum('donations', 'amount')
            ->withCount('donations')
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
}
