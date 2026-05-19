<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

class ZoneManagerDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->filterColumn('zones', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    // Search for users with "All Zones" when keyword matches
                    if (stripos('all zones', $keyword) !== false) {
                        $q->where('allow_all_zones', true);
                    }
                    // Search for users with specific zone names
                    $q->orWhereHas('zonePermissions', function ($zoneQuery) use ($keyword) {
                        $zoneQuery->where('zones.name', 'like', "%{$keyword}%");
                    });
                });
            })
            ->editColumn('checkbox', function ($row) {
                return '<div class="form-check"><input type="checkbox" name="row" class="rowClass form-check-input" value='.$row->id.' id="rowId'.$row->id.'"></div>';
            })
            ->editColumn('name', function ($row) {
                return view('backend.inc.action', [
                    'info' => $row,
                ]);
            })
            ->editColumn('role', function ($row) {
                return $row->getRoleNames()->first() ?? 'N/A';
            })
            ->editColumn('zones', function ($row) {
                if ($row->allow_all_zones) {
                    return '<span class="badge badge-success">All Zones</span>';
                }
                $zones = $row->zonePermissions;
                if ($zones->isEmpty()) {
                    return '<span class="badge badge-warning">No Zones</span>';
                }
                return $zones->pluck('name')->implode(', ');
            })
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->editColumn('action', function ($row) {
                return view('backend.inc.action', [
                    'edit' => 'backend.zone_manager.edit',
                    'delete' => 'backend.zone_manager.destroy',
                    'data' => $row,
                ]);
            })
            ->editColumn('status', function ($row) {
                return view('backend.inc.action', [
                    'toggle' => $row,
                    'name' => 'status',
                    'route' => 'backend.zone_manager.status',
                    'value' => $row->status,
                ]);
            })
            ->rawColumns(['checkbox', 'action', 'role', 'created_at', 'status', 'zones']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        // Get users with custom roles (non-system reserved roles)
        $users = $model->newQuery()
            ->whereHas('roles', function ($query) {
                $query->where('system_reserve', 0);
            })
            ->with(['roles', 'zonePermissions']);

        if (request()->order) {
            if ((bool) head(request()->order)['column']) {
                $index = head(request()->order)['column'];
                if (!isset(request()->columns[$index]['orderable'])) {
                    return $users;
                }
            }
        }

        return $users->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $user = auth()->user();
        $builder = $this->builder();
        $no_records_found = __('static.no_records_found');
        $users = User::whereHas('roles', function ($query) {
            $query->where('system_reserve', 0);
        })->get();

        $builder->setTableId('zone-manager-table');
        
        if ($user->can('backend.zone_manager.destroy')) {
            if($users->count() > 1) {
                $builder->addColumn(['data' => 'checkbox', 'title' => '<div class="form-check"><input type="checkbox" class="form-check-input" title="Select All" id="select-all-rows" /> </div>', 'class' => 'title', 'orderable' => false, 'searchable' => false]);
            }
        }

        $builder->addColumn(['data' => 'name', 'title' => __('static.name'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'role', 'title' => __('static.roles.role'), 'orderable' => false, 'searchable' => false])
            ->addColumn(['data' => 'zones', 'title' => __('static.zone.zones'), 'orderable' => false, 'searchable' => true])
            ->addColumn(['data' => 'created_at', 'title' => __('static.created_at'), 'orderable' => true, 'searchable' => false]);

        if ($user->can('backend.zone_manager.edit')) {
            $builder->addColumn(['data' => 'status', 'title' => __('static.status'), 'orderable' => true, 'searchable' => false]);
        }

        if ($user->can('backend.zone_manager.edit') || $user->can('backend.zone_manager.destroy')) {
            $builder->addColumn(['data' => 'action', 'title' => __('static.action'), 'orderable' => false, 'searchable' => false]);
        }

        return $builder->minifiedAjax()
            ->parameters([
                'language' => [
                    'emptyTable' => $no_records_found,
                    'infoEmpty' => '',
                    'zeroRecords' => $no_records_found,
                ],
                'drawCallback' => 'function(settings) {
                    if (settings._iRecordsDisplay === 0) {
                        $(settings.nTableWrapper).find(".dataTables_paginate").hide();
                    } else {
                        $(settings.nTableWrapper).find(".dataTables_paginate").show();
                    }
                    feather.replace();
                }',
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ZoneManager_'.date('YmdHis');
    }
}

