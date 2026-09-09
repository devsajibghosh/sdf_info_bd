<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\CallLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CallLogController extends Controller
{
    public function delete(Request $request, $id) {
        $callLog = CallLog::findOrFail($id);
        $callLog->delete();
        
        return back()->withSuccess(__('Call log deleted'));
    }
    public function store(Request $request)
    {   
        $validated = $request->validate([
            'user_id'        => 'required|exists:users,id',
            'status'         => ['required', Rule::in([
                Status::AFFIRMED_DONATE,
                Status::CALL_DECLINED,
                Status::CALL_LATER,
                Status::CALL_SPECIFIC_DATE,
                Status::DECLINED_TO_DONATE,
                Status::PHONE_OFF,
            ])],
            'amount'         => 'required_if:status,' . Status::AFFIRMED_DONATE,
            'approx_date'    => 'sometimes|required_if:status,' . Status::AFFIRMED_DONATE,
            'next_call_date' => 'sometimes|required_if:status,' . Status::CALL_SPECIFIC_DATE,
            'note'           => 'nullable',
            'call_time'      => 'required',
        ]);

        $callLog               = new CallLog();
        $validated['admin_id'] = auth('admin')->id();
        $callLog->fill($validated);
        $callLog->save();
        
        return back()->withSuccess(__('Call log created'));
    }
}
