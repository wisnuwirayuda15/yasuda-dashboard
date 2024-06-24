<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shirt extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_id',
        'child',
        'adult',
        'male_teacher',
        'female_teacher',
        'child_color',
        'adult_color',
        'male_teacher_color',
        'female_teacher_color',
        'child_sleeve',
        'adult_sleeve',
        'male_teacher_sleeve',
        'female_teacher_sleeve',
        'child_material',
        'adult_material',
        'male_teacher_material',
        'female_teacher_material',
        'status',
        'total',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'invoice_id' => 'integer',
        'child' => 'array',
        'adult' => 'array',
        'male_teacher' => 'array',
        'female_teacher' => 'array',
        'total' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
