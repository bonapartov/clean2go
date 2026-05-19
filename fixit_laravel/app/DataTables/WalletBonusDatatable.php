<?php

namespace App\DataTables;

use App\Helpers\Helpers;
use App\Models\WalletBonus;
use Yajra\DataTables\EloquentDataTable;
use App\Enums\SymbolPositionEnum;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class WalletBonusDatatable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $currencySetting = Helpers::getSettings()['general']['default_currency'];
        $currencySymbol = $currencySetting->symbol;
        $symbolPosition = $currencySetting->symbol_position;
        
        return (new EloquentDataTable($query))
            ->editColumn('name', function ($row) {
                return $row->name ?? 'N/A'; 
            })
            ->editColumn('description', function ($row) {
                return $row->description ?? 'N/A'; 
            })
            ->editColumn('status', function ($row) {
                return view('backend.inc.action', [
                    'toggle' => $row,
                    'name'   => 'status',
                    'route'  => 'backend.wallet-bonus.status',
                    'value'  => $row->status,
                ]);
            })
            ->editColumn('min_top_up_amount', function ($row) use ($currencySymbol, $symbolPosition) {
                $formattedAmount = number_format($row->min_top_up_amount, 2);
                
                return ($symbolPosition === SymbolPositionEnum::LEFT)
                    ? $currencySymbol . '' . $formattedAmount 
                    : $formattedAmount . ' ' . $currencySymbol;
            })
            ->editColumn('max_bonus', function ($row) use ($currencySymbol, $symbolPosition) {
                $formattedAmount = number_format($row->max_bonus, 2);
                
                return ($symbolPosition === SymbolPositionEnum::LEFT)
                    ? $currencySymbol . '' . $formattedAmount 
                    : $formattedAmount . ' ' . $currencySymbol;
            })
            ->editColumn('type', function ($row) {
                return $row->type . ' : ' . ($row->type == 'fixed'
                    ? '$' . $row->bonus
                    : $row->bonus . '%');
            })
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->addColumn('action', function ($row) {
                return view('backend.inc.action', [
                    'edit' => 'backend.walletBonus.edit',
                    'edit_permission' => 'backend.wallet_bonus.edit',
                    'locale' => Session::get('locale', app()->getLocale()),
                    'delete' => 'backend.walletBonus.destroy',
                    'delete_permission' => 'backend.wallet_bonus.destroy',
                    'data'   => $row,
                ]);
            })
            ->editColumn('checkbox', function ($row) {
                if ($row->first() == 'Admin') {
                    return '<div class="form-check"><input type="checkbox" class="form-check-input" id="disable-select" disabled></div>';
                }

                return '<div class="form-check"><input type="checkbox" name="row" class="rowClass form-check-input" value='.$row->id.' id="rowId'.$row->id.'"></div>';
            })
            ->rawColumns(['checkbox', 'created_at', 'status', 'title', 'type','action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(WalletBonus $model): QueryBuilder
    {
        $query = $model->newQuery()->whereNotNull('bonus');

        $startDate = request()->start_date;
        $endDate   = request()->end_date;
        $status = request()->status;
        $price = request()->price;
        $typeIds = request()->type;

        if ($startDate && $endDate) {
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
        }

        if ($price) {
            [$min, $max] = explode(';', $price);
            $query->whereBetween('bonus', [(float) $min, (float) $max]);
        }

        if ($typeIds) { 
            $query->where('type', $typeIds);
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $user = auth()->user();
        $settings = Helpers::getSettings();
        $walletBonuses = WalletBonus::query()->get();
        $builder = $this->builder();
        $no_records_found = __('static.no_records_found');
        $builder->setTableId('wallet-bonus-table');

         if ($user->can('backend.wallet_bonus.destroy')) {
            if($walletBonuses->count() > 1) {
                $builder->addColumn(['data' => 'checkbox', 'title' => '<div class="form-check"><input type="checkbox" class="form-check-input" title="Select All" id="select-all-rows" /> </div>', 'class' => 'title', 'orderable' => false, 'searchable' => false]);
             }
        }
        $builder
            ->addColumn(['data' => 'name', 'title' => __('static.name'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'type', 'title' => __('static.type'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'min_top_up_amount', 'title' => __('static.min_top_up_amount'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'max_bonus', 'title' => __('static.max_bonus'), 'orderable' => true, 'searchable' => true])
            ->addColumn(['data' => 'created_at', 'title' => __('static.created_at'), 'orderable' => true, 'searchable' => true]);
            
            if ($user->can('backend.wallet_bonus.edit') || $user->can('backend.wallet_bonus.destroy')) {
                if ($user->can('backend.wallet_bonus.edit')){
                    $builder->addColumn(['data' => 'status', 'title' => __('static.status'), 'orderable' => true, 'searchable' => false]);
                }
                $builder->addColumn(['data' => 'action','title' => __('static.action'),'orderable' => false,'searchable' => false,
            ]);
    }
        return $builder->minifiedAjax()
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
        return 'WalletBonus_'.date('YmdHis');
    }
}
