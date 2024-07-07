<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BasePage;

class Login extends BasePage
{
  public function mount(): void
  {
    parent::mount();

    if (app()->environment('local')) {
      $this->form->fill([
        'email' => env('ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('ADMIN_PASSWORD', '12345678'),
        'remember' => true,
      ]);
    }
  }
}
