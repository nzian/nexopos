<?php
/**
 * Jiyon resort Model
 * @since 1.0
 **/
namespace Modules\Jresort\Models;

use App\Models\NsModel;

class Booking extends NsModel
{
    protected $table = 'bookings';
    protected $fillable = [
        'room_number',
        'date',
        'status',
        'phone_number',
        'adults',
        'children',
        'stay_type',
        'is_event',
        'notes',
        'paid_amount',
        'discount',
        'service_charge',
    ];

    protected $dates = ['date'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'phone_number', 'phone');
    }
}
