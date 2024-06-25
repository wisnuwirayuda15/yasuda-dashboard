<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Rawilk\FilamentPasswordInput\Password;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\Shield\RoleResource;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;
use Filament\Forms\Components\Group;

class UserResource extends Resource
{
  protected static ?string $model = User::class;

  protected static ?string $navigationIcon = 'fas-user-circle';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
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
              ->unique(ignoreRecord: true)
              ->columnSpan(1)
              ->maxLength(255),
            TextInput::make('email')
              ->unique(ignoreRecord: true)
              ->email()
              ->prefixIcon('fas-envelope')
              ->required()
              ->columnSpan(1)
              ->maxLength(255),
            Select::make('roles')
              ->relationship('roles', 'name')
              ->multiple()
              ->preload()
              ->searchable()
              ->columnSpanFull()
              ->getOptionLabelFromRecordUsing(fn($record): string => Str::headline($record->name))
              ->hintAction(Action::make('add_role')
                ->label('Tambah Role')
                ->icon('bxs-shield-plus')
                ->visible(fn(string $operation): bool => $operation === 'create' || $operation === 'edit')
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
              ->schema([
                Password::make('password')
                  ->required()
                  ->confirmed()
                  ->minLength(8)
                  ->maxLength(255)
                  ->helperText('Password minimal harus berisi 8 karakter')
                  ->copyable(color: 'warning')
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
        ImageColumn::make('avatar_url')
          ->label('Photo')
          ->circular(),
        TextColumn::make('name')
          ->searchable(),
        TextColumn::make('roles.name')
          ->badge()
          ->label('Role')
          ->placeholder('Not assigned')
          ->formatStateUsing(fn(?string $state): string => Str::headline($state))
          ->searchable(),
        TextColumn::make('email')
          ->searchable(),
        TextColumn::make('email_verified_at')
          ->dateTime()
          ->sortable(),
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
        //
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make(),
          Tables\Actions\EditAction::make(),
          Tables\Actions\DeleteAction::make()
            ->hidden(fn(User $record): bool => $record->id === auth()->user()->id)
            ->before(function (User $record, Tables\Actions\DeleteAction $action) {
              if ($record->id === auth()->user()->id) {
                Notification::make()
                  ->danger()
                  ->title('Nu uhh...')
                  ->body("Are you trying to delete your self!?")
                  ->send();
                $action->cancel();
              }
            }),
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
}
