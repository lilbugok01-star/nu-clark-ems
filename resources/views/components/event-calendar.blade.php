@props([
    'calendarId' => 'eventCalendar',
    'eventsUrl' => route('calendar.events.json'),
    'initialView' => 'dayGridMonth',
    'rightToolbar' => 'dayGridMonth,timeGridWeek,timeGridDay'
])

<div id="{{ $calendarId }}" style="min-height:500px"></div>

@push('scripts')
@once
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
@endonce
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calEl = document.getElementById('{{ $calendarId }}');
    if(calEl) {
        var cal = new FullCalendar.Calendar(calEl, {
            initialView: '{{ $initialView }}',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: '{{ $rightToolbar }}'
            },
            buttonIcons: false,
            buttonText: { today: 'Today', month: 'Month', week: 'Week', day: 'Day', prev: ' < ', next: ' > ' },
            slotMinTime: '06:00:00',
            slotMaxTime: '23:00:00',
            events: '{!! $eventsUrl !!}',
            eventClick: function(info) { if(typeof showCalendarEventModal === 'function') showCalendarEventModal(info); },
            height: 'auto'
        });
        cal.render();
    }
});
</script>
<style>
    #{{ $calendarId }} .fc .fc-button-primary {
        background-color: var(--nu-blue) !important;
        border-color: var(--nu-blue) !important;
        font-size: 0.85rem !important;
        font-weight: 700 !important;
        padding: 0.4rem 0.8rem !important;
        transition: all 0.2s ease !important;
    }
    #{{ $calendarId }} .fc .fc-button-primary:hover {
        background-color: var(--nu-blue-dk) !important;
        border-color: var(--nu-blue-dk) !important;
        transform: translateY(-1px);
    }
    #{{ $calendarId }} .fc .fc-button-active {
        background-color: var(--nu-gold) !important;
        border-color: var(--nu-gold) !important;
        color: var(--nu-blue) !important;
    }
    #{{ $calendarId }} .fc .fc-toolbar-title {
        font-size: 1.1rem !important;
        font-weight: 800 !important;
        color: var(--nu-blue);
    }
    #{{ $calendarId }} .fc-icon {
        font-size: 1.25em !important;
    }
</style>
@endpush
