<?php

declare(strict_types = 1);

namespace App\Filament\Pages;

use App\Models\Account;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('accountIds')
                    ->label(__('widget.filters.accounts'))
                    ->options(fn (): array => Account::query()
                        ->where('user_id', auth()->id())
                        ->pluck('name', 'id')
                        ->all())
                    ->multiple()
                    ->searchable()
                    ->placeholder(__('widget.filters.all_accounts')),
            ]);
    }
}
