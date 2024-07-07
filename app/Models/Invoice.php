<?php

namespace App\Models;

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
