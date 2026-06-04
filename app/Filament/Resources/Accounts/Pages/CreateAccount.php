<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Accounts\Pages;

use App\Actions\Accounts\CreateAccount as CreateAccountAction;
use App\Filament\Resources\Accounts\AccountResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAccount extends CreateRecord
{
    protected static string $resource = AccountResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $action = app(CreateAccountAction::class);

        return $action($data, auth()->id());
    }
}
