<?php

namespace App\Models;

use App\Enums\OrderFleetStatus;
use App\Enums\FleetPaymentStatus;
use App\Models\Scopes\ApprovedScope;
use Illuminate\Database\Eloquent\Model;
use EightyNine\Approvals\Models\ApprovableModel;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([ApprovedScope::class])]

class OrderFleet extends ApprovableModel
{
  use HasFactory;

  public function getStatus(): string
  {
    $date = $this->trip_date;

    $order = $this->order()->exists();

    return match (true) {
      $order => OrderFleetStatus::BOOKED->getLabel(),
      $date->isToday() => OrderFleetStatus::ON_TRIP->getLabel(),
      $date->isPast() => OrderFleetStatus::FINISHED->getLabel(),
      default => OrderFleetStatus::READY->getLabel(),
    };
  }

  public function getRemainingDay(): int
  {
    $date = $this->trip_date;

    return match (true) {
      $date->isToday() ||
      $date->isPast() => 0,
      default => today()->diffInDays($date),
    };
  }

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'code',
    'order_id',
    'employee_id',
    'fleet_id',
    'trip_date',
    // 'payment_status',
    // 'payment_date',
    // 'payment_amount',
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'id' => 'integer',
    'order_id' => 'integer',
    'employee_id' => 'integer',
    'fleet_id' => 'integer',
    'trip_date' => 'datetime',
    // 'payment_date' => 'datetime',
    // 'payment_amount' => 'integer',
    // 'payment_status' => FleetPaymentStatus::class,
  ];

  public function order(): BelongsTo
  {
    return $this->belongsTo(Order::class);
  }

  public function employee(): BelongsTo
  {
    return $this->belongsTo(Employee::class);
  }

  public function fleet(): BelongsTo
  {
    return $this->belongsTo(Fleet::class);
  }
}
