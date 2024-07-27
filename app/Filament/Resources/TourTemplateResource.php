<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Regency;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Destination;
use App\Models\TourTemplate;
use Filament\Resources\Resource;
use App\Enums\NavigationGroupLabel;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use EightyNine\Approvals\Tables\Actions\RejectAction;
use EightyNine\Approvals\Tables\Actions\SubmitAction;
use App\Filament\Resources\TourTemplateResource\Pages;
use EightyNine\Approvals\Tables\Actions\ApproveAction;
use EightyNine\Approvals\Tables\Actions\DiscardAction;
use EightyNine\Approvals\Tables\Actions\ApprovalActions;
use EightyNine\Approvals\Tables\Columns\ApprovalStatusColumn;
use App\Filament\Resources\TourTemplateResource\RelationManagers;

class TourTemplateResource extends Resource
{
  protected static ?string $model = TourTemplate::class;

  protected static ?string $navigationIcon = 'fontisto-holiday-village';

  public static function getLabel(): string
  {
    return __('navigation.label.' . static::getSlug());
  }

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::MASTER_DATA->getLabel();
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        TextInput::make('name')
          ->required()
          ->live(true)
          ->helperText(fn(string $operation) => $operation !== 'view' ? 'Anda dapat mengenerate nama berdasarkan regensi dan destinasi yang dipilih.' : null)
          ->maxLength(255)
          ->hintAction(
            Action::make('generate_name')
              ->label('Generate')
              ->hidden(fn(string $operation) => $operation === 'view')
              ->icon('gmdi-text-fields-o')
              ->disabled(fn(Get $get) => blank($get('regency_id')) || blank($get('destinations')))
              ->action(function (Get $get, Set $set, TextInput $component) {
                $regency = Regency::find($get('regency_id'));
                $destinations = Destination::find($get('destinations'));
                if (blank($regency) || blank($destinations)) {
                  $set($component, null);
                } else {
                  $set($component, "{$regency->name} ({$destinations->implode('name', ' + ')})");
                }
              })
          ),
        Group::make()
          ->columns(2)
          ->visible(fn(Get $get) => filled($get('destinations')))
          ->schema([
            Placeholder::make('weekday_price')
              ->label('Total Harga Weekday')
              ->content(function (Get $get, Set $set, Placeholder $component) {
                $price = Destination::find($get('destinations'))->sum('weekday_price');
                $set($component, $price);
                return view('filament.components.badges.default', ['text' => idr($price), 'color' => 'success', 'big' => true]);
              }),
            Placeholder::make('weekend_price')
              ->label('Total Harga Weekday')
              ->content(function (Get $get, Set $set, Placeholder $component) {
                $price = Destination::find($get('destinations'))->sum('weekend_price');
                $set($component, $price);
                return view('filament.components.badges.default', ['text' => idr($price), 'color' => 'warning', 'big' => true]);
              }),
          ]),
        Select::make('destinations')
          ->required()
          ->live(true)
          ->multiple()
          ->allowHtml()
          ->options(Destination::getOptionsWithPrice()),
        Select::make('regency_id')
          ->required()
          ->live(true)
          ->relationship('regency', 'name'),
        FileUpload::make('image')
          ->image()
          ->imageEditor()
          ->maxSize(2048)
          ->directory('tour-template')
          ->imageCropAspectRatio('1:1')
          ->imageResizeMode('cover')
          ->columnSpanFull(),
        RichEditor::make('description')->columnSpanFull(),
        Checkbox::make('submission')->submission()
      ])->columns(1);
  }

  public static function table(Table $table): Table
  {
    $des = Destination::all();

    return $table
      ->columns([
        ImageColumn::make('image'),
        TextColumn::make('name')
          ->limit(40)
          ->searchable(),
        TextColumn::make('regency.name')
          ->searchable()
          ->sortable(),
        TextColumn::make('destinations')
          ->badge()
          ->searchable()
          ->formatStateUsing(fn(string $state): string => $des->find($state)?->name),
        TextColumn::make('weekday_prices')
          ->money('IDR')
          ->state(fn(TourTemplate $record): float => $des->find($record->destinations)?->sum('weekday_price')),
        TextColumn::make('weekend_prices')
          ->money('IDR')
          ->state(fn(TourTemplate $record): float => $des->find($record->destinations)?->sum('weekend_price')),
        TextColumn::make('high_season_prices')
          ->money('IDR')
          ->state(fn(TourTemplate $record): float => $des->find($record->destinations)?->sum('high_season_price'))
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        ApprovalStatusColumn::make('approvalStatus.status'),
      ])
      ->filters([
        Filter::make('approved')->approved(),
        Filter::make('notApproved')->notApproved(),
      ])
      ->actions(
        [
          SubmitAction::make()->color('info'),
          ApproveAction::make()->color('success'),
          DiscardAction::make()->color('warning'),
          RejectAction::make()->color('danger'),
          ActionGroup::make([
            ViewAction::make(),
            EditAction::make(),
            DeleteAction::make(),
          ])->visible(fn(Model $record) => $record->isApprovalCompleted()),
        ]
      )
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ]);
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ManageTourTemplates::route('/'),
    ];
  }
}
