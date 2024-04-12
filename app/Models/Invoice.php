<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
  use HasFactory, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'code',
    'order_id',
    'status',
    'costs_detail',
    'special_notes',
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'id' => 'integer',
    'order_id' => 'integer',
    'costs_detail' => 'array',
  ];

  /**
   * The default main costs for invoice.
   *
   * @var array
   */
  protected $defaultMainCosts = [
    1 => [
      "name" => "Program",
      "qty" => 0,
      "price" => 75000,
      "cashback" => 0,
    ],
    2 => [
      "name" => "Ibu & Anak Pangku",
      "qty" => 0,
      "price" => 400000,
      "cashback" => 30000,
    ],
    3 => [
      "name" => "Beli Kursi",
      "qty" => 0,
      "price" => 200000,
      "cashback" => 15000,
    ],
    4 => [
      "name" => "Tambahan Orang",
      "qty" => 0,
      "price" => 315000,
      "cashback" => 20000,
    ],
    5 => [
      "name" => "Pembina",
      "qty" => 0,
      "price" => 100000,
      "cashback" => 0,
    ],
    6 => [
      "name" => "Special Rate",
      "qty" => 0,
      "price" => 0,
      "cashback" => 0,
    ]
  ];

  public function getDefaultMainCosts(): array
  {
    return $this->defaultMainCosts;
  }

  public function profitLoss(): HasOne
  {
    return $this->hasOne(ProfitLoss::class);
  }

  public function order(): BelongsTo
  {
    return $this->belongsTo(Order::class);
  }
}
