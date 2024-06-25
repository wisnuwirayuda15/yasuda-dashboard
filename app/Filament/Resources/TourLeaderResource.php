<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Enums\Gender;
use Filament\Forms\Form;
use App\Models\TourLeader;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Enums\NavigationGroupLabel;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
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

  public static function getNavigationBadge(): ?string
  {
    return static::getModel()::count();
  }

  public static function form(Form $form): Form
  {
    return $form
      ->columns(3)
      ->schema([
        Section::make('Tour Leader')
          ->schema([
            // Select::make('user_id')
            //   ->unique(ignoreRecord: true)
            //   ->relationship('user', 'name'),
            TextInput::make('name')
              ->required()
              ->maxLength(255),
            DatePicker::make('join_date')
              ->required()
              ->default(today())
              ->maxDate(today()),
            FileUpload::make('photo')
              ->image()
              ->imageEditor()
              ->maxSize(2048)
              ->directory('tour-leader')
              ->imageCropAspectRatio('1:1')
              ->imageResizeMode('cover'),
          ])
          ->columnSpan(2),
        Grid::make()
          ->schema([
            Section::make()
              ->schema([
                ToggleButtons::make('gender')
                  ->required()
                  ->inline()
                  ->options(Gender::class),
              ]),
            Section::make('Contact')
              ->schema([
                PhoneInput::make('phone')
                  ->idDefaultFormat(),
              ]),
          ])->columnSpan(1)
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\ImageColumn::make('photo')
          ->circular(),
        Tables\Columns\TextColumn::make('code')
          ->badge()
          ->searchable(),
        Tables\Columns\TextColumn::make('name')
          ->sortable()
          ->searchable(),
        Tables\Columns\TextColumn::make('alias')
          ->sortable()
          ->searchable(),
        Tables\Columns\TextColumn::make('join_date')
          ->label('Tanggal Masuk')
          ->date()
          ->sortable(),
        Tables\Columns\TextColumn::make('gender')
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
