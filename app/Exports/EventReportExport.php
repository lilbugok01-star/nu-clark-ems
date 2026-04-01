<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EventReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Event::with('organizer')->withCount('registrations')->orderByDesc('event_date')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Title', 'Venue', 'Date', 'Start', 'End', 'Capacity', 'Registrations', 'Organizer', 'Status'];
    }

    public function map($event): array
    {
        return [
            $event->id,
            $event->title,
            $event->venue,
            $event->event_date->format('Y-m-d'),
            $event->start_time,
            $event->end_time,
            $event->capacity,
            $event->registrations_count,
            $event->organizer->name ?? '-',
            ucfirst($event->status),
        ];
    }
}
