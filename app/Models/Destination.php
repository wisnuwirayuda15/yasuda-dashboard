<?php

namespace App\Models;

use App\Enums\DestinationType;
use App\Models\Scopes\ApprovedScope;
use Illuminate\Database\Eloquent\Model;
use EightyNine\Approvals\Models\ApprovableModel;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([ApprovedScope::class])]

class Destination extends ApprovableModel
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

  public static function getOptionsWithPrice(): array
  {
    return self::query()
      ->select('name', 'id', 'weekday_price', 'weekend_price')
      ->get()
      ->mapWithKeys(function (self $destination) {
        $price = today()->isWeekday() ? $destination->weekday_price : $destination->weekend_price;
        return [$destination->id => "{$destination->name} • " . idr($price)];
      })
      ->toArray();
  }
}
