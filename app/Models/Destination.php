<?php

namespace App\Models;

use App\Enums\DestinationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Destination extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name',
    'type',
    'marketing_name',
    'marketing_phone',
    'weekday_price',
    'weekend_price',
    'high_season_price',
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'id' => 'integer',
    'weekday_price' => 'integer',
    'weekend_price' => 'integer',
    'high_season_price' => 'integer',
    'type' => DestinationType::class,
  ];
}
