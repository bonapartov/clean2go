<?php

namespace App\Exports;

use App\Enums\BookingStatusReq;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ServicemanFilterExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $serviceman = User::role(RoleEnum::SERVICEMAN)->whereNull('deleted_at');
        $startDate = request()->start_date;
        $endDate   = request()->end_date;
        $providerIds = request()->providers ? explode(',', request()->providers) : [];
        $status   = request()->status;

        if ($startDate && $endDate) {
            $serviceman->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
        }

        if ($providerIds) {
            $serviceman->whereHas('provider', function ($q) use ($providerIds) {
                $q->whereIn('id', $providerIds);
            });
        }

        if ($status !== null && $status !== '') {
            $serviceman->where('status', $status);
        }

        return $serviceman->get();
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
            'name',
            'email',
            'password',
            'phone',
            'code',
            'description',
            'image',
            'system_reserve',
            'experience_interval',
            'experience_duration',
            'status',
            'role'
        ];
    }

    public function map($serviceman): array
    {
        return [
            $serviceman->provider_id,
            $serviceman->name ?? 'Н/Д',
            $serviceman->email ?? 'Н/Д',
            $serviceman->password ?? 'Н/Д',
            $serviceman->phone ?? 'Н/Д',
            $serviceman->code ?? 'Н/Д',
            $serviceman->description ?? 'Н/Д',
            $serviceman->media->first()->original_url ,
            $serviceman->system_reserve ?? 'Н/Д',
            $serviceman->experience_interval ?? 'Н/Д',
            $serviceman->experience_duration ?? 'Н/Д',
            $serviceman->status ?? 'Н/Д',
            $serviceman?->role?->id,
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
            'provider_id',
            'name',
            'email',
            'password',
            'phone',
            'code',
            'description',
            'image',
            'system_reserve',
            'experience_interval',
            'experience_duration',
            'status',
            'role'
        ];
    }

   
}
