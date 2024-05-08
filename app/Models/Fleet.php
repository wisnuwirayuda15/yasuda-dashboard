<?php

namespace App\Models;

use App\Enums\FleetSeat;
use App\Enums\FleetCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fleet extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'image',
        'name',
        'description',
        'category',
        'seat_set',
        'pic_name',
        'pic_phone',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'seat_set' => FleetSeat::class,
        'category' => FleetCategory::class,
    ];

    public function orderFleets(): HasMany
    {
        return $this->hasMany(OrderFleet::class);
    }
}
