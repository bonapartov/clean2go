<?php

namespace App\DataTables;

use App\Models\SeoSetting;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

class SeoSettingsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($row) {
                return view('backend.inc.action', [
                    'edit' => 'backend.seo-setting.edit',
                    'locale' => Session::get('locale', app()->getLocale()),
                    'edit_permission' => 'backend.seo_setting.edit',
                    'delete' => 'backend.seo-setting.destroy',
                    'delete_permission' => 'backend.seo_setting.delete',
                    'data' => $row,
                ]);
            })
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->editColumn('updated_at', function ($row) {
                return $row->updated_at->diffForHumans();
            })
            ->editColumn('is_active', function ($row) {
                return view('backend.inc.action', [
                    'toggle' => $row,
                    'name'   => 'is_active',
                    'route'  => 'backend.seo-setting.status',
                    'value'  => $row->is_active,
                ]);
            })
            ->rawColumns(['action', 'created_at','is_active']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(SeoSetting $model): QueryBuilder
    {
        $SeoSettingss = $model->newQuery();
        if (request()->order) {
            if ((bool) head(request()->order)['column']) {
                $index = head(request()->order)['column'];
                if (!isset(request()->columns[$index]['orderable'])) {
                    return $SeoSettingss;
                }
            }
        }

        return $SeoSettingss->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $no_records_found = __('static.no_records_found');
        $user = auth()->user();
        $builder = $this->builder();
        $builder->addColumn(['data' => 'page_name', 'title' => __('static.name'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'created_at', 'title' => __('static.created_at'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'is_active', 'title' => __('static.status'), 'orderable' => true, 'searchable' => true]);

        if ($user?->can('backend.seo_setting.edit') || $user?->can('backend.seo_setting.destroy')) {
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
        return 'SeoSettings_'.date('YmdHis');
    }
}
