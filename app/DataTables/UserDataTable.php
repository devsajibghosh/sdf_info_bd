<?php

namespace App\DataTables;

use App\Models\User;

class UserDataTable extends DataTableBase
{
    protected $tableId = 'user-table';

    public $scope = null;

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($user) {
                return view('admin.users.actions', compact('user'))->render();
            })
            ->addColumn('balance', fn($user) => software()->amountWithCurrency($user->balance))
            ->addColumn('created_at', fn($user) => software()->getDateTime($user->created_at))
            ->editColumn('status', fn($user) => $user->statusBadge)
            ->editColumn('pc', fn($user) => $user->profileCompletedBadge)
            ->editColumn('email', fn($user) => $this->na($user->email))
            ->editColumn('phone_number', fn($user) => $this->na($user->phone_number))
            ->rawColumns(['action', 'pc', 'status']);
    }

    function na($value)
    {
        return $value ?: 'N/A';
    }

    public function query(User $model)
    {
        $query = $model->newQuery()->select(['id', 'name', 'email', 'pc', 'phone_number', 'balance', 'status', 'created_at']);

        if ($this->scope && method_exists($model, 'scope' . ucfirst($this->scope))) {
            $query->{$this->scope}();
        }

        return $query;
    }
    protected function getColumns()
    {
        return [
            ['data' => 'name', 'name' => 'name', 'title' => 'User'],
            ['data' => 'email', 'name' => 'email', 'title' => 'Email'],
            ['data' => 'phone_number', 'name' => 'phone_number', 'title' => 'Phone Number'],
            ['data' => 'pc', 'name' => 'pc', 'title' => 'Profile Completed'],
            ['data' => 'balance', 'name' => 'balance', 'title' => 'Balance'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Joined At'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Actions', 'orderable' => false, 'searchable' => false],
        ];
    }
}
