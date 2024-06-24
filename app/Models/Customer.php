<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Enums\CustomerCategory;
use Illuminate\Database\Eloquent\Model;
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

  public function orders(): HasMany
  {
    return $this->hasMany(Order::class);
  }

  public function loyaltyPoints(): HasMany
  {
    return $this->hasMany(LoyaltyPoint::class);
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
