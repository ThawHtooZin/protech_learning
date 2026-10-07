<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;

/**
 * Seeds one bank account from config/lms.php payments.bank when the table is empty.
 *
 *   php artisan db:seed --class=BankAccountSeeder
 */
class BankAccountSeeder extends Seeder
{
    public function run(): void
    {
        if (BankAccount::query()->exists()) {
            $this->command?->warn('Bank accounts already exist — skipping BankAccountSeeder.');

            return;
        }

        $bank = config('lms.payments.bank', []);
        $name = trim((string) ($bank['name'] ?? ''));
        $accountName = trim((string) ($bank['account_name'] ?? ''));
        $accountNumber = trim((string) ($bank['account_number'] ?? ''));

        if ($name === '' || $accountName === '' || $accountNumber === '') {
            $this->command?->warn('LMS_BANK_* not set — add accounts under Admin → Bank accounts.');

            return;
        }

        BankAccount::query()->create([
            'name' => $name,
            'account_name' => $accountName,
            'account_number' => $accountNumber,
            'note' => $bank['note'] ?: null,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $this->command?->info('Seeded bank account from config.');
    }
}
