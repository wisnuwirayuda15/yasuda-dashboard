<?php

namespace App\Filament\Resources;

use App\Models\Bus;
use Filament\Forms;
use Filament\Tables;
use App\Enums\BusType;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use App\Tables\Columns\BusSeatsColumn;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use App\Filament\Resources\BusResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BusResource\RelationManagers;
use App\Filament\Resources\BusResource\RelationManagers\BusAvailabilityRelationManager;

class BusResource extends Resource
{
  protected static ?string $model = Bus::class;

  protected static ?string $navigationGroup = 'Master Data';

  protected static ?string $recordTitleAttribute = 'name';

  protected static ?string $modelLabel = 'Bus';

  protected static ?string $navigationLabel = 'Bus';

  protected static ?string $navigationIcon = 'bx-bus';

  protected static ?string $activeNavigationIcon = 'bxs-bus';

  protected static ?int $navigationSort = 1;

  public static function getNavigationBadge(): ?string
  {
    return static::getModel()::count();
  }


  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\FileUpload::make('image')
          ->required()
          ->image()
          ->imageEditor()
          ->maxSize(2048)
          ->directory('bus')
          ->imageCropAspectRatio('16:9')
          ->imageResizeMode('cover')
          ->columnSpanFull(),
        Forms\Components\TextInput::make('name')
          ->label('Armada')
          ->required()
          ->maxLength(255)
          ->columnSpanFull(),
        Forms\Components\RichEditor::make('description')
          ->required()
          ->columnSpanFull(),
        Forms\Components\Section::make()
          ->columns(['xl' => 2])
          ->schema([
            Forms\Components\TextInput::make('seat_total')
              ->label('Jumlah Kursi')
              ->required()
              ->numeric()
              ->columnSpanFull(),
            Forms\Components\TextInput::make('left_seat')
              ->label('Kiri')
              ->required()
              ->numeric(),
            Forms\Components\TextInput::make('right_seat')
              ->label('Kanan')
              ->required()
              ->numeric(),
          ]),
        Forms\Components\Select::make('type')
          ->label('Tipe')
          ->required()
          ->options(BusType::class)
          ->native(false)
          ->columnSpanFull(),
        Forms\Components\TextInput::make('price')
          ->label('Harga')
          ->required()
          ->numeric()
          ->prefix('Rp')
          ->columnSpanFull(),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\ImageColumn::make('image')
          ->simpleLightbox(),
        Tables\Columns\TextColumn::make('name')
          ->label('Armada')
          ->searchable(),
        BusSeatsColumn::make('seat_total')
          ->label('Kursi'),
        Tables\Columns\TextColumn::make('type')
          ->label('Tipe')
          ->searchable()
          ->badge(),
        Tables\Columns\TextColumn::make('price')
          ->label('Harga')
          ->money('idr')
          // ->summarize(Sum::make()->money('IDR'))
          ->sortable(),
        Tables\Columns\TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        //
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make()->color('info'),
          Tables\Actions\EditAction::make()
            ->after(function (Bus $record) {
              if ($record->isDirty('image')) {
                Storage::disk('public')->delete($record->image);
              }
            }),
          Tables\Actions\DeleteAction::make()
            ->after(function (Bus $record) {
              if ($record->image) {
                Storage::disk('public')->delete($record->image);
              }
            })
        ])
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ]);
  }

  public static function getRelations(): array
  {
    return [
      BusAvailabilityRelationManager::class
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListBuses::route('/'),
      'create' => Pages\CreateBus::route('/create'),
      'view' => Pages\ViewBus::route('/{record}'),
      'edit' => Pages\EditBus::route('/{record}/edit'),
    ];
  }
}
