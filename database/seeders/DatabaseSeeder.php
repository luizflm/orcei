<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Actions\Accounts\AdjustAccountBalance;
use App\Enums\{TransactionMethod, TransactionType};
use App\Models\{Account, Category, RecurringExpense, Transaction, User};
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::factory()->admin()->create([
            'name'     => 'Admin',
            'email'    => 'admin@admin.com',
            'password' => 'admin',
        ]);

        $accounts = collect([
            Account::factory()->for($user)->create([
                'name'    => 'Nubank',
                'balance' => 0,
            ]),
            Account::factory()->for($user)->create([
                'name'    => 'Picpay',
                'balance' => 150,
            ]),
        ]);

        $expenseCategories = collect([
            Category::factory()->for($user)->create(['name' => 'Comida', 'color' => '#FF6B6B']),
            Category::factory()->for($user)->create(['name' => 'Transporte', 'color' => '#4ECDC4']),
            Category::factory()->for($user)->create(['name' => 'Saúde', 'color' => '#45B7D1']),
            Category::factory()->for($user)->create(['name' => 'Entretenimento', 'color' => '#96CEB4']),
            Category::factory()->for($user)->create(['name' => 'Utilidades', 'color' => '#98D8C8']),
        ]);

        $salaryCategory = Category::factory()->for($user)->create(['name' => 'Salário', 'color' => '#FFEAA7']);

        $incomeMethods = collect([
            TransactionMethod::PIX->value,
            TransactionMethod::CASH->value,
        ]);

        $expenseMethods = collect([
            TransactionMethod::PIX->value,
            TransactionMethod::CASH->value,
            TransactionMethod::CREDIT->value,
            TransactionMethod::DEBIT->value,
        ]);

        $firstTransaction = Transaction::factory()->for($user)->create([
            'account_id'  => $accounts->first(),
            'category_id' => $salaryCategory->id,
            'method'      => $incomeMethods->random(),
            'type'        => TransactionType::INCOME->value,
            'amount'      => '3000.00',
            'description' => 'Exemplo de transação de entrada',
            'date'        => now()->format('Y-m-d'),
        ]);

        $secondTransaction = Transaction::factory()->for($user)->create([
            'account_id'  => $accounts->first(),
            'category_id' => $expenseCategories->random(),
            'method'      => $expenseMethods->random(),
            'type'        => TransactionType::EXPENSE->value,
            'amount'      => '150.00',
            'description' => 'Exemplo de transação de saída',
            'date'        => now()->format('Y-m-d'),
        ]);

        RecurringExpense::factory()->for($user)->create([
            'account_id'   => $accounts->first(),
            'category_id'  => $expenseCategories->random(),
            'method'       => TransactionMethod::CREDIT->value,
            'amount'       => '69.99',
            'description'  => 'Exemplo: Conta de Internet',
            'day_of_month' => 1,
        ]);

        $adjustAccountBalance = app(AdjustAccountBalance::class);
        $adjustAccountBalance($firstTransaction->account, $firstTransaction->amount, $firstTransaction->type);
        $adjustAccountBalance($secondTransaction->account, $secondTransaction->amount, $secondTransaction->type);
    }
}
