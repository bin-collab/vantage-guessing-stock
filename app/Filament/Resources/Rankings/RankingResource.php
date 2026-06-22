<?php

namespace App\Filament\Resources\Rankings;

use App\Filament\Resources\Rankings\Pages\ManageRankings;
use App\Models\OptInUser;
use App\Models\Ranking;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use League\Csv\Writer;

class RankingResource extends Resource
{
    protected static ?string $model = Ranking::class;

    protected static ?string $navigationLabel = '竞猜排名';

    protected static ?string $slug = 'rankings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    // public static function getModelLabel(): string
    // {
    //     return 'Rankings';
    // }

    // public static function getPluralModelLabel(): string
    // {
    //     return 'Rankings';
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rank')
                    ->label('排名')
                    ->state(fn ($record, $rowLoop) => $rowLoop->iteration),
                TextColumn::make('email')
                    ->searchable()
                    ->label('邮箱'),
                TextColumn::make('uid')
                    ->label('UID')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('用户名')
                    ->searchable(),
                TextColumn::make('guesses_count')
                    ->label('猜中次数')
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn ($query) => $query->withCount(['guesses' => fn ($q) => $q->where('is_correct', true)]))
            ->defaultSort('guesses_count', 'desc')
            ->headerActions([
                Action::make('export_ranking')
                    ->label('导出排名')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $users = OptInUser::withCount(['guesses' => fn ($q) => $q->where('is_correct', true)])
                            ->orderBy('guesses_count', 'desc')
                            ->orderBy('created_at', 'asc')
                            ->get();

                        $csv = Writer::createFromFileObject(new \SplTempFileObject);
                        $csv->insertOne(['Rank', 'Email', 'UID', 'Name', 'Correct Guesses']);

                        foreach ($users as $index => $user) {
                            $csv->insertOne([
                                $index + 1,
                                $user->email,
                                $user->uid,
                                $user->name,
                                $user->guesses_count,
                            ]);
                        }

                        return response()->streamDownload(function () use ($csv) {
                            echo $csv->toString();
                        }, 'ranking_'.now()->format('Y-m-d').'.csv');
                    }),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRankings::route('/'),
        ];
    }
}
