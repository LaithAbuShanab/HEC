<?php

namespace App\Filament\Admin\Resources\QuoteRequests\Pages;

use App\Domain\Quotes\Enums\QuoteStatus;
use App\Filament\Admin\Resources\QuoteRequests\Actions\QuoteActions;
use App\Filament\Admin\Resources\QuoteRequests\QuoteRequestResource;
use App\Models\QuoteRequest;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;

class ViewQuoteRequest extends ViewRecord
{
    protected static string $resource = QuoteRequestResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->startReviewIfNew();
    }

    private function startReviewIfNew(): void
    {
        $record = $this->getRecord();

        if ($record instanceof QuoteRequest && $record->status === QuoteStatus::New && auth()->user()?->can('update', $record)) {
            $record->update(['status' => QuoteStatus::Reviewing]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            QuoteActions::call(),
            QuoteActions::whatsapp(),
            QuoteActions::changeStatus(),
            QuoteActions::notes(),
            ActionGroup::make([
                QuoteActions::assignToMe(),
                QuoteActions::assign(),
                QuoteActions::markSpam(),
                DeleteAction::make(),
                RestoreAction::make(),
            ]),
        ];
    }
}
