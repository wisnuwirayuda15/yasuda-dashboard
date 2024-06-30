<?php

namespace App\Models;

use App\Enums\EmployeeRole;
use App\Enums\EmployeeStatus;
use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'code',
    'name',
    'alias',
    'join_date',
    'exit_date',
    'ktp',
    'photo',
    'phone',
    'gender',
    'role',
    'status',
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'id' => 'integer',
    'join_date' => 'datetime',
    'exit_date' => 'datetime',
    'gender' => Gender::class,
    'role' => EmployeeRole::class,
    'status' => EmployeeStatus::class,
  ];

  public function employable()
  {
    return $this->morphOne(User::class, 'employable');
  }
}
