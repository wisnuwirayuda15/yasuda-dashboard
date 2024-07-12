<?php

namespace App\Http\Controllers;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Filament\Notifications\Notification;

class EmailController extends Controller
{
  public function sendVerification(User $user): \Illuminate\Http\RedirectResponse
  {
    if ($user->hasVerifiedEmail()) {
      Notification::make()
        ->danger()
        ->title('Failed')
        ->body("<strong>{$user->email}</strong> already verified")
        ->send();
    } else {
      $user->sendEmailVerificationNotification();

      Notification::make()
        ->success()
        ->title('Email Verification')
        ->body("Email verification link has been sent to <strong>{$user->email}</strong>")
        ->send();
    }

    return redirect(UserResource::getUrl('index'));
  }
}
