<?php

namespace App\Models;

use App\Models\Scopes\ApprovedScope;
use Illuminate\Database\Eloquent\Model;
use EightyNine\Approvals\Models\ApprovableModel;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([ApprovedScope::class])]

class TourTemplate extends ApprovableModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'image',
        'name',
        'regency_id',
        'destinations',
        'description',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'regency_id' => 'integer',
        'destinations' => 'array',
    ];

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }
}
