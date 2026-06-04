<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('guess_start_date')
                    ->label('竞猜开始日期')
                    ->required(),
                DatePicker::make('guess_end_date')
                    ->label('竞猜结束日期')
                    ->required(),
                TextInput::make('daily_deadline')
                    ->label('每日截止时间 (GMT+3)')
                    ->placeholder('18:00')
                    ->regex('/^(?:[01]\d|2[0-3]):[0-5]\d$/')
                    ->validationMessages([
                        'regex' => '截止时间格式必须为 HH:MM，如 18:00',
                    ])
                    ->required(),
            ]);
    }
}
