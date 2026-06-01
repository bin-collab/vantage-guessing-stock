<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stocks = [
            ['symbol' => 'TSLA.24H', 'name' => 'Tesla'],
            ['symbol' => 'NVIDIA.24H', 'name' => 'Nvidia'],
            ['symbol' => 'AAPL.24H', 'name' => 'Apple'],
            ['symbol' => 'GOOG.24H', 'name' => 'Google'],
            ['symbol' => 'META.24H', 'name' => 'Meta'],
            ['symbol' => 'AMAZON.24H', 'name' => 'Amazon'],
        ];

        foreach ($stocks as $stock) {
            \App\Models\Stock::updateOrCreate(['symbol' => $stock['symbol']], $stock);
        }
    }
}
