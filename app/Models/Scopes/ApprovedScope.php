<?php

namespace App\Models\Scopes;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class ApprovedScope implements Scope
{
  /**
   * Apply the scope to a given Eloquent query builder.
   */
  public function apply(Builder $builder, Model $model): void
  {
    if (!Str::contains(Route::currentRouteName(), ['view', 'edit'])) {
      static::getQuery($builder);
    }
  }

  public static function getQuery(Builder $builder): Builder
  {
    return $builder->whereHas('approvalStatus', function (Builder $query) {
      $query->where('status', 'Approved');
    });
  }
}
