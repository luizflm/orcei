<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Transactions\Pages;

use App\Actions\Transactions\CreateTransaction as CreateTransactionAction;
use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $action = app(CreateTransactionAction::class);

        return $action($data, auth()->id());
    }
}
