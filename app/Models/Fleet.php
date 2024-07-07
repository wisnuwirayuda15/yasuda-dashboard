<?php

namespace App\Models;

use App\Enums\FleetSeat;
use App\Enums\FleetCategory;
use App\Models\Scopes\ApprovedScope;
use Illuminate\Database\Eloquent\Model;
use EightyNine\Approvals\Models\ApprovableModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([ApprovedScope::class])]

class Fleet extends ApprovableModel
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

  public static function getGroupOptionsByCategories(): array
  {
    return Fleet::query()
      ->select('category', 'id', 'name', 'seat_set')
      ->get()
      ->groupBy('category')
      ->mapWithKeys(function ($fleets, $category) {
        return [
          ucwords($category) . " Bus" => $fleets->mapWithKeys(function (Fleet $fleet) {
            return [$fleet->id => "{$fleet->name} • {$fleet->seat_set->getLabel()}"];
          })->toArray()
        ];
      })
      ->sortKeysDesc()
      ->toArray();
  }

  public function orderFleets(): HasMany
  {
    return $this->hasMany(OrderFleet::class);
  }
}
