<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Models\Scopes\ApprovedScope;
use Illuminate\Database\Eloquent\Model;
use EightyNine\Approvals\Models\ApprovableModel;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([ApprovedScope::class])]

class Invoice extends ApprovableModel
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'code',
    'order_id',
    'main_costs',
    'submitted_shirt',
    'teacher_shirt_qty',
    'adult_shirt_qty',
    'child_shirt_price',
    'teacher_shirt_price',
    'adult_shirt_price',
    'adjusted_seat',
    'down_payments',
    'other_cost',
    'notes',
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'id' => 'integer',
    'order_id' => 'integer',
    'main_costs' => 'array',
    'child_shirt_price' => 'integer',
    'teacher_shirt_price' => 'integer',
    'adult_shirt_price' => 'integer',
    'down_payments' => 'array',
    'other_cost' => 'integer',
  ];

  public function getMainCostItem(string $slug): array
  {
    return collect($this->main_costs)->firstWhere('slug', $slug);
  }

  public function getTotalSeat(): float|int
  {
    $order = $this->order;

    if (filled($order->orderFleets)) {
      $seats = 0;

      foreach ($order->orderFleets as $orderFleet) {
        $fleet = $orderFleet->fleet;
        $seats = $fleet->seat_set->value + $seats;
      }

      return $seats;
    }

    return 0;
  }

  public function getTotalQty(): float|int
  {
    return array_sum(array_map(fn($cost) => $cost['qty'], $this->main_costs)) ?: 0;
  }

  public function getEmptySeat(): float|int
  {
    return $this->getTotalSeat() - $this->getTotalQty() - $this->adjusted_seat;
  }

  public function getSeatCharge(): float|int
  {
    $seat = $this->getMainCostItem('beli-kursi');

    return 0.5 * $this->getEmptySeat() * ($seat['price'] - $seat['cashback']);
  }

  public function getAdditionalShirtsTotal(): float|int
  {
    $program = $this->getMainCostItem('program')['qty'];
    $anak = $this->getMainCostItem('ibu-anak-pangku')['qty'];

    $paket = $program + $anak;

    $kaosPaket = $this->submitted_shirt - $paket;

    $child = $this->child_shirt_price * $kaosPaket;
    $adult = $this->adult_shirt_qty * $this->adult_shirt_price;
    $teacher = $this->teacher_shirt_qty * $this->teacher_shirt_price;

    return $child + $adult + $teacher;
  }

  public function getTotalNetTransactions(): float|int
  {
    return array_sum(
      array_map(
        fn($cost) => ($cost['qty'] * $cost['price']) - ($cost['qty'] * $cost['cashback']),
        $this->main_costs
      )
    ) ?: 0;
  }

  public function getTotalTransactions(): float|int
  {
    return $this->getTotalNetTransactions() + $this->getSeatCharge() + $this->getAdditionalShirtsTotal() + $this->other_cost;
  }

  public function getDownPaymentsAmount(): float|int
  {
    return array_sum(array_map(fn($dp) => $dp['amount'], $this->down_payments)) ?: 0;
  }

  public function getTotalPayment(): float|int
  {
    return $this->getTotalTransactions() - $this->getDownPaymentsAmount();
  }

  public function getPaymentStatus(): InvoiceStatus
  {
    $payment = $this->getTotalPayment();

    return match (true) {
      $payment == 0 => InvoiceStatus::PAID_OFF,
      $payment > 0 => InvoiceStatus::UNDER_PAYMENT,
      default => InvoiceStatus::OVER_PAYMENT,
    };
  }

  public function profitLoss(): HasOne
  {
    return $this->hasOne(ProfitLoss::class);
  }

  public function tourReport(): HasOne
  {
    return $this->hasOne(TourReport::class);
  }

  public function shirt(): HasOne
  {
    return $this->hasOne(Shirt::class);
  }

  public function loyaltyPoint(): HasOne
  {
    return $this->hasOne(LoyaltyPoint::class);
  }

  public function order(): BelongsTo
  {
    return $this->belongsTo(Order::class);
  }
}
