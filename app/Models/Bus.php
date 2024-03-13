<?php

namespace App\Models;

use App\Enums\BusType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bus extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'image',
        'name',
        'description',
        'seat_total',
        'left_seat',
        'right_seat',
        'type',
        'price',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'price' => 'integer',
        'type' => BusType::class
    ];

    public function busAvailability(): HasMany
    {
        return $this->hasMany(BusAvailability::class);
    }
}
