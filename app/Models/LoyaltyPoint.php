<?php

namespace App\Models;

use App\Enums\CashFlow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyPoint extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'invoice_id',
    'cash_status',
    'description',
    'amount',
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'id' => 'integer',
    'invoice_id' => 'integer',
    'amount' => 'integer',
    'cash_status' => CashFlow::class,
  ];

  public function invoice(): BelongsTo
  {
    return $this->belongsTo(Invoice::class);
  }
}
