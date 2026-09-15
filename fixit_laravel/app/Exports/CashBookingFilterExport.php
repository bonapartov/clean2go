<?php

namespace App\Exports;

use App\Enums\PaymentMethod;
use App\Models\Booking;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class CashBookingFilterExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    
    public function collection()
    {
        $query = Booking::query()->whereNull('parent_id')->where('payment_method', PaymentMethod::COD);

        $startDate = request()->start_date;
        $endDate   = request()->end_date;
        $serviceIds = request()->services ? explode(',', request()->services) : [];
        $consumerIds = request()->consumers ? explode(',', request()->consumers) : [];
        $providerIds = request()->providers ? explode(',', request()->providers) : [];
        $statuses = request()->statuses ? explode(',', request()->statuses) : [];
        $paymentStatuses = request()->payment_statuses ? explode(',', request()->payment_statuses) : [];

        if ($startDate && $endDate) {
            $query->whereHas('sub_bookings', function ($query) use ($startDate, $endDate) {
                $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
            });
        }

        if ($serviceIds) {
            $query->whereHas('sub_bookings', function ($query) use ($serviceIds) {
                $query->whereIn('service_id', $serviceIds);
            });
        }

        if ($consumerIds) {
            $query->whereHas('sub_bookings', function ($query) use ($consumerIds) {
                $query->whereIn('consumer_id', $consumerIds);
            });
        }

        if ($providerIds) {
            $query->whereHas('sub_bookings', function ($query) use ($providerIds) {
                $query->whereIn('provider_id', $providerIds);
            });
        }

        if ($statuses) {
            $query->whereHas('sub_bookings', function ($query) use ($statuses) {
                $query->whereIn('booking_status_id', $statuses);
            });
        }

        if ($paymentStatuses) {
            $query->whereHas('sub_bookings', function ($query) use ($paymentStatuses) {
                $query->whereIn('payment_status', $paymentStatuses);
            });
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
            $booking->id ?? 'Н/Д',
            $booking->booking_number ?? 'Н/Д',
            $booking->consumer ? ($booking->consumer->name ?? 'Н/Д') : 'Н/Д',
            $booking->provider ? ($booking->provider->name ?? 'Н/Д') : 'Н/Д',
            $booking->service ? ($booking->service->title ?? 'Н/Д') : 'Н/Д',
            $booking->service_package_id ?? 'Н/Д',
            $booking->service_price ?? 'Н/Д',
            $booking->type ?? 'Н/Д',
            $booking->tax ?? 'Н/Д',
            $booking->per_serviceman_charge ?? 'Н/Д',
            $booking->required_servicemen ?? 'Н/Д',
            $booking->total_extra_servicemen ?? 'Н/Д',
            $booking->total_extra_servicemen_charge ?? 'Н/Д',
            $booking->total_servicemen ?? 'Н/Д',
            $booking->coupon_total_discount ?? 0,
            $booking->platform_fees_type ?? 'Н/Д',
            $booking->platform_fees ?? 'Н/Д',
            $booking->subtotal ?? 'Н/Д',
            $booking->total ?? 'Н/Д',
            $booking->date_time ?? 'Н/Д',
            $booking->booking_status->name ? ($booking->booking_status->name ?? 'Н/Д') : 'Н/Д',
            $booking->payment_method ?? 'Н/Д',
            $booking->payment_status ?? 'Н/Д',
            $booking->description ?? 'Н/Д',
            $booking->created_by->name ?? 'Н/Д',
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