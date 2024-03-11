<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revenue extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'payment_id',
        'restaurant',
        'souvenir',
        'shirt',
        'hotel',
        'snack',
        'catering',
        'gross_income',
        'net_income',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'order_id' => 'integer',
        'payment_id' => 'integer',
        'restaurant' => 'integer',
        'souvenir' => 'integer',
        'shirt' => 'integer',
        'hotel' => 'integer',
        'snack' => 'integer',
        'catering' => 'integer',
        'gross_income' => 'integer',
        'net_income' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
