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

  public static function getQuery(Builder $builder, bool $whereNot = false): Builder
  {
    return $builder->whereHas('approvalStatus', function (Builder $query) use ($whereNot) {
      if ($whereNot) {
        $query->whereNot('status', 'Approved');
      } else {
        $query->where('status', 'Approved');
      }
    });
  }
}
