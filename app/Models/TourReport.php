<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_id',
        'main_costs',
        'other_costs',
        'customer_repayment',
        'difference',
        'income_total',
        'expense_total',
        'defisit_surplus',
        'refundable',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'invoice_id' => 'integer',
        'main_costs' => 'array',
        'other_costs' => 'array',
        'customer_repayment' => 'integer',
        'difference' => 'integer',
        'income_total' => 'integer',
        'expense_total' => 'integer',
        'defisit_surplus' => 'integer',
        'refundable' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
