<?php

namespace App\Components;

use GuzzleHttp\Client;

class ImportCurrencyData
{
    public $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'http://www.cbr.ru/scripts/XML_daily.asp',
            'timeout'  => 2.0,
        ]);
    }
}
