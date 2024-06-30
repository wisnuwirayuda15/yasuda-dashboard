<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Enums\Gender;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\TourLeader;
use Filament\Tables\Table;
use App\Enums\EmployeeStatus;
use Filament\Resources\Resource;
use App\Enums\NavigationGroupLabel;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ToggleButtons;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TourLeaderResource\Pages;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use App\Filament\Resources\TourLeaderResource\RelationManagers;

class TourLeaderResource extends Resource
{
  protected static ?string $model = TourLeader::class;

  protected static ?string $navigationIcon = 'gmdi-tour-r';

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::HR->getLabel();
  }

  public static function getNavigationBadge(): ?string
  {
    return static::getModel()::count();
  }

  public static function form(Form $form): Form
  {
    return $form
      ->columns(3)
      ->schema([
        Section::make('General Information')
          ->columnSpan(2)
          ->schema([
            TextInput::make('code')
              ->live(true)
              ->code(emp_code(new TourLeader, '02/TLF/'), generateable: false),
            FileUpload::make('photo')
              ->image()
              ->imageEditor()
              ->maxSize(2048)
              ->directory('employee')
              ->imageCropAspectRatio('1:1')
              ->imageResizeMode('cover')
              ->columnSpanFull(),
            TextInput::make('name')
              ->required(),
            TextInput::make('alias')
              ->required()
              ->unique(ignoreRecord: true),
            DatePicker::make('join_date')
              ->required()
              ->default(today())
              ->maxDate(today()),
            DatePicker::make('exit_date')
              ->required()
              ->live()
              ->visible(fn(Get $get): bool => $get('status') === EmployeeStatus::RESIGN->value || $get('status') === EmployeeStatus::RETIRE->value),
            TextInput::make('ktp')
              ->numeric()
              ->unique(ignoreRecord: true)
              ->maxLength(255),
            PhoneInput::make('phone')
              ->unique(ignoreRecord: true)
              ->idDefaultFormat(),
          ]),
        Grid::make()
          ->columnSpan(1)
          ->schema([
            Section::make('Other Information')
              ->schema([
                ToggleButtons::make('status')
                  ->required()
                  ->inline()
                  ->options(EmployeeStatus::class),
                ToggleButtons::make('gender')
                  ->required()
                  ->inline()
                  ->options(Gender::class),
              ])
          ]),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\ImageColumn::make('photo')
          ->circular(),
        Tables\Columns\IconColumn::make('is_user_assigned')
          ->label('Is User Assigned')
          ->state(fn(TourLeader $record) => $record->employable()->exists())
          ->tooltip(fn(TourLeader $record) => $record->employable?->name ?? 'Assign User')
          ->boolean()
          ->alignCenter()
          ->action(EmployeeResource::getAssignUserAction()),
        Tables\Columns\TextColumn::make('code')
          ->badge()
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('name')
          ->sortable()
          ->description(fn(TourLeader $record): string => $record->alias ?? null)
          ->searchable(),
        Tables\Columns\TextColumn::make('join_date')
          ->label('Tanggal Masuk')
          ->date()
          ->sortable(),
        Tables\Columns\TextColumn::make('gender')
          ->badge(),
        Tables\Columns\TextColumn::make('status')
          ->badge(),
        Tables\Columns\TextColumn::make('working_day')
          ->label('Masa Kerja (Hari)')
          ->numeric()
          ->state(function (TourLeader $record): string {
            return today()->diffInDays($record->join_date);
          })
          ->sortable(query: function (Builder $query, string $direction): Builder {
            return $query->orderBy('join_date', $direction);
          }),
        Tables\Columns\TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make(),
          Tables\Actions\EditAction::make(),
          Tables\Actions\DeleteAction::make(),
          EmployeeResource::getAssignUserAction()
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
      'index' => Pages\ListTourLeaders::route('/'),
      'create' => Pages\CreateTourLeader::route('/create'),
      'view' => Pages\ViewTourLeader::route('/{record}'),
      'edit' => Pages\EditTourLeader::route('/{record}/edit'),
    ];
  }
}
