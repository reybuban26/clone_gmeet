<?php

namespace App\Filament\Resources\MeetingLogResource\Pages;

use App\Filament\Resources\MeetingLogResource;
use Filament\Resources\Pages\ListRecords;

class ListMeetingLogs extends ListRecords
{
    protected static string $resource = MeetingLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
