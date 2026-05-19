<?php

namespace App\Exports;
use App\Models\ServicePackage;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ServicePackageFilterExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $servicesPackages = ServicePackage::query();

        $startDate = request()->start_date;
        $endDate   = request()->end_date;
        $status = request()->status;
        $providerIds = request()->providers ? explode(',', request()->providers) : [];
        $serviceIds = request()->services ? explode(',', request()->services) : [];

         if ($startDate && $endDate) {
            $servicesPackages->whereDate('created_at', '>=', $startDate)
                   ->whereDate('created_at', '<=', $endDate);
        }

        if ($serviceIds) {
            $servicesPackages->whereHas('services', function ($query) use ($serviceIds) {
                $query->whereIn('services.id', $serviceIds);
            });
        }

        if ($providerIds) {
            $servicesPackages->whereHas('user', function ($query) use ($providerIds) {
                $query->whereIn('id', $providerIds);
            });
        }

        if ($status !== null && $status !== '') {
            $servicesPackages->where('status', $status);
        }

        return $servicesPackages->get();
    }

    /**
     * Specify the columns for the export.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            'title',
            'hexa_code',
            'bg_color',
            'price',
            'discount',
            'description',
            'is_featured',
            'provider_id',
            'ended_at',
            'started_at',
            'status',
            'service_ids',
            'image',
        ];
    }

    public function map($servicesPackage): array
    {
        return [
            $servicesPackage->title,
            $servicesPackage->hexa_code,
            $servicesPackage->bg_color,
            $servicesPackage?->price,
            $servicesPackage?->discount,
            $servicesPackage?->description,
            $servicesPackage?->is_featured,
            $servicesPackage?->provider_id,
            $servicesPackage?->ended_at,
            $servicesPackage?->started_at,
            $servicesPackage?->status,
            $servicesPackage?->services?->pluck('id')?->toArray(),
            $servicesPackage->media?->first()->original_url,        
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
            'title',
            'hexa_code',
            'bg_color',
            'price',
            'discount',
            'description',
            'is_featured',
            'provider_id',
            'ended_at',
            'started_at',
            'status',
            'service_ids',
            'image',
        ];
    }


}
