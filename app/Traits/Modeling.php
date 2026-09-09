<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Modeling
{
  public function getStatusBadgeAttribute()
  {
      if ($this->status == 1) {
          return '<span class="badge text-bg-success">' . __('Active') . '</span>';
      }

      if ($this->status == 0) {
          return '<span class="badge text-bg-danger">' . __('Inactive') . '</span>';
      }
  }
  
  public function scopeActive($query)
  {
    return $query->where('status', 1);
  }

  public function scopeInactive($query)
  {
    return $query->where('status', 0);
  }

  public function scopeSorting(Builder $query)
  {
    if(request()->sort_by) $query->orderBy(request()->sort_by, request()->sort_type ?? 'asc');
  }

  public function scopeSearching(Builder $query, array $fields = [])
  {
    $search = request()->get('search', '');

    if (!empty($search) && is_array($fields)) {
      foreach ($fields as $field) {
        if (str_contains($field, ':')) {
          list($relation, $relatedField) = explode(':', $field);

          $query->orWhereHas($relation, function ($q) use ($relatedField, $search) {
            $q->where($relatedField, 'LIKE', '%' . $search . '%');
          });
        } else {
          $query->orWhere($field, 'LIKE', '%' . $search . '%');
        }
      }
    }

    return $query;
  }
}