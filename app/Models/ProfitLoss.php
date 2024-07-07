<?php

namespace App\Models;

use App\Models\Scopes\ApprovedScope;
use Illuminate\Database\Eloquent\Model;
use EightyNine\Approvals\Models\ApprovableModel;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ScopedBy([ApprovedScope::class])]

class ProfitLoss extends ApprovableModel
{
    use HasFactory;

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
