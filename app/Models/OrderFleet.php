<?php

namespace App\Models;

use Carbon\Carbon;
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
    /** @var Carbon */
    $date = $this->trip_date;

    /** @var Order */
    $order = $this->order;

    /** @var ?Invoice */
    $inv = $order?->invoice;

    $tr = $this->employee_id;

    return match (true) {
      (bool) $inv => OrderFleetStatus::ORDERED->getLabel(),
      $date->isToday() => OrderFleetStatus::ON_TRIP->getLabel(),
      $date->isPast() && ((bool) !$order || (bool) !$tr) => OrderFleetStatus::CANCELED->getLabel(),
      (bool) $order => OrderFleetStatus::BOOKED->getLabel(),
      $date->isPast() => OrderFleetStatus::FINISHED->getLabel(),
      default => OrderFleetStatus::READY->getLabel(),
    };
  }

  public function getStatusColor(): array|string
  {
    return match ($this->getStatus()) {
      OrderFleetStatus::CANCELED->getLabel() => OrderFleetStatus::CANCELED->getColor(),
      OrderFleetStatus::ORDERED->getLabel() => OrderFleetStatus::ORDERED->getColor(),
      OrderFleetStatus::BOOKED->getLabel() => OrderFleetStatus::BOOKED->getColor(),
      OrderFleetStatus::ON_TRIP->getLabel() => OrderFleetStatus::ON_TRIP->getColor(),
      OrderFleetStatus::FINISHED->getLabel() => OrderFleetStatus::FINISHED->getColor(),
      default => OrderFleetStatus::READY->getColor(),
    };
  }

  public function getRemainingDay(): int
  {
     /** @var Carbon */
    $date = $this->trip_date;

    return match (true) {
      $date->isToday() || $date->isPast() => 0,
      default => today()->diffInDays($date),
    };
  }

  public function getRemainingDayColor(): array|string
  {
    $day = $this->getRemainingDay();

    return match ($day) {
      OrderFleetStatus::ON_TRIP->getLabel() => OrderFleetStatus::ON_TRIP->getColor(),
      default => match (true) {
          $day <= 7 => 'danger',
          $day <= 30 => 'warning',
          default => 'success',
        },
    };
  }

  public function isOrdered(): bool
  {
    return $this->getStatus() === OrderFleetStatus::ORDERED->getLabel();
  }

  public function isCanceled(): bool
  {
    return $this->getStatus() === OrderFleetStatus::CANCELED->getLabel();
  }

  public function isFinished(): bool
  {
    return (bool) $this->order?->invoice?->tourReport;
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
    'payment_status',
    'payment_date',
    'payment_amount',
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
