<?php

namespace App\Exports;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Helpers\Helpers;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProviderFilterExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = User::query()->role(RoleEnum::PROVIDER)->whereNull('deleted_at');

        $startDate     = request()->start_date;
        $endDate       = request()->end_date;
        $status        = request()->status;
        $serviceIds    = request()->services ? explode(',', request()->services) : [];
        $servicemenIds = request()->servicemen ? explode(',', request()->servicemen) : [];
        $types         = request()->types ? explode(',', request()->types) : [];

        if ($startDate && $endDate) {
            $query->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        if ($serviceIds) {
            $query->whereHas('services', function ($q) use ($serviceIds) {
                $q->whereIn('id', $serviceIds);
            });
        }

        if ($servicemenIds) {
            $query->whereHas('servicemans', function ($q) use ($servicemenIds) {
                $q->whereIn('id', $servicemenIds);
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($types) {
            $query->whereIn('type', $types);
        }

        return $query->get();
    }

    /**
     * Specify the columns for the export.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
           'name',
            'email',
            'password',
            'phone',
            'code',
            'system_reserve',
            'status',
            'is_featured',
            'provider_id',
            'created_by',
            'current_password',
            'new_password',
            'confirm_password',
            'type',
            'slug',
            'experience_interval',
            'is_verified',
            'experience_duration',
            'company_name',
            'company_email',
            'company_phone',
            'company_code',
            'description',
            'served',
            'fcm_token',
            'company_id',
            'location_cordinates'     
        ];
    }

    public function map($provider): array
    {

        return [
            $provider->name ?? 'N/A',
            $provider->email ?? 'N/A',
            $provider->password ?? 'N/A',
            $provider->phone ?? 'N/A',
            $provider->code ?? 'N/A',
            $provider->description ?? 'N/A',
            $provider->media->first()->original_url ?? 'N/A',
            $provider->type ?? 'N/A',
            $provider->getReviewRatingsAttribute() ?? '0.0',
            $provider->status ?? 'N/A',
            Helpers::getTotalProviderBookings($provider->id) ?? '0',
            $provider->servicemans->count() ?? '0',
            $provider->services->count() ?? '0',
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
            'Provider Name',
            'Provider Email',
            'Provider Password',
            'Provider Phone',
            'Provider Code',
            'Provider Description',
            'Provider Media',
            'Provider Type',
            'Provider Ratings',
            'Provider Status',
            'Earnings',
            'Total Bookings	', 
            'Total Servicemen', 
            'Total Services', 
        ];
    }
}