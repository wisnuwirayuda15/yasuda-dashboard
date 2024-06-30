<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Enums\CustomerCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'code',
    'name',
    'address',
    'category',
    'regency_id',
    'district_id',
    'headmaster',
    'operator',
    'phone',
    'email',
    'lat',
    'lng',
    'status',
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'id' => 'integer',
    'regency_id' => 'integer',
    'district_id' => 'integer',
    'category' => CustomerCategory::class,
    'status' => CustomerStatus::class,
  ];

  public function getBalance(bool $formatted = false): int|float|string
  {
    $points = LoyaltyPoint::whereHas('invoice.order.customer', function (Builder $query) {
      $query->where('id', $this->id);
    })->get()->sum('amount');

    $rewards = $this->rewards()->get()->sum('amount');

    $balance = (float) $points - (float) $rewards;

    if ($formatted) {
      return idr((float) $balance);
    }

    return (float) $balance;
  }

  public function orders(): HasMany
  {
    return $this->hasMany(Order::class);
  }

  public function rewards(): HasMany
  {
    return $this->hasMany(Reward::class);
  }

  public function regency(): BelongsTo
  {
    return $this->belongsTo(Regency::class);
  }

  public function district(): BelongsTo
  {
    return $this->belongsTo(District::class);
  }
}
