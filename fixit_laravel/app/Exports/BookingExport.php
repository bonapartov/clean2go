<?php

namespace App\Exports;

use App\Helpers\Helpers;
use App\Models\Booking;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class BookingExport implements FromCollection,WithMapping,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $bookings = Booking::whereNotNull('parent_id');
        return $this->filter($bookings, request()->all());
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
            Helpers::formatBookingStatusName($booking->booking_status) ?: 'Н/Д',
            Helpers::formatPaymentMethod($booking->payment_method) ?: 'Н/Д',
            Helpers::formatPaymentStatus($booking->payment_status) ?: 'Н/Д',
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

    public function filter($bookings, $request)
    {
        if(isset($request['provider']) && !in_array('all', $request['provider'])) {
            $bookings = $bookings->whereIn('provider_id', $request['provider']);
        }

        if(isset($request['user']) && !in_array('all', $request['user'])) {
            $bookings = $bookings->whereIn('consumer_id', $request['user']);
        }

        if(isset($request['booking_status']) && !in_array('all', $request['booking_status'])) {
            $bookings = $bookings->whereIn('booking_status_id',$request['booking_status']);
        }

        if(isset($request['payment_status']) && !in_array('all', $request['payment_status'])) {
            $requestStatuses = array_map('strtoupper', $request['payment_status']);
            $bookings = $bookings->whereIn('payment_status', $requestStatuses);
        }

        if(isset($request['service']) && !in_array('all', $request['service'])) {
            $bookings = $bookings->whereIn('service_id', $request['service']);
        }
        
        if(isset($request['start_end_date']))
        {
            [$start_date, $end_date] = explode(' — ', $request['start_end_date']);
            $bookings =  $bookings->whereBetween('created_at', [$start_date, $end_date]);
        }

        return $bookings->get();

    }
}