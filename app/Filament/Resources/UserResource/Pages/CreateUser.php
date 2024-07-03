<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use App\Models\Employee;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
  protected static string $resource = UserResource::class;

  protected function handleRecordCreation(array $data): Model
  {
    $user = static::getModel()::create($data);

    // event(new Registered($user)); //send email verification

    // Notification::make()
    //   ->successg()
    //   ->title('Email Verification')
    //   ->body('Email verification link has been sent to ' . $user->email . '.')
    //   ->send();

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
