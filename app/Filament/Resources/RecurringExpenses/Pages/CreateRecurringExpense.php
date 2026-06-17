<?php

declare(strict_types = 1);

namespace App\Filament\Resources\RecurringExpenses\Pages;

use App\Enums\TransactionType;
use App\Filament\Resources\RecurringExpenses\RecurringExpenseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRecurringExpense extends CreateRecord
{
    protected static string $resource = RecurringExpenseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['type']    = TransactionType::EXPENSE->value;

        return $data;
    }
}
