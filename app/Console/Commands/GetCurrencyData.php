<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Currency;
use Exception;

class GetCurrencyData extends Command
{
    protected $signature = 'currency:get';
    protected $description = 'Get currency data from the database in JSON format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $currencies = Currency::all();

            if ($currencies->isEmpty()) {
                $this->error('No currency data.');
                return;
            }

            $currencyData = $currencies->map(function ($currency) {
                return [
                    'id' => $currency->id,
                    'num_code' => $currency->num_code,
                    'char_code' => $currency->char_code,
                    'nominal' => $currency->nominal,
                    'name' => $currency->name,
                    'value' => $currency->value,
                    'vunit_rate' => $currency->vunit_rate,
                ];
            });

            $this->info(json_encode($currencyData, JSON_PRETTY_PRINT));
        } catch (Exception $error) {
            Log::error('Failed to get currency data. Message: ' . $error->getMessage());
            return $this->error('Failed to get currency data: ' . $error->getMessage());
        }
    }
}
