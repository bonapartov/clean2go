<?php

namespace App\DataTables;

use App\Models\CustomAIModel;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Session;

class CustomAIDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('provider', function ($row) {
                return '<span class="badge bg-info">' . ucfirst($row->provider) . '</span>';
            })
            ->editColumn('is_default', function ($row) {
                return $row->is_default
                    ? '<span class="badge bg-success">'.__('static.chats.yes').'</span>'
                    : '<span class="badge bg-secondary">'.__('static.no').'</span>';
            })
            ->addColumn('action', function ($row) {
                return view('backend.inc.action', [
                    'edit' => 'backend.custom-ai-model.edit',
                    'edit_permission' => 'backend.custom_ai_model.edit',
                    'locale' => Session::get('locale', app()->getLocale()),
                    'delete' => 'backend.custom-ai-model.destroy',
                    'delete_permission' => 'backend.custom_ai_model.destroy',
                    'data'   => $row,
                ]);
            })
            ->rawColumns(['provider', 'is_default', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(CustomAIModel $model): QueryBuilder
    {
       $models = $model->query()->orderBy('created_at', 'desc');

        return $models;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $builder = $this->builder();
        $builder->setTableId('custom-ai-table');
        $no_records_found = __('static.no_records_found');

        $builder
            ->addColumn(['data' => 'name', 'title' => __('static.custom_ai_models.name'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'provider', 'title' => __('static.custom_ai_models.provider'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'model_name', 'title' => __('static.custom_ai_models.model_name'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'base_url', 'title' => __('static.custom_ai_models.base_url'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'is_default', 'title' => __('static.custom_ai_models.is_default'), 'orderable' => true, 'searchable' => false])
            ->addColumn(['data' => 'action', 'title' => __('static.action'), 'orderable' => false, 'searchable' => false]);
        
        return $this->builder()
            ->selectStyleSingle()
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
        return 'custom-ai-table_'.date('YmdHis');
    }
}
