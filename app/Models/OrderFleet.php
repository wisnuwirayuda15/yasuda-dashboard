<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderFleet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'order_id',
        'fleet_id',
        'trip_date',
        'payment_status',
        'payment_date',
        'payment_amount',
        'tour_leader_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'order_id' => 'integer',
        'fleet_id' => 'integer',
        'trip_date' => 'datetime',
        'payment_date' => 'datetime',
        'payment_amount' => 'integer',
        'tour_leader_id' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class);
    }

    public function tourLeader(): BelongsTo
    {
        return $this->belongsTo(TourLeader::class);
    }
}
