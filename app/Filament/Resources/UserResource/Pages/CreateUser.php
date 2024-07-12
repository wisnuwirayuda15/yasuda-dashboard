<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use App\Models\Employee;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\UserResource;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\EmployeeResource;

class CreateUser extends CreateRecord
{
  protected static string $resource = UserResource::class;

  protected function handleRecordCreation(array $data): Model
  {
    $user = static::getModel()::create($data);

    // $user->sendEmailVerificationNotification();

    Notification::make()
      ->warning()
      ->title('Email Verification')
      // ->body("Email verification link has been sent to <strong>{$user->email}</strong>")
      ->body("Akun <strong>{$user->email}</strong> hanya dapat digunakan jika email sudah terverifikasi, silahkan kirim email verifikasi untuk email terkait.")
      ->persistent()
      ->send()
      ->sendToDatabase(auth()->user());

    if (isset($data['employee_id'])) {
      $employee = Employee::find($data['employee_id']);

      $employee->employable()->save($user);
    }

    return $user;
  }

  protected function getRedirectUrl(): string
  {
    return $this->getRecord()->employable?->exists()
      ? EmployeeResource::getUrl('index')
      : $this->getResource()::getUrl('index');
  }
}
