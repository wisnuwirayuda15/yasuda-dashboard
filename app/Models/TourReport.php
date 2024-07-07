<?php

namespace App\Models;

use App\Models\Scopes\ApprovedScope;
use Illuminate\Database\Eloquent\Model;
use EightyNine\Approvals\Models\ApprovableModel;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([ApprovedScope::class])]

class TourReport extends ApprovableModel
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
