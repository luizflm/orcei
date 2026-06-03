<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Transactions\Pages;

use App\Actions\Transactions\{DeleteTransaction as DeleteTransactionAction, UpdateTransaction as UpdateTransactionAction};
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->using(function (Transaction $record): bool {
                    app(DeleteTransactionAction::class)($record);

                    return true;
                }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Transaction $record */
        $action = app(UpdateTransactionAction::class);

        return $action($record, $data);
    }
}
