<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Employee;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use App\Enums\NavigationGroupLabel;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Group;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Illuminate\Auth\Events\Registered;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Rawilk\FilamentPasswordInput\Password;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\Shield\RoleResource;
use Filament\Tables\Actions\Action as TableAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
  protected static ?string $model = User::class;

  protected static ?string $navigationIcon = 'fas-user-circle';

  protected static ?Employee $employee = null;

  protected static ?int $navigationSort = 1;

  public static function getLabel(): string
  {
    return __('navigation.label.' . static::getSlug());
  }

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::SETTING->getLabel();
  }

  public static function form(Form $form): Form
  {
    $operation = $form->getOperation();

    if (request('employee') && $operation === 'create') {
      static::$employee = Employee::find(request('employee'));
    }

    $username = str_replace(' ', '.', strtolower(static::$employee?->name));

    return $form
      ->schema([
        Hidden::make('employee_id')
          ->default(fn() => static::$employee?->id),
        Section::make('Personal Information')
          ->columns(2)
          ->schema([
            // FileUpload::make('avatar_url')
            //   ->image()
            //   ->imageEditor()
            //   ->maxSize(2048)
            //   ->directory('user_avatar')
            //   ->imageCropAspectRatio('1:1')
            //   ->imageResizeMode('cover')
            //   ->columnSpanFull(),
            TextInput::make('name')
              ->required()
              ->label('Username')
              ->default(fn() => $username)
              ->unique(ignoreRecord: true)
              ->columnSpan(1)
              ->maxLength(255),
            TextInput::make('email')
              ->unique(ignoreRecord: true)
              ->email()
              ->live(true, condition: $operation === 'edit')
              ->helperText(fn(?User $record, $state) => $record?->email !== $state && $operation === 'edit' ? 'Jika diganti, email ini perlu diverifikasi ulang' : null)
              // ->default(fn() => static::$employee ? $username . '@gmail.com' : null)
              ->placeholder(fn() => (static::$employee ? $username : 'user') . '@gmail.com')
              ->prefixIcon('fas-envelope')
              ->required()
              ->columnSpan(1)
              ->maxLength(255),
            Select::make('roles')
              ->required()
              ->relationship('roles', 'name')
              ->multiple()
              ->preload()
              ->searchable()
              ->columnSpanFull()
              ->getOptionLabelFromRecordUsing(fn($record): string => Str::headline($record->name))
              ->hintAction(Action::make('create_role')
                ->label('Tambah Role')
                ->icon('bxs-shield-plus')
                ->visible(fn(): bool => $operation === 'create' || $operation === 'edit')
                ->url(RoleResource::getUrl('create'))),
          ]),
        Section::make('Password')
          ->visibleOn(['create', 'edit'])
          ->schema([
            Toggle::make('change_password')
              ->live()
              ->columnSpanFull()
              ->visibleOn('edit')
              ->onIcon('heroicon-s-key')
              ->offIcon('gmdi-lock')
              ->afterStateUpdated(fn(Set $set) => $set('password', null)),
            Group::make()
              ->live(true)
              ->columnSpanFull()
              ->visible(fn(Get $get, string $operation): bool => $get('change_password') || $operation === 'create')
              ->columns(2)
              ->schema([
                Password::make('password')
                  ->required()
                  ->confirmed()
                  ->minLength(8)
                  ->maxLength(255)
                  ->helperText('Password minimal harus berisi 8 karakter')
                  ->regeneratePassword(color: 'secondary', using: function (Set $set) {
                    $generated = Str::password(16);
                    $set('password_confirmation', $generated);
                    return $generated;
                  }),
                Password::make('password_confirmation')
                  ->required()
                  ->label('Confirm Password')
                  ->minLength(8)
                  ->maxLength(255),
              ])
          ])
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('name')
          ->label('Username')
          ->sortable()
          ->searchable(),
        TextColumn::make('email')
          ->icon('gmdi-mail')
          ->sortable()
          ->description(fn(User $record) => $record->email_verified_at ? 'Verified at ' . $record->email_verified_at : 'Not verified')
          ->searchable(),
        TextColumn::make('roles.name')
          ->badge()
          ->placeholder('Not assigned')
          ->formatStateUsing(fn(?string $state): string => Str::headline($state))
          ->searchable(),
        IconColumn::make('has_employee')
          ->label('Has Employee')
          ->state(fn(User $record) => (bool) $record->employable)
          ->tooltip(fn(User $record) => $record->employable?->name)
          ->boolean()
          ->alignCenter(),
        // TextColumn::make('email_verified_at')
        //   ->icon('heroicon-s-check-circle')
        //   ->iconColor('success')
        //   ->label('Verified At')
        //   ->placeholder('Not verified')
        //   ->dateTime()
        //   ->sortable(),
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        Filter::make('verified')
          ->label('Email Verified')
          ->query(fn(Builder $query): Builder => $query->whereNotNull('email_verified_at')),
        Filter::make('has_employee')
          ->label('Has Employee')
          ->query(fn(Builder $query): Builder => $query->whereHas('employable')),
      ])
      ->actions([
        ActionGroup::make([
          ViewAction::make(),
          EditAction::make()
            ->hidden(fn(User $record): bool => $record->id == auth()->id()),
          static::getDeleteAction(),
          static::getRemoveEmployeeAction(),
          static::getSendEmailVerificationAction(),
        ])
      ]);
  }

  public static function getRelations(): array
  {
    return [
      //
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListUsers::route('/'),
      'create' => Pages\CreateUser::route('/create'),
      'view' => Pages\ViewUser::route('/{record}'),
      'edit' => Pages\EditUser::route('/{record}/edit'),
    ];
  }

  public static function getDeleteAction(): TableAction
  {
    return Tables\Actions\DeleteAction::make()
      ->hidden(fn(User $record): bool => $record->id === auth()->id() || $record->employable?->exists())
      ->before(function (User $record, Tables\Actions\DeleteAction $action) {
        if ($record->id === auth()->id()) {
          Notification::make()
            ->danger()
            ->title('Nu uhh...')
            ->body("Are you trying to delete your self!?")
            ->send();
          $action->cancel();
        }

        if ($record->employable?->exists()) {
          Notification::make()
            ->danger()
            ->title('Failed')
            ->body('User has an employee')
            ->send();
          $action->cancel();
        }
      })
      ->after(function () {
        Notification::make()
          ->success()
          ->title('Success')
          ->body('User permanently deleted')
          ->send();
      })
      ->requiresPasswordConfirmation();
  }

  public static function getRemoveEmployeeAction(): TableAction
  {
    return TableAction::make('remove_employee')
      ->requiresConfirmation()
      ->visible(fn(User $record): bool => (bool) $record->employable)
      ->icon('fas-user-xmark')
      ->label('Remove Employee')
      ->color('warning')
      ->action(function (User $record): void {
        $record->employable()->dissociate();

        $record->update([
          'employable_id' => null,
          'employable_type' => null
        ]);

        Notification::make()
          ->success()
          ->title('Success')
          ->body("Employee removed from user <strong>{$record->name}</strong>")
          ->send();
      });
  }

  public static function getSendEmailVerificationAction(): TableAction
  {
    return TableAction::make('send_email_verification')
      ->icon('gmdi-mail')
      ->label('Send Email Verification')
      ->color('success')
      ->requiresConfirmation()
      ->hidden(fn(User $record): bool => $record->hasVerifiedEmail())
      ->action(function (User $record, TableAction $action): void {
        if ($record->hasVerifiedEmail()) {
          Notification::make()
            ->danger()
            ->title('Failed')
            ->body("<strong>{$record->email}</strong> already verified")
            ->send();
          $action->cancel();
        }

        $record->sendEmailVerificationNotification();

        Notification::make()
          ->success()
          ->title('Email Verification')
          ->body("Email verification link has been sent to <strong>{$record->email}</strong>")
          ->send();
      });
  }
}
