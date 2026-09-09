<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\Modeling;
use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    use Modeling;

    protected $fillable = ['user_id', 'admin_id', 'status', 'call_time', 'amount', 'approx_date', 'next_call_date', 'note'];
    
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusBadgeAttribute()
    {
        $text = '';
        $class = '';

        if($this->status == Status::PHONE_OFF){
            $text  = 'Phone Off';
            $class = 'danger';
        } else if($this->status == Status::AFFIRMED_DONATE) {
            $text  = 'Affirmed to Donate';
            $class = 'success';
        } else if($this->status == Status::CALL_LATER) {
            $text  = 'Call Later';
            $class = 'secondary';
        } else if($this->status == Status::CALL_SPECIFIC_DATE) {
            $text  = 'Call Specific Date';
            $class = 'primary';
        } else if($this->status == Status::CALL_DECLINED) {
            $text  = 'Call Declined';
            $class = 'danger';
        } else if($this->status == Status::DECLINED_TO_DONATE) {
            $text  = 'Declined to Donate';
            $class = 'danger';
        } else {
            $text = 'Unknown status';
        }
        
        return '<span class="badge text-bg-'. $class .'">' . __($text) . '</span>';
    }
}
