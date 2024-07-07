<?php

namespace EightyNine\Approvals\Filament\Resources\ApprovalFlowResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class StepsRelationManager extends RelationManager
{
  protected static string $relationship = 'steps';

  public function form(Form $form): Form
  {
    return $form
      ->columns(12)
      ->schema([
        Select::make("role_id")
          ->searchable()
          ->label("Role")
          ->helperText("Who should approve in this step?")
          ->options(fn() => ((string) config('process_approval.roles_model'))::get()
            ->map(fn($model) => [
              "name" => str($model->name)
                ->replace("_", " ")
                ->title()
                ->toString(),
              "id" => $model->id
            ])->pluck("name", "id"))
          ->columnSpan(6)
          ->native(false),
        Select::make("action")
          ->helperText("What should be done in this step?")
          ->native(false)
          ->default("APPROVE")
          ->columnSpan(4)
          ->options([
            'APPROVE' => __('filament-approvals::approvals.actions.approve'),
            'VERIFY' => __('filament-approvals::approvals.actions.verify'),
            'CHECK' => __('filament-approvals::approvals.actions.check'),
          ]),
        TextInput::make('order')
          ->label('Order')
          ->type('number')
          ->columnSpan(2)
          ->default(fn($livewire) => $livewire->ownerRecord->steps->count() + 1)
      ]);
  }

  public function table(Table $table): Table
  {
    return $table
      ->recordTitleAttribute('id')
      ->reorderable("order")
      ->defaultSort('order', 'asc')
      ->columns([
        TextColumn::make('order')->label('Order'),
        TextColumn::make('role.name'),
        TextColumn::make('action'),
      ])
      ->headerActions([
        Tables\Actions\CreateAction::make()
          ->icon('heroicon-s-plus')
          ->label(__('filament-approvals::approvals.actions.add_step')),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ]);
  }
}
