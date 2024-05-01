<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'customer_id',
        'regency_id',
        'destinations',
        'trip_date',
        'description',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'customer_id' => 'integer',
        'regency_id' => 'integer',
        'destinations' => 'array',
        'trip_date' => 'datetime',
    ];

    public function orderFleets(): HasMany
    {
        return $this->hasMany(OrderFleet::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }
}
