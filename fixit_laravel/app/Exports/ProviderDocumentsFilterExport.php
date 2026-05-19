<?php

namespace App\Exports;


use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\UserDocument;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProviderDocumentsFilterExport implements FromCollection,WithMapping,WithHeadings
{

    public function collection()
    {
        $provider_documents = UserDocument::query();
        
        $startDate = request()->start_date;
        $endDate   = request()->end_date;
        $statusIds = request()->status ? explode(',', request()->status) : [];
        $providerIds = request()->providers ? explode(',', request()->providers) : [];
        $documentIds = request()->documents ? explode(',', request()->documents) : [];

        if ($startDate && $endDate) {
            $provider_documents->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
        }

        if ($providerIds) {
            $provider_documents->whereHas('user', function ($q) use ($providerIds) {
                $q->whereIn('id', $providerIds);
            });
        }

        if ($documentIds) {
            $provider_documents->whereHas('document', function ($q) use ($documentIds) {
                $q->whereIn('id', $documentIds);
            });
        }

        if ($statusIds) {
            $provider_documents->whereIn('status', $statusIds);
        }

        return $provider_documents->get();
    }


    public function columns(): array
    {
        return [
            'user_id',
            'document_id',
            'notes',
            'identity_no',
            'status',
            'document_id',
            'document_image'
        ];
    }

    public function map($providerDocument): array
    {

        return [
            $providerDocument?->user_id,
            $providerDocument?->document_id,
            $providerDocument?->notes,
            $providerDocument?->identity_no,
            $providerDocument?->status,
            $providerDocument?->document_id,
            $providerDocument?->media?->first()?->original_url
        ];
    }

    public function headings(): array
    {
        return [
            'user_id',
            'document_id',
            'notes',
            'identity_no',
            'status',
            'document_id',
            'document_image'
        ];
    }

}
