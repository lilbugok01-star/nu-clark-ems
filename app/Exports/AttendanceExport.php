<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private int $eventId) {}

    public function collection()
    {
        return Attendance::with(['registration.user.course', 'registration.user.section', 'verifiedBy'])
            ->whereHas('registration', fn($q) => $q->where('event_id', $this->eventId))
            ->get();
    }

    public function headings(): array
    {
        return ['#', 'Student Name', 'Student ID', 'Email', 'Course', 'Section', 'Checked In At', 'Status', 'Verified By'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->registration->user->full_name ?? '-',
            $row->registration->user->student_id ?? '-',
            $row->registration->user->email ?? '-',
            $row->registration->user->course->name ?? '-',
            $row->registration->user->section->name ?? '-',
            $row->checked_in_at?->format('Y-m-d H:i') ?? '-',
            ucfirst($row->status),
            $row->verifiedBy->full_name ?? 'Not verified',
        ];
    }
}
