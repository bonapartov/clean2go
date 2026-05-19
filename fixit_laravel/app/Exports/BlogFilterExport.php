<?php

namespace App\Exports;

use App\Models\Blog;
use App\Models\Booking;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class BlogFilterExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    
    public function collection()
    {
        $blogs = Blog::query();
        $startDate = request()->start_date;
        $endDate   = request()->end_date;
        $status = request()->status;
        $tagsIds = request()->tags ? explode(',', request()->tags) : [];
        $categoryIds = request()->categories ? explode(',', request()->categories) : [];

        if ($startDate && $endDate) {
            $blogs->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
        }

        if ($categoryIds) {
            $blogs->whereIn('id', $categoryIds);
        }

        if ($tagsIds) {
            $blogs->whereHas('tags', function ($query) use ($tagsIds) {
                $query->whereIn('tags.id', $tagsIds);
            });
        }

        if ($status !== null && $status !== '') {
            $blogs->where('status', $status);
        }

        return $blogs->get();
    }

    /**
     * Specify the columns for the export.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            'id',
            'title',
            'description',
            'content',
            'meta_title',
            'meta_description',
            'is_featured',
            'status',
            'created_by_id',                                    
        ];
    }

    public function map($blog): array
    {
        return [
            $blog->id ?? 'N/A',
            $blog->title ?? 'N/A',
            $blog->description ?? 'N/A',
            $blog->content ?? 'N/A',
            $blog->meta_title ?? 'N/A',
            $blog->meta_description ?? 'N/A',
            $blog->is_featured ?? 'N/A',
            $blog->status ?? 'N/A',
            $blog->created_by_id ?? 'N/A',
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
            'title',
            'description',
            'content',
            'meta_title',
            'meta_description',
            'is_featured',
            'status',
            'created_by_id',      
        ];
    }
}