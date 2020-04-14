<?php

namespace App\DataTables\Managers;

use App\Models\Manager;
use Yajra\DataTables\Services\DataTable;

class ReleasedManagersDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables($query)
            ->editColumn('name', function (Manager $manager) {
                return $manager->full_name;
            })
            ->editColumn('released_at', function (Manager $manager) {
                return $manager->released_at->toDateString();
            })
            ->filterColumn('id', function ($query, $keyword) {
                $query->where($query->qualifyColumn('id'), $keyword);
            })
            ->filterColumn('name', function ($query, $keyword) {
                $sql = "CONCAT(managers.first_name, ' ', managers.last_name)  like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->addColumn('action', function ($manager) {
                return view(
                    'managers.partials.action-cell',
                    [
                        'actions' => collect(['employ']),
                        'model' => $manager
                    ]
                );
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = Manager::released()->withReleasedAtDate();

        return $query;
    }
}
