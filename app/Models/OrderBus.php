<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderBus extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'tour_leader_id',
        'bus_availability_id',
        'order_tour_leader_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'order_id' => 'integer',
        'tour_leader_id' => 'integer',
        'bus_availability_id' => 'integer',
        'order_tour_leader_id' => 'integer',
    ];

    public function orderTourLeader(): BelongsTo
    {
        return $this->belongsTo(OrderTourLeader::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function tourLeader(): BelongsTo
    {
        return $this->belongsTo(TourLeader::class);
    }

    public function busAvailability(): BelongsTo
    {
        return $this->belongsTo(BusAvailability::class);
    }

    public function tourLeader(): HasOne
    {
        return $this->hasOne(TourLeader::class);
    }
}
