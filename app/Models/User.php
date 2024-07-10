<?php

namespace App\Models;

use Filament\Panel;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\HasName;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;

class User extends Authenticatable implements HasAvatar, HasName, FilamentUser, MustVerifyEmail
{
  use HasRoles, HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

  public function getFilamentAvatarUrl(): ?string
  {
    // return $this->avatar_url ? Storage::url($this->avatar_url) : null;

    $employeePhoto = $this->employable?->photo;

    if ($employeePhoto) {
      return str_starts_with($employeePhoto, 'http')
        ? $employeePhoto
        : Storage::url($employeePhoto);
    }

    return null;
  }

  public function canAccessPanel(Panel $panel): bool
  {
    return true;

    // if ($this->employable?->exists() || $this->email === env('ADMIN_EMAIL', 'yasudajayatour@gmail.com')) {
    //   return true;
    // }
    // throw new HttpException(403, 'Your account is not activated, contact your admin for futher information');
  }

  public function getFilamentName(): string
  {
    return $this->employable?->name ?? $this->name;
  }

  public function unverify()
  {
    $this->email_verified_at = null;

    $this->save();
  }

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
    'avatar_url',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
  ];

  public function employable()
  {
    return $this->morphTo();
  }
}
