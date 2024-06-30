<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\HasName;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;

class User extends Authenticatable implements HasAvatar, HasName, FilamentUser
{
  use HasRoles, HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

  public function getFilamentAvatarUrl(): ?string
  {
    // return $this->avatar_url ? Storage::url($this->avatar_url) : null;

    if ($this->employable?->photo) {
      $photo = $this->employable?->photo;
      return str_starts_with($photo, 'http') ? $photo : Storage::url($photo);
    } 

    return null;
  }

  public function canAccessPanel(Panel $panel): bool
  {
    return true;
  }

  public function getFilamentName(): string
  {
    return $this->employable?->name ?? $this->name;
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
