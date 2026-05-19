<?php

namespace App\DataTables\Frontend;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\ReferralBonus;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use App\Enums\SymbolPositionEnum;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ConsumerReferralDataTable extends DataTable
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
            ->editColumn('referred_id', function ($row) {
                $referred = $row->referred;
                if ($referred) {
                    return view('backend.inc.action', [
                        'info' => $referred,
                    ]);
                }
                return '';
            })
            ->filterColumn('referred_id', function ($query, $keyword) {
                $query->whereHas('referred', function ($providerQuery) use ($keyword) {
                    $providerQuery->where('name', 'like', "%{$keyword}%")
                                ->orwhere('email', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('referrer_bonus_amount', function ($row) use ($currencySymbol, $symbolPosition) {
                $formattedAmount = number_format($row->referrer_bonus_amount, 2);
                
                return ($symbolPosition === SymbolPositionEnum::LEFT)
                    ? $currencySymbol . '' . $formattedAmount 
                    : $formattedAmount . ' ' . $currencySymbol;
            })
            ->editColumn('referred_bonus_amount', function ($row) use ($currencySymbol, $symbolPosition) {
                $formattedAmount = number_format($row->referred_bonus_amount, 2);
                
                return ($symbolPosition === SymbolPositionEnum::LEFT)
                    ? $currencySymbol . '' . $formattedAmount 
                    : $formattedAmount . ' ' . $currencySymbol;
            })
            ->editColumn('status', function ($row) {
                if($row->status === 'pending'){
                    return '<span class="badge booking-status-FDB448">'.$row->status.'</span>';
                }else{
                    return '<span class="badge booking-status-5465FF">'.$row->status.'</span>';
                }
            })
            ->editColumn('created_at', function ($row) {
                return date('d-M-Y', strtotime($row->created_at));
            })
            ->rawColumns(['created_at','status']);

    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ReferralBonus $model): QueryBuilder
    {
        return $model->where(function ($q) {
            $q->where('referrer_id', auth()->id());
        });
    
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $no_records_found = __('static.no_records_found');

        return $this->builder()
            ->setTableId('referral-data')
            ->columns($this->getColumns())
            ->minifiedAjax() 
            ->orderBy(4)
            ->selectStyleSingle()->parameters([
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
        return [
            Column::make('referred_id')->title(__('static.dashboard.referred_user'))->orderable(true)->searchable(true),
            Column::make('referrer_bonus_amount')->title(__('static.dashboard.referrer_bonus'))->orderable(true)->searchable(true),
            Column::make('referred_bonus_amount')->title(__('static.dashboard.referred_bonus'))->orderable(true)->searchable(true),
            Column::make('status')->title(__('static.status'))->orderable(true)->searchable(true),
            Column::make('created_at')->title(__('static.created_at'))->orderable(true)->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Referral_'.date('YmdHis');
    }
}
