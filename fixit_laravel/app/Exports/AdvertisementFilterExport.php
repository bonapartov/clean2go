<?php

namespace App\Exports;

use App\Models\Advertisement;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class AdvertisementFilterExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $advertisements = Advertisement::query();
        $startDate = request()->start_date;
        $endDate   = request()->end_date;
        $status = request()->status;    
        $zoneIds   = request()->zones ? explode(',', request()->zones) : [];
        $providerIds = request()->providers ? explode(',', request()->providers) : [];
        $typeIds   = request()->type;
        $advertisementScreen = request()->advertisementScreen ;

        
        if ($startDate && $endDate) {
            $advertisements->whereDate('created_at', '>=', $startDate)
              ->whereDate('created_at', '<=', $endDate);
        }

        if ($zoneIds) {
            $advertisements->whereHas('zone_id', function ($q) use ($zoneIds) {
                $q->whereIn('id', $zoneIds);
            });
        }

        if ($typeIds) {
            $advertisements->whereIn('type', (array) $typeIds);
        }

        if ($advertisementScreen) {
            $advertisements->where('screen', $advertisementScreen);
        }

        if ($providerIds) {
            $advertisements->whereIn('provider_id', $providerIds);
        }

        if ($status) {
            $advertisements->where('status', $status);
        }

        return $advertisements->get();
    }

    /**
     * Specify the columns for the export.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            'provider_id',
            'images',
            'type',
            'screen',
            'status',
            'start_date',
            'end_date',
            'created_by',
            'zone',
            'banner_type',
            'video_link',
            'price'

        ];
    }

    public function map($advertisements): array
    {
        return [
            $advertisements->id ?? 'N/A',
            $advertisements->provider_id ?? 'N/A',
            $advertisements->type ?? 'N/A',
            $advertisements->screen ?? 'N/A',
            $advertisements->status ?? 'N/A',
            $advertisements->start_date ?? 'N/A',
            $advertisements->end_date ?? 'N/A',
            $advertisements->created_by ?? 'N/A',
            $advertisements->zone ?? 'N/A',
            $advertisements->banner_type ?? 'N/A',
            $advertisements->video_link ?? 'N/A',
            $advertisements->price ?? 'N/A',
        ];
    }

     /**
     * Get the headings for the export file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
           'id',
            'provider_id',
            'type',
            'screen',
            'status',
            'start_date',
            'end_date',
            'created_by',
            'zone',
            'banner_type',
            'video_link',
            'price'
        ];
    }
}