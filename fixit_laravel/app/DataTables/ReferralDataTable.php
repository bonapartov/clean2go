<?php

namespace App\DataTables;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\ReferralBonus;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use App\Enums\SymbolPositionEnum;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ReferralDataTable extends DataTable
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
            ->filterColumn('referred_id', function ($query, $keyword) {
                $query->whereHas('referred', function ($providerQuery) use ($keyword) {
                    $providerQuery->where('name', 'like', "%{$keyword}%")
                                ->orwhere('email', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('referrer_id', function ($query, $keyword) {
                $query->whereHas('referrer', function ($providerQuery) use ($keyword) {
                    $providerQuery->where('name', 'like', "%{$keyword}%")
                                ->orwhere('email', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('referrer_id', function ($row) {
                $referrer = $row->referrer;
                if ($referrer) {
                    return view('backend.inc.action', [
                        'info' => $referrer,
                        'route' => 'backend.consumer.general-info'
                    ]);
                }
                return '';
            })
            ->editColumn('referred_id', function ($row) {
                $referred = $row->referred;
                if ($referred) {
                    return view('backend.inc.action', [
                        'info' => $referred,
                        'route' => 'backend.consumer.general-info'
                    ]);
                }
                return '';
            })
            ->editColumn('booking_amount', function ($row) use ($currencySymbol, $symbolPosition) {
                $formattedAmount = number_format($row->booking_amount, 2);
                
                return ($symbolPosition === SymbolPositionEnum::LEFT)
                    ? $currencySymbol . '' . $formattedAmount 
                    : $formattedAmount . ' ' . $currencySymbol;
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
            ->editColumn('credited_at', function($row) {
                return $row->credited_at ?? 'N/A';
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
            ->editColumn('credited_at', function ($row) {
                return date('d-M-Y', strtotime($row->credited_at));
            })
            ->rawColumns(['created_at','status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ReferralBonus $model): QueryBuilder
    {
        if (auth()->user()->hasRole('admin')) {
            $referral = $model->newQuery();
        } else {
            $referral = $model->newQuery()->where('referrer_id', auth()->user()->id);
        }
        return $referral;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $no_records_found = __('static.no_records_found');

        return $this->builder()
            ->setTableId('referral-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
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
            Column::make('referrer_id')->title(__('static.dashboard.referrer'))->orderable(true)->searchable(true),
            Column::make('referred_id')->title(__('static.dashboard.referred_user'))->searchable(true),
            Column::make('referrer_bonus_amount')->title(__('static.dashboard.referrer_bonus'))->orderable(false)->searchable(true),
            Column::make('referred_bonus_amount')->title(__('static.dashboard.referred_bonus'))->orderable(true)->searchable(true),
            Column::make('status')->title(__('static.status'))->orderable(true)->searchable(true),
            Column::make('credited_at')->title(__('static.dashboard.credited_at'))->orderable(true)->searchable(false),
            Column::make('created_at')->title(__('static.created_at'))->orderable(false)->searchable(false),
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
