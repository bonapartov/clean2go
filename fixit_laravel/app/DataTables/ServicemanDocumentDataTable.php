<?php

namespace App\DataTables;

use App\Enums\RoleEnum;
use App\Models\UserDocument;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ServicemanDocumentDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('Image', function ($row) {

                $media = $row->getFirstMedia('serviceman_documents');
                    if ($media) {
                        return '<img src="'.$media->getUrl().'" alt="Image" class="img-thumbnail img-fix">';
                    }
                    return '<img src="'.asset("admin/images/No-image-found.jpg").'" alt="Placeholder Image" class="img-thumbnail img-fix">';
                })
                ->editColumn('user.name', function ($row) {
                    $serviceman = $row->user;
                    if ($serviceman) {
                        return view('backend.inc.action', [
                            'info' => $serviceman,
                            'ratings' => $serviceman->review_ratings,
                            'route' => 'backend.servicemen.general-info'
                        ]);
                    }

                })
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->editColumn('status', function ($row) {
                $labelClass = '';
                switch ($row->status) {
                    case 'approved':
                        $labelClass = 'success';
                        break;
                    case 'pending':
                        $labelClass = 'warning';
                        break;
                    case 'rejected':
                        $labelClass = 'danger';
                        break;
                }

                return '<span class="badge badge-'.$labelClass.'-light">'.ucfirst($row->status).'</span>';
            })
            ->editColumn('action', function ($row) {
                return view('backend.inc.action', [
                    'edit' => 'backend.serviceman-document.edit',
                    'edit_permission' => 'backend.serviceman_document.edit',
                    'delete' => 'backend.serviceman-document.destroy',
                    'delete_permission' => 'backend.serviceman_document.destroy',
                    'data' => $row,
                ]);
            })
            ->editColumn('checkbox', function ($row) {
                if ($row->first() == 'Admin') {
                    return '<div class="form-check"><input type="checkbox" class="form-check-input" id="disable-select" disabled></div>';
                }

                return '<div class="form-check"><input type="checkbox" name="row" class="rowClass form-check-input" value='.$row->id.' id="rowId'.$row->id.'"></div>';
            })
            ->rawColumns(['checkbox', 'Image', 'action', 'created_at', 'status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(UserDocument $model): QueryBuilder
    {
        $serviceman_documents = $model->newQuery();
        
        $user = auth()->user();
        
        // If a specific serviceman ID is passed (for admin filter view)
        if (request()->id) {
            $serviceman_documents->where('user_id', request()->id);
        }

        // If logged-in user is a Provider → show only their documents
        if ($user && $user->hasRole(RoleEnum::SERVICEMAN)) {
            $serviceman_documents->where('user_id', $user->id);
        }

        // Ensure only serviceman-type users' documents appear (not serviceman, consumer, etc.)
        $serviceman_documents->whereHas('user', function ($q) {
            $q->whereHas('roles', function ($r) {
                $r->where('name', RoleEnum::SERVICEMAN);
            });
        });

        if (request()->order) {
            if ((bool) head(request()->order)['column']) {
                $index = head(request()->order)['column'];
                if (!isset(request()->columns[$index]['orderable'])) {
                    return $serviceman_documents;
                }
            }
        }

        return $serviceman_documents->orderBy('created_at', 'desc');

    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $user = auth()->user();
        $builder = $this->builder();
        $no_records_found = __('static.no_records_found');
        $documents = UserDocument::get();
        $builder->setTableId('servicemandocument-table');
        if ($user?->can('backend.serviceman_document.destroy')) {
            if($documents->count() > 1) {
                $builder->addColumn(['data' => 'checkbox', 'title' => '<div class="form-check"><input type="checkbox" class="form-check-input" title="Select All" id="select-all-rows" /> </div>', 'class' => 'title', 'orderable' => false, 'searchable' => false]);
            }
        }
        $builder->addColumn(['data' => 'Image', 'title' => __('static.image'), 'orderable' => false, 'searchable' => false])
        ->addColumn(['data' => 'user.name', 'title' => __('static.serviceman-document.serviceman_name'), 'orderable' => false, 'searchable' => true])
        ->addColumn(['data' => 'identity_no', 'title' => __('static.document_no'), 'orderable' => true, 'searchable' => true])
        ->addColumn(['data' => 'created_at', 'title' => __('static.created_at'), 'orderable' => true, 'searchable' => true]);

        if ($user->can('backend.serviceman_document.edit') || $user->can('backend.serviceman_document.destroy')) {
            if ($user->can('backend.serviceman_document.edit')) {
                $builder->addColumn(['data' => 'status', 'title' => __('static.status'), 'orderable' => true, 'searchable' => true]);
            }

            $builder->addColumn(['data' => 'action', 'title' => __('static.action'), 'orderable' => false, 'searchable' => false]);

        }

        return $builder->minifiedAjax()
        ->orderBy(1)
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
        return 'ServicemanDocument_'.date('YmdHis');
    }
}
