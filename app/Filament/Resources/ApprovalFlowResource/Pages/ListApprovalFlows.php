<?php

namespace EightyNine\Approvals\Filament\Resources\ApprovalFlowResource\Pages;

use EightyNine\Approvals\Filament\Resources\ApprovalFlowResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApprovalFlows extends ListRecords
{
  protected static string $resource = ApprovalFlowResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->icon('heroicon-s-plus')
        ->createAnother(false)
        ->label(__('filament-approvals::approvals.actions.create_flow'))
        ->after(fn($record) => redirect(ApprovalFlowResource::getUrl('edit', ['record' => $record])))
    ];
  }
}
