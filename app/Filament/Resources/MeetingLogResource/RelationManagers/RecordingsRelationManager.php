<?php

namespace App\Filament\Resources\MeetingLogResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class RecordingsRelationManager extends RelationManager
{
    protected static string  $relationship = 'recordings';
    protected static ?string $title        = 'Voice Recordings';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recorded_at')
                    ->label('Recorded At')
                    ->dateTime('H:i:s · M j, Y', timezone: 'Asia/Manila')
                    ->sortable(),

                Tables\Columns\TextColumn::make('speaker')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Host'  => 'warning',
                        'Guest' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('file_size_label')
                    ->label('File Size')
                    ->getStateUsing(fn ($record) => $record->file_size_label),

                Tables\Columns\TextColumn::make('file_path')
                    ->label('Audio')
                    ->html()
                    ->formatStateUsing(function ($record) {
                        return '<audio controls src="' . $record->file_url . '" style="height: 40px; max-width: 250px;"></audio>';
                    }),

            ])
            ->defaultSort('recorded_at', 'asc')
            ->recordActions([
                Actions\DeleteAction::make()
                    ->label('Delete'),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}