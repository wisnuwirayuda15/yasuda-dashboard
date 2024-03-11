<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourLeaderSalary extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tour_leader_id',
        'amount',
        'date',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'tour_leader_id' => 'integer',
        'amount' => 'integer',
        'date' => 'datetime',
    ];

    public function tourLeader(): BelongsTo
    {
        return $this->belongsTo(TourLeader::class);
    }
}
