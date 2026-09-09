<?php

namespace App\DataTables;

use Yajra\DataTables\Services\DataTable;

class DataTableBase extends DataTable
{
    protected $tableId = 'table--id';
    
    protected $paginateLength = 10;
    
    protected $lengthMenu = [10, 25, 50, 100];

    public function html()
    {
        return $this->builder()
            ->setTableId($this->tableId)
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->searchDelay(1000)
            ->orderBy(0)
            ->parameters([
                'pageLength' => $this->paginateLength,
                'lengthMenu' => $this->lengthMenu
            ]);
    }
}