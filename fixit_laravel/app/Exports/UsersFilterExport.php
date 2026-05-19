<?php

namespace App\Exports;

use App\Enums\RoleEnum;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersFilterExport implements FromCollection, WithMapping, WithHeadings
{
    public function collection()
    {
        $users = User::role(RoleEnum::CONSUMER)->where('system_reserve', 0);
        $startDate = request()->start_date;
        $endDate   = request()->end_date;
        $status = request()->status;

        if ($startDate && $endDate) {
            $users->whereDate('created_at', '>=', $startDate)
                      ->whereDate('created_at', '<=', $endDate);
        }

        if ($status !== null && $status !== '') {
             $users->where('status', $status);
        }

        return $users->get();
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
           'code',
           'phone',
           'status',
           'password',
           'is_featured',
           'description',
           'image',
           'role',
        ];
    }

    public function map($user): array
    {
        return [
            $user?->name,
            $user?->email,
            $user?->code,
            $user?->phone,
            $user?->status,
            $user?->password,
            $user?->is_featured,
            $user?->description,
            $user?->media[0]?->original_url ?? null,
            $user?->role->id,
        ];
    }

 
    public function headings(): array
    {
        return [
            'name',
            'email',
            'code',
            'phone',
            'status',
            'password',
            'is_featured',
            'description',
            'image',
            'role',
        ];
    }

    public function filter($users, $request)
    {
        return $users->get();
    }
}
