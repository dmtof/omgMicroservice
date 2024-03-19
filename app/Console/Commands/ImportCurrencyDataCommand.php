<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use SimpleXMLElement;
use App\Models\Currency;
use Exception;

class ImportCurrencyDataCommand extends Command
{
    protected $signature = 'import:cdc';
    protected $description = 'Import currencies data from the CBR';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cacheKey = 'currency_rates_' . now()->format('Y-m-d');

        if (Cache::has($cacheKey)) {
            $this->info('Currency rates have already been imported today.');
            return;
        }

        $cbrUrl = env('CBR_URL', 'http://www.cbr.ru/scripts/XML_daily.asp'); // get url from .env file
        $client = new Client(); // create new Guzzle client

        while (true) {
            try {
                $response = $client->get($cbrUrl);
                $xmlContent = $response->getBody()->getContents();

                $xml = new SimpleXMLElement($xmlContent);

                foreach ($xml->Valute as $valute) {
                    Currency::updateOrCreate(
                        ['id' => (string)$valute['ID']],
                        [
                            'num_code' => (int)$valute->NumCode,
                            'char_code' => (string)$valute->CharCode,
                            'nominal' => (int)$valute->Nominal,
                            'name' => (string)$valute->Name,
                            'value' => (float)str_replace(',', '.', $valute->Value),
                            'vunit_rate' => (float)str_replace(',', '.', $valute->VunitRate),
                        ]
                    );
                }

                Cache::put($cacheKey, true, now()->endOfDay());
                $this->info('Currency rates have been imported and stored successfully.');
                break;
            } catch (Exception $error) {
                Log::error('Failed to import currency rates. Message: ' . $error->getMessage());
                $this->warn('Failed to import currency rates. Retrying... ');
                sleep(10);
            }
        }
    }
}
