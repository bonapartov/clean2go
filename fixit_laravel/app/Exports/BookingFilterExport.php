<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class BookingFilterExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    
    public function collection()
    {
        $query = Booking::query()->whereNull('parent_id');

        $startDate = request()->start_date;
        $endDate = request()->end_date;
        $serviceIds = request()->services ? explode(',', request()->services) : [];
        $consumerIds = request()->consumers ? explode(',', request()->consumers) : [];
        $providerIds = request()->providers ? explode(',', request()->providers) : [];
        $statuses = request()->statuses ? explode(',', request()->statuses) : [];
        $paymentStatuses = request()->payment_statuses ? explode(',', request()->payment_statuses) : [];
        $paymentMethods = request()->payment_methods ? explode(',', request()->payment_methods) : [];

        if ($startDate && $endDate) {
            $query->whereHas('sub_bookings', function($q) use ($startDate, $endDate) {
                $q->whereDate('date_time', '>=', $startDate)
                ->whereDate('date_time', '<=', $endDate);
            });
        }

        if ($serviceIds) {
            $query->whereHas('sub_bookings', function($q) use ($serviceIds) {
                $q->whereIn('service_id', $serviceIds);
            });
        }

        if ($consumerIds) {
            $query->whereHas('sub_bookings', function($q) use ($consumerIds) {
                $q->whereIn('consumer_id', $consumerIds);
            });
        }

        if ($providerIds) {
            $query->whereHas('sub_bookings', function($q) use ($providerIds) {
                $q->whereIn('provider_id', $providerIds);
            });
        }

        if ($statuses) {
            $query->whereHas('sub_bookings', function($q) use ($statuses) {
                $q->whereIn('booking_status_id', $statuses);
            });
        }

        if ($paymentStatuses) {
            $query->whereHas('sub_bookings', function($q) use ($paymentStatuses) {
                $q->whereIn('payment_status', $paymentStatuses);
            });
        }

        if ($paymentMethods) {
            $query->whereIn('payment_method', $paymentMethods);
        }

        return $query->with(['consumer', 'provider', 'service', 'booking_status', 'created_by'])->get();
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
            'booking_number',
            'consumer_id',
            'provider_id',
            'service_id',
            'service_package_id',
            'service_price',
            'type',
            'tax',
            'per_serviceman_charge',
            'required_servicemen',
            'total_extra_servicemen',
            'total_servicemen',
            'coupon_total_discount',
            'platform_fees',
            'total_extra_servicemen_charge',
            'subtotal',
            'total',
            'date_time',
            'parent_id',
            'booking_status_id',
            'payment_method',
            'payment_status',
            'description',
            'created_by_id',
            'platform_fees_type',                                            
        ];
    }

    public function map($booking): array
    {
        return [
            $booking->id ?? 'N/A',
            $booking->booking_number ?? 'N/A',
            $booking->consumer ? ($booking->consumer->name ?? 'N/A') : 'N/A',
            $booking->provider ? ($booking->provider->name ?? 'N/A') : 'N/A',
            $booking->service ? ($booking->service->title ?? 'N/A') : 'N/A',
            $booking->service_package_id ?? 'N/A',
            $booking->service_price ?? 'N/A',
            $booking->type ?? 'N/A',
            $booking->tax ?? 'N/A',
            $booking->per_serviceman_charge ?? 'N/A',
            $booking->required_servicemen ?? 'N/A',
            $booking->total_extra_servicemen ?? 'N/A',
            $booking->total_extra_servicemen_charge ?? 'N/A',
            $booking->total_servicemen ?? 'N/A',
            $booking->coupon_total_discount ?? 0,
            $booking->platform_fees_type ?? 'N/A',
            $booking->platform_fees ?? 'N/A',
            $booking->subtotal ?? 'N/A',
            $booking->total ?? 'N/A',
            $booking->date_time ?? 'N/A',
            $booking->booking_status->name ? ($booking->booking_status->name ?? 'N/A') : 'N/A',
            $booking->payment_method ?? 'N/A',
            $booking->payment_status ?? 'N/A',
            $booking->description ?? 'N/A',
            $booking->created_by->name ?? 'N/A',
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
            'Booking ID',
            'Booking Number',
            'Consumer Name',
            'Provider Name',
            'Service Title',
            'Service Package',
            'Service Price', 
            'Type', 
            'Tax', 
            'Per Serviceman Charge', 
            'Required Servicemen', 
            'Total Extra Servicemen', 
            'Total Extra Servicemen Charge', 
            'Total Servicemen', 
            'Coupon Total Discount', 
            'Platform Fees Type', 
            'Platform Fees', 
            'Subtotal', 
            'Total', 
            'Booking Date', 
            'Booking Status', 
            'Payment Method', 
            'Payment Status', 
            'Description', 
            'Created By', 
        ];
    }

    
    
}