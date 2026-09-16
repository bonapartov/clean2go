<?php

namespace App\Exports;

use App\Enums\BookingStatusReq;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProvidersExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $providers = User::role(RoleEnum::PROVIDER)->where('status', true)->whereNull('deleted_at');
        return $this->filter($providers, request()->all());
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
            'description',
            'image',
            'system_reserve',
            'experience_interval',
            'experience_duration',
            'type',
            'status',
            'is_featured',
            'slug',
            'is_verified',
            'location_cordinates',
            'company_name',
            'company_email',
            'company_phone',
            'company_code',
            'company_description',
            // 'company_logo',
            'role_id',
            'zones',
        ];
    }

    public function map($provider): array
    {

        return [
            $provider->name ?? 'Н/Д',
            $provider->email ?? 'Н/Д',
            $provider->password ?? 'Н/Д',
            $provider->phone ?? 'Н/Д',
            $provider->code ?? 'Н/Д',
            $provider->description ?? 'Н/Д',
            $provider->media->first()->original_url ?? 'Н/Д',
            $provider->system_reserve ?? 'Н/Д',
            $provider->experience_interval ?? 'Н/Д',
            $provider->experience_duration ?? 'Н/Д',
            $provider->type ? __('static.' . $provider->type) : 'Н/Д',
            $provider->status ?? 'Н/Д',
            $provider->is_featured ?? 'Н/Д',
            $provider->slug ?? 'Н/Д',
            $provider->is_verified ?? 'Н/Д',
            $provider->location_cordinates ?? 'Н/Д',
            $provider?->company->name ?? 'Н/Д',
            $provider?->company->email ?? 'Н/Д',
            $provider?->company->phone ?? 'Н/Д',
            $provider?->company->code ?? 'Н/Д',
            $provider?->company->description ?? 'Н/Д',
            // $provider?->company->media?->first()?->original_url,
            $provider?->role?->id,
            $provider?->zones->pluck('id')->toArray() ?? []

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
            'type',
            'status',
            'is_featured',
            'slug',
            'is_verified',
            'location_cordinates',
            'company_name',
            'company_email',
            'company_phone',
            'company_code',
            'company_description',
            // 'company_logo',
            'role_id',
            'zones',
        ];
    }

    public function filter($providers, $request)
    {
        if(isset($request['provider']) && !in_array('all', $request['provider'])) {
            $providers = $providers->whereIn('id',$request['provider']);
        }

        if(isset($request['zone']) && !in_array('all', $request['zone'])) {
            $requestedZones = $request['zone'];
            $filteredProviders = $providers->get()->filter(function ($provider) use ($requestedZones) {
                $locationCoordinates = json_decode($provider->location_cordinates);

                if (isset($locationCoordinates->lat, $locationCoordinates->lng)) {
                    $zoneIds = Helpers::getZoneByPoint($locationCoordinates->lat, $locationCoordinates->lng)->pluck('id')->toArray();
                    return !empty(array_intersect($zoneIds, $requestedZones));
                }
                return false;
            });

            $providerIds = $filteredProviders->pluck('id')->toArray();
            $providers = $providers->whereIn('id', $providerIds);
        }

        if(isset($request['type']) && !in_array('all', $request['type'])) {
            $providers = $providers->whereIn('type', $request['type']);
        }

        if(isset($request['start_end_date']))
        {
            [$start_date, $end_date] = explode(' — ', $request['start_end_date']);
            $providers =  $providers->whereBetween('created_at', [$start_date, $end_date]);
        }

        return $providers->get();
    }
}
