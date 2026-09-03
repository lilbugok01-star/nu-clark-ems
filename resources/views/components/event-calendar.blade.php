@props([
    'calendarId' => 'eventCalendar',
    'eventsUrl' => route('calendar.events.json'),
    'initialView' => 'dayGridMonth',
    'rightToolbar' => 'dayGridMonth,timeGridWeek,timeGridDay'
])

<div id="{{ $calendarId }}" style="min-height:500px"></div>

@push('scripts')
@once
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'
    integrity='sha384-5JIwZN3kuxX2zKsavvNmbZ3zhZZMUtu/eQiK3BbXukpSXp0Cd2ZP4OAYKx7mrPgI' crossorigin='anonymous'></script>
@endonce
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calEl = document.getElementById('{{ $calendarId }}');
    if(calEl) {
        var isMobile = window.innerWidth < 768;
        var cal = new FullCalendar.Calendar(calEl, {
            initialView: isMobile ? 'listMonth' : '{{ $initialView }}',
            headerToolbar: isMobile ? {
                left: 'prev,next',
                center: 'title',
                right: 'listMonth,dayGridMonth'
            } : {
                left: 'prev,next today',
                center: 'title',
                right: '{{ $rightToolbar }}'
            },
            buttonIcons: false,
            buttonText: { today: 'Today', month: 'Month', week: 'Week', day: 'Day', list: 'List', prev: ' < ', next: ' > ' },
            slotMinTime: '06:00:00',
            slotMaxTime: '23:00:00',
            events: '{!! $eventsUrl !!}',
            eventClick: function(info) { if(typeof showCalendarEventModal === 'function') showCalendarEventModal(info); },
            height: 'auto',
            dayMaxEventRows: isMobile ? 2 : false,
            eventDisplay: isMobile ? 'dot' : 'auto',
            windowResize: function(arg) {
                var nowMobile = window.innerWidth < 768;
                if (nowMobile && cal.view.type !== 'listMonth') {
                    cal.changeView('listMonth');
                } else if (!nowMobile && cal.view.type === 'listMonth') {
                    cal.changeView('{{ $initialView }}');
                }
            }
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
    @media (max-width: 767px) {
        #{{ $calendarId }} .fc .fc-toolbar { flex-wrap: wrap; gap: 6px; }
        #{{ $calendarId }} .fc .fc-toolbar-title { font-size: 0.9rem !important; }
        #{{ $calendarId }} .fc .fc-button { font-size: 0.72rem !important; padding: 0.25rem 0.5rem !important; }
        #{{ $calendarId }} .fc .fc-daygrid-event { font-size: 0.7rem; }
        #{{ $calendarId }} .fc-daygrid-day-events { min-height: 1em !important; }
    }
</style>
@endpush
