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
      ->select('name', 'id', 'weekday_price', 'weekend_price', 'high_season_price')
      ->get()
      ->mapWithKeys(function (self $destination) {
        $weekdayPrice = idr($destination->weekday_price);
        $weekendPrice = idr($destination->weekend_price);
        $highSeasonPrice = idr($destination->high_season_price);
        $name = strtoupper($destination->name);
        // return [
        //   $destination->id =>
        //     view('filament.components.badges.default', ['text' => $destination->name, 'color' => 'success']) .
        //     "Weekday: {$weekdayPrice} • Weekend: {$weekendPrice}"
        // ];
        return [
          $destination->id => "{$name} • Weekday: {$weekdayPrice} • Weekend: {$weekendPrice} • High Season: {$highSeasonPrice}"
        ];
      })
      ->toArray();
  }
}
