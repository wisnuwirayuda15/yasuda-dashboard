<?php

namespace App\Models;

use App\Enums\CashFlow;
use App\Enums\FleetCategory;
use App\Models\Scopes\ApprovedScope;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use EightyNine\Approvals\Models\ApprovableModel;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([ApprovedScope::class])]

class ProfitLoss extends ApprovableModel
{
  use HasFactory;

  public static function getAllProfitLossCollectionForCurrentMonth(): Collection
  {
    return self::whereBetween('created_at', [
      now()->startOfMonth(),
      now()->endOfMonth()
    ])
      ->with('invoice.tourReport')
      ->get();
  }

  public function calculateIncome(): ?float
  {
    if ($this->invoice && $this->invoice->tourReport) {
      return $this->adjusted_income + $this->invoice->tourReport->difference;
    }
    return null;
  }

  public function calculateNetSales(): float
  {
    $inv = $this->invoice;
    $mainCosts = $inv->main_costs;
    $totalPrices = array_sum(array_map(fn($cost) => $cost['qty'] * $cost['price'], $mainCosts)) ?: 0;
    $totalCashbacks = array_sum(array_map(fn($cost) => $cost['qty'] * $cost['cashback'], $mainCosts)) ?: 0;
    $totalNetSales = $totalPrices - $totalCashbacks;
    return $totalNetSales;
  }

  public static function getTotalIncomeForCurrentMonth(): float
  {
    $profitLosses = self::getAllProfitLossCollectionForCurrentMonth();

    $validIncomes = $profitLosses->sum(function (self $profitLoss) {
      return $profitLoss->calculateIncome();
    });

    return $validIncomes;

    // $totalIncome = $validIncomes->sum();

    // $count = $validIncomes->count();

    // return $count > 0 ? $totalIncome / $count : 0;
  }

  public static function getIncomeArrayForCurrentMonth(): array
  {
    $profitLosses = self::getAllProfitLossCollectionForCurrentMonth();

    return $profitLosses->map(function (self $profitLoss) {
      return $profitLoss->calculateIncome();
    })->filter()->values()->toArray();
  }

  public static function getTotalNetSalesForCurrentMonth(): float
  {
    $profitLosses = self::getAllProfitLossCollectionForCurrentMonth();

    $totalNetSales = $profitLosses->sum(function (self $profitLoss) {
      return $profitLoss->calculateNetSales();
    });

    return $totalNetSales;

    // $count = $profitLosses->count();

    // return $count > 0 ? $totalNetSales / $count : 0;
  }

  public static function getNetSalesArrayForCurrentMonth(): array
  {
    $profitLosses = self::getAllProfitLossCollectionForCurrentMonth();

    return $profitLosses->map(function (self $profitLoss) {
      return $profitLoss->calculateNetSales();
    })->toArray();
  }

  public function createOrUpdateLoyaltyPoint(): Model|bool
  {
    $pnl = $this;

    if (!$pnl->isApprovalCompleted()) {
      Notification::make()
        ->danger()
        ->title('Failed')
        ->body("Profit & Loss belum disetujui!")
        ->send();

      return false;
    }

    $inv = $pnl->invoice;

    $order = $inv->order;

    $loyaltyPoint = $pnl->invoice->loyaltyPoint;

    $customer = $inv->order->customer->name;

    $mediumTotal = $order->orderFleets->filter(fn(OrderFleet $orderFleet) => $orderFleet->fleet->category->value === FleetCategory::MEDIUM->value)->count();
    $bigTotal = $order->orderFleets->filter(fn(OrderFleet $orderFleet) => $orderFleet->fleet->category->value === FleetCategory::BIG->value)->count();
    $legrestTotal = $order->orderFleets->filter(fn(OrderFleet $orderFleet) => $orderFleet->fleet->category->value === FleetCategory::LEGREST->value)->count();

    $bonus = $mediumTotal * $pnl->medium_subs_bonus +
      $bigTotal * $pnl->big_subs_bonus +
      $legrestTotal * $pnl->legrest_subs_bonus;

    if ($loyaltyPoint) {
      $model = $loyaltyPoint->update([
        'amount' => $bonus,
      ]);

      Notification::make()
        ->success()
        ->title('Success')
        ->body("Loyalty Point untuk <strong>{$customer}</strong> berhasil diubah!")
        ->send();
    } else {
      $model = $inv->loyaltyPoint()->create([
        'cash_status' => CashFlow::IN->value,
        'description' => '<p>Tambahan saldo bonus langganan</p>',
        'amount' => $bonus,
      ]);

      Notification::make()
        ->success()
        ->title('Success')
        ->body("Loyalty Point untuk <strong>{$customer}</strong> berhasil dibuat!")
        ->send();
    }

    return $model;
  }

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'invoice_id',
    'medium_rent_price',
    'big_rent_price',
    'legrest_rent_price',
    'toll_price',
    'banner_price',
    'crew_price',
    'tour_leader_price',
    'documentation_qty',
    'documentation_price',
    'teacher_shirt_qty',
    'teacher_shirt_price',
    'souvenir_price',
    'child_shirt_price',
    'adult_shirt_price',
    'photo_price',
    'snack_price',
    'eat_price',
    'eat_child_price',
    'eat_prasmanan_price',
    'backup_price',
    'emergency_cost_price',
    'others_income',
    'medium_subs_bonus',
    'big_subs_bonus',
    'legrest_subs_bonus',
    'adjusted_income',
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'id' => 'integer',
    'invoice_id' => 'integer',
    'medium_rent_price' => 'integer',
    'big_rent_price' => 'integer',
    'legrest_rent_price' => 'integer',
    'toll_price' => 'integer',
    'banner_price' => 'integer',
    'crew_price' => 'integer',
    'tour_leader_price' => 'integer',
    'documentation_price' => 'integer',
    'teacher_shirt_qty' => 'integer',
    'teacher_shirt_price' => 'integer',
    'souvenir_price' => 'integer',
    'child_shirt_price' => 'integer',
    'adult_shirt_price' => 'integer',
    'photo_price' => 'integer',
    'snack_price' => 'integer',
    'eat_price' => 'integer',
    'eat_child_price' => 'integer',
    'eat_prasmanan_price' => 'integer',
    'backup_price' => 'integer',
    'emergency_cost_price' => 'integer',
    'others_income' => 'integer',
    'medium_subs_bonus' => 'integer',
    'big_subs_bonus' => 'integer',
    'legrest_subs_bonus' => 'integer',
    'adjusted_income' => 'integer',
  ];

  public function invoice(): BelongsTo
  {
    return $this->belongsTo(Invoice::class);
  }
}
