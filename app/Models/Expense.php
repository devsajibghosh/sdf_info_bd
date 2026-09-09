<?php

namespace App\Models;

use App\Traits\Modeling;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use Modeling;
    
    protected $guarded = [];

    public function approvedBy()
    {
        return $this->belongsTo(Admin::class, 'approved_by', 'id');
    }
    
    public function scopeApproved($query)
    {
        return $query->where('status', 1);
    }

    public function scopeNotApproved($query)
    {
        return $query->where('status', 0);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function expenseBy()
    {
        return $this->belongsTo(Admin::class, 'expense_by');
    }

    public function expenseFor()
    {
        return $this->belongsTo(Admin::class, 'expense_for');
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->status == 1) {
            return '<span class="badge text-bg-success">' . __('Approved') . '</span>';
        }

        if ($this->status == 0) {
            return '<span class="badge text-bg-warning">' . __('Not Approved') . '</span>';
        }
    }
}
