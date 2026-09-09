<?php

namespace App\Models;

use App\Traits\Modeling;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use Modeling;
    
    protected $guarded = [];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'added_by');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class); 
    }
}
 