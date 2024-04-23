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
        'main_costs',
        'down_payments',
        'kaos_diserahkan',
        'kaos_guru',
        'kaos_dewasa',
        'adjusted_seat',
        'other_cost',
        'notes',
        'total_transactions',
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
        'down_payments' => 'array',
        'kaos_guru' => 'array',
        'kaos_dewasa' => 'array',
        'other_cost' => 'integer',
        'total_transactions' => 'integer',
    ];

    public function profitLoss(): HasOne
    {
        return $this->hasOne(ProfitLoss::class);
    }

    public function tourReport(): HasOne
    {
        return $this->hasOne(TourReport::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
