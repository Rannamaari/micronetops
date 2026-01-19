{{-- resources/views/jobs/calendar.blade.php - Mobile-First Optimized --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Calendar
            </h2>
            {{-- Header buttons - visible on all screens --}}
            <div class="flex gap-2 sm:gap-3">
                <a href="{{ route('jobs.index') }}"
                   class="inline-flex items-center px-3 py-2 sm:px-4 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-medium text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    List
                </a>
                <a href="{{ route('jobs.create') }}"
                   class="inline-flex items-center px-3 py-2 sm:px-4 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-700 focus:outline-none">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New
                </a>
            </div>
        </div>
    </x-slot>

    {{-- FullCalendar CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
    <style>
        .fc {
            font-family: inherit;
        }
        .fc-event {
            cursor: pointer;
            border-radius: 6px;
            padding: 2px 6px;
            font-size: 12px;
        }
        .fc-daygrid-event {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .dark .fc-theme-standard td,
        .dark .fc-theme-standard th {
            border-color: #374151;
        }
        .dark .fc-theme-standard .fc-scrollgrid {
            border-color: #374151;
        }
        .dark .fc-col-header-cell-cushion,
        .dark .fc-daygrid-day-number {
            color: #e5e7eb;
        }
        .dark .fc-day-today {
            background-color: rgba(79, 70, 229, 0.1) !important;
        }
        .dark .fc-button-primary {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }
        .dark .fc-button-primary:hover {
            background-color: #4338ca;
        }
        .dark .fc-button-primary:disabled {
            background-color: #374151;
            border-color: #374151;
        }
        .fc-timegrid-slot {
            height: 2.5em;
        }

        /* Hide FullCalendar default toolbar - we use custom */
        .fc .fc-toolbar {
            display: none !important;
        }

        /* Compact month grid on mobile */
        @media (max-width: 640px) {
            .fc-daygrid-day-frame {
                min-height: 45px;
            }
            .fc-daygrid-day-top {
                justify-content: center;
            }
            .fc-daygrid-day-number {
                padding: 2px;
                font-size: 0.75rem;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
            }
            .fc-day-today .fc-daygrid-day-number {
                background-color: #4f46e5;
                color: white;
            }
            /* Hide events in month grid on mobile - show in agenda below */
            .fc-daygrid-event-harness {
                display: none;
            }
            /* Show dot indicators instead */
            .fc-daygrid-day-events {
                display: flex;
                justify-content: center;
                gap: 2px;
                margin-top: 2px;
            }
            .fc-daygrid-event-dot {
                display: block;
                width: 6px;
                height: 6px;
                border-radius: 50%;
            }
            /* Hide all-day section when empty */
            .fc-timegrid-divider,
            .fc-timegrid-axis-cushion {
                display: none;
            }
            .fc-col-header-cell-cushion {
                font-size: 0.7rem;
                padding: 4px 2px;
            }
        }

        /* Desktop styles */
        @media (min-width: 641px) {
            .fc-daygrid-day-frame {
                min-height: 100px;
            }
        }

        /* View button active state */
        .view-btn.active {
            background-color: white;
            color: #4f46e5;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .dark .view-btn.active {
            background-color: #374151;
            color: #818cf8;
        }
        .view-btn:not(.active) {
            color: #6b7280;
        }
        .dark .view-btn:not(.active) {
            color: #9ca3af;
        }

        /* Quick create modal */
        #quick-create-modal {
            backdrop-filter: blur(4px);
        }
        .modal-content {
            animation: slideIn 0.2s ease-out;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <div class="max-w-7xl mx-auto" x-data="{ showFilters: false }">
        {{-- Sticky Calendar Header --}}
        <div class="sticky top-0 z-40 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
            {{-- Row 1: Navigation --}}
            <div class="flex items-center justify-between px-3 py-2">
                <div class="flex items-center gap-1">
                    <button id="cal-prev" type="button"
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <h3 id="cal-title" class="text-base font-semibold text-gray-900 dark:text-gray-100 min-w-[120px] text-center">
                        January 2026
                    </h3>
                    <button id="cal-next" type="button"
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 active:bg-gray-200">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <button id="cal-today" type="button"
                            class="px-3 py-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-full hover:bg-indigo-100 dark:hover:bg-indigo-900/50">
                        Today
                    </button>
                    <button type="button" @click="showFilters = !showFilters"
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                            :class="showFilters ? 'bg-gray-100 dark:bg-gray-700' : ''">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Row 2: View Segmented Control --}}
            <div class="flex items-center justify-between px-3 pb-2">
                <div id="view-toggle" class="inline-flex rounded-lg bg-gray-100 dark:bg-gray-700 p-0.5">
                    <button type="button" data-view="timeGridDay"
                            class="view-btn px-4 py-1.5 text-xs font-medium rounded-md transition-colors">
                        Day
                    </button>
                    <button type="button" data-view="timeGridWeek"
                            class="view-btn px-4 py-1.5 text-xs font-medium rounded-md transition-colors hidden sm:block">
                        Week
                    </button>
                    <button type="button" data-view="dayGridMonth"
                            class="view-btn px-4 py-1.5 text-xs font-medium rounded-md transition-colors">
                        Month
                    </button>
                </div>
                {{-- Legend --}}
                <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                        AC
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                        Bike
                    </span>
                </div>
            </div>

            {{-- Collapsible Filters --}}
            <div x-show="showFilters" x-collapse class="px-3 pb-3 border-t border-gray-100 dark:border-gray-700 pt-2">
                <div class="flex items-center gap-2">
                    <select id="type-filter"
                            class="flex-1 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm">
                        <option value="">All Types</option>
                        <option value="ac">AC Jobs</option>
                        <option value="moto">Bike Jobs</option>
                    </select>
                    <select id="assignee-filter"
                            class="flex-1 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm">
                        <option value="">All Technicians</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Mobile Date Strip (Day view only) --}}
        <div id="date-strip" class="sm:hidden bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 overflow-x-auto hidden">
            <div id="date-strip-inner" class="flex py-2 px-2 gap-1">
                {{-- Dates will be populated by JS --}}
            </div>
        </div>

        {{-- Calendar Container (hidden on mobile when in day/agenda view) --}}
        <div id="calendar-container" class="bg-white dark:bg-gray-800">
            <div id="calendar"></div>
        </div>

        {{-- Mobile Agenda List (shown below month grid or instead of day grid) --}}
        <div id="agenda-list" class="sm:hidden bg-gray-50 dark:bg-gray-900 hidden">
            <div class="px-3 py-2 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <h4 id="agenda-date-title" class="text-sm font-semibold text-gray-900 dark:text-gray-100">Today's Jobs</h4>
            </div>
            <div id="agenda-items" class="divide-y divide-gray-200 dark:divide-gray-700">
                {{-- Agenda items populated by JS --}}
            </div>
            <div id="agenda-empty" class="hidden p-8 text-center">
                <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-sm text-gray-500 dark:text-gray-400">No jobs scheduled</p>
                <button type="button" id="agenda-add-job"
                        class="mt-3 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium">
                    + Add Job
                </button>
            </div>
        </div>
    </div>

    {{-- Quick Create Modal - Mobile optimized --}}
    <div id="quick-create-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-end sm:items-center justify-center">
        <div class="modal-content bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-xl shadow-xl w-full sm:max-w-md sm:mx-4 p-5 sm:p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Quick Create Job</h3>
                <button type="button" id="cancel-quick-create-x"
                        class="p-2 -mr-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="quick-create-form" class="space-y-4">
                {{-- Job type selection with Alpine.js --}}
                <div x-data="{ jobType: 'moto' }" class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer" @click="jobType = 'moto'">
                        <input type="radio" name="quick_job_type" value="moto" class="sr-only" x-model="jobType">
                        <div class="flex items-center justify-center p-4 border-2 rounded-xl transition-all"
                             :class="jobType === 'moto'
                                 ? 'border-orange-500 bg-orange-100 dark:bg-orange-900/30'
                                 : 'border-gray-200 dark:border-gray-600'">
                            <span class="text-xl mr-2">🏍️</span>
                            <span class="font-bold text-gray-900 dark:text-gray-100">Bike</span>
                        </div>
                    </label>
                    <label class="cursor-pointer" @click="jobType = 'ac'">
                        <input type="radio" name="quick_job_type" value="ac" class="sr-only" x-model="jobType">
                        <div class="flex items-center justify-center p-4 border-2 rounded-xl transition-all"
                             :class="jobType === 'ac'
                                 ? 'border-sky-500 bg-sky-100 dark:bg-sky-900/30'
                                 : 'border-gray-200 dark:border-gray-600'">
                            <span class="text-xl mr-2">❄️</span>
                            <span class="font-bold text-gray-900 dark:text-gray-100">AC</span>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Phone</label>
                    <input type="tel" id="quick_customer_phone" name="customer_phone"
                           class="block w-full text-lg p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                           placeholder="7XXXXXX"
                           inputmode="tel"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Name</label>
                    <input type="text" id="quick_customer_name" name="customer_name"
                           class="block w-full text-lg p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                           placeholder="Customer name"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Issue (optional)</label>
                    <input type="text" id="quick_title" name="title"
                           class="block w-full p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                           placeholder="AC not cooling, bike won't start...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Schedule</label>
                    <input type="datetime-local" id="quick_scheduled_at" name="scheduled_at"
                           class="block w-full p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                           required>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" id="cancel-quick-create"
                            class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-semibold">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 active:bg-indigo-800">
                        Create Job
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- FullCalendar JS --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const typeFilter = document.getElementById('type-filter');
            const assigneeFilter = document.getElementById('assignee-filter');
            const modal = document.getElementById('quick-create-modal');
            const quickForm = document.getElementById('quick-create-form');
            const scheduledAtInput = document.getElementById('quick_scheduled_at');

            // Custom header elements
            const calTitle = document.getElementById('cal-title');
            const calPrev = document.getElementById('cal-prev');
            const calNext = document.getElementById('cal-next');
            const calToday = document.getElementById('cal-today');
            const viewBtns = document.querySelectorAll('.view-btn');
            const dateStrip = document.getElementById('date-strip');
            const dateStripInner = document.getElementById('date-strip-inner');
            const calendarContainer = document.getElementById('calendar-container');
            const agendaList = document.getElementById('agenda-list');
            const agendaItems = document.getElementById('agenda-items');
            const agendaEmpty = document.getElementById('agenda-empty');
            const agendaDateTitle = document.getElementById('agenda-date-title');

            const isMobile = window.innerWidth < 640;
            let currentView = isMobile ? 'dayGridMonth' : 'dayGridMonth';
            let selectedDate = new Date();
            let allEvents = [];

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: currentView,
                headerToolbar: false, // We use custom header
                editable: true,
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                nowIndicator: true,
                slotMinTime: '06:00:00',
                slotMaxTime: '22:00:00',
                eventTimeFormat: {
                    hour: 'numeric',
                    minute: '2-digit',
                    meridiem: 'short'
                },

                // Fetch events from API
                events: function(fetchInfo, successCallback, failureCallback) {
                    const params = new URLSearchParams({
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr
                    });

                    if (typeFilter.value) params.append('type', typeFilter.value);
                    if (assigneeFilter.value) params.append('assignee', assigneeFilter.value);

                    fetch(`{{ route('jobs.calendar-events') }}?${params}`)
                        .then(response => response.json())
                        .then(events => {
                            allEvents = events; // Store for agenda view
                            successCallback(events);
                            // Update agenda if on mobile
                            if (isMobile) {
                                loadAgendaForDate(selectedDate);
                            }
                        })
                        .catch(error => failureCallback(error));
                },

                // Click on event to view job
                eventClick: function(info) {
                    window.location.href = `/jobs/${info.event.id}`;
                },

                // Click on date/time to create job
                select: function(info) {
                    // Format datetime for input
                    const startDate = new Date(info.start);
                    const localDatetime = startDate.toISOString().slice(0, 16);
                    scheduledAtInput.value = localDatetime;

                    // Show modal
                    modal.classList.remove('hidden');
                    document.getElementById('quick_customer_phone').focus();
                },

                // Drag and drop to reschedule
                eventDrop: function(info) {
                    const jobId = info.event.id;
                    const newStart = info.event.start.toISOString();
                    const newEnd = info.event.end ? info.event.end.toISOString() : null;

                    fetch(`/jobs/${jobId}/reschedule`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            scheduled_at: newStart,
                            scheduled_end_at: newEnd
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            info.revert();
                            alert('Failed to reschedule job');
                        }
                    })
                    .catch(() => {
                        info.revert();
                        alert('Failed to reschedule job');
                    });
                },

                // Event resize to change duration
                eventResize: function(info) {
                    const jobId = info.event.id;
                    const newEnd = info.event.end.toISOString();

                    fetch(`/jobs/${jobId}/reschedule`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            scheduled_at: info.event.start.toISOString(),
                            scheduled_end_at: newEnd
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            info.revert();
                        }
                    })
                    .catch(() => info.revert());
                }
            });

            calendar.render();

            // Update custom title
            function updateTitle() {
                const view = calendar.view;
                const date = view.currentStart;
                const options = { month: 'short', year: 'numeric' };
                if (view.type === 'timeGridDay') {
                    options.day = 'numeric';
                }
                calTitle.textContent = date.toLocaleDateString('en-US', options);
            }

            // Update view button states
            function updateViewButtons() {
                viewBtns.forEach(btn => {
                    if (btn.dataset.view === calendar.view.type) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            }

            // Build date strip for mobile day view
            function buildDateStrip() {
                if (!isMobile) return;

                dateStripInner.innerHTML = '';
                const today = new Date();
                const startDate = new Date(selectedDate);
                startDate.setDate(startDate.getDate() - 3);

                for (let i = 0; i < 14; i++) {
                    const date = new Date(startDate);
                    date.setDate(startDate.getDate() + i);
                    const isSelected = date.toDateString() === selectedDate.toDateString();
                    const isToday = date.toDateString() === today.toDateString();

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = `flex flex-col items-center px-3 py-1.5 rounded-lg min-w-[44px] ${
                        isSelected
                            ? 'bg-indigo-600 text-white'
                            : isToday
                                ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400'
                                : 'text-gray-600 dark:text-gray-400'
                    }`;
                    btn.innerHTML = `
                        <span class="text-[10px] font-medium uppercase">${date.toLocaleDateString('en-US', { weekday: 'short' })}</span>
                        <span class="text-sm font-semibold">${date.getDate()}</span>
                    `;
                    btn.addEventListener('click', () => {
                        selectedDate = date;
                        buildDateStrip();
                        loadAgendaForDate(date);
                    });
                    dateStripInner.appendChild(btn);
                }

                // Scroll to selected date
                setTimeout(() => {
                    const selected = dateStripInner.querySelector('.bg-indigo-600');
                    if (selected) {
                        selected.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                    }
                }, 100);
            }

            // Load agenda items for a specific date
            function loadAgendaForDate(date) {
                const dateStr = date.toDateString();
                agendaDateTitle.textContent = date.toLocaleDateString('en-US', {
                    weekday: 'long',
                    month: 'short',
                    day: 'numeric'
                });

                // Filter events for this date
                const dayEvents = allEvents.filter(e => {
                    const eventDate = new Date(e.start);
                    return eventDate.toDateString() === dateStr;
                }).sort((a, b) => new Date(a.start) - new Date(b.start));

                agendaItems.innerHTML = '';

                if (dayEvents.length === 0) {
                    agendaEmpty.classList.remove('hidden');
                    return;
                }

                agendaEmpty.classList.add('hidden');

                // Group by time of day
                const morning = dayEvents.filter(e => new Date(e.start).getHours() < 12);
                const afternoon = dayEvents.filter(e => {
                    const h = new Date(e.start).getHours();
                    return h >= 12 && h < 17;
                });
                const evening = dayEvents.filter(e => new Date(e.start).getHours() >= 17);

                function renderSection(title, events) {
                    if (events.length === 0) return;
                    const section = document.createElement('div');
                    section.innerHTML = `<div class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 text-xs font-medium text-gray-500 dark:text-gray-400">${title}</div>`;
                    events.forEach(event => {
                        const time = new Date(event.start).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                        const item = document.createElement('a');
                        item.href = `/jobs/${event.id}`;
                        item.className = 'flex items-center gap-3 px-3 py-3 bg-white dark:bg-gray-800 active:bg-gray-50 dark:active:bg-gray-700';
                        item.innerHTML = `
                            <div class="flex items-center gap-2 min-w-[60px]">
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: ${event.backgroundColor}"></span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">${time}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">${event.title}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">${event.extendedProps?.customer_name || ''}</div>
                            </div>
                            <span class="px-2 py-0.5 text-xs rounded-full flex-shrink-0" style="background-color: ${event.extendedProps?.status_color}20; color: ${event.extendedProps?.status_color}">
                                ${event.extendedProps?.status_label || ''}
                            </span>
                        `;
                        section.appendChild(item);
                    });
                    agendaItems.appendChild(section);
                }

                renderSection('Morning', morning);
                renderSection('Afternoon', afternoon);
                renderSection('Evening', evening);
            }

            // Switch view mode
            function switchView(viewType) {
                currentView = viewType;
                calendar.changeView(viewType);
                updateTitle();
                updateViewButtons();

                if (isMobile) {
                    if (viewType === 'timeGridDay') {
                        // Show date strip + agenda, hide calendar
                        dateStrip.classList.remove('hidden');
                        calendarContainer.classList.add('hidden');
                        agendaList.classList.remove('hidden');
                        buildDateStrip();
                        loadAgendaForDate(selectedDate);
                    } else if (viewType === 'dayGridMonth') {
                        // Show calendar + agenda below
                        dateStrip.classList.add('hidden');
                        calendarContainer.classList.remove('hidden');
                        agendaList.classList.remove('hidden');
                        loadAgendaForDate(selectedDate);
                    } else {
                        dateStrip.classList.add('hidden');
                        calendarContainer.classList.remove('hidden');
                        agendaList.classList.add('hidden');
                    }
                }
            }

            // Initial setup
            updateTitle();
            updateViewButtons();
            if (isMobile) {
                agendaList.classList.remove('hidden');
                loadAgendaForDate(selectedDate);
            }

            // Custom header button handlers
            calPrev.addEventListener('click', () => {
                calendar.prev();
                updateTitle();
                if (currentView === 'timeGridDay' && isMobile) {
                    selectedDate = calendar.getDate();
                    buildDateStrip();
                    loadAgendaForDate(selectedDate);
                }
            });

            calNext.addEventListener('click', () => {
                calendar.next();
                updateTitle();
                if (currentView === 'timeGridDay' && isMobile) {
                    selectedDate = calendar.getDate();
                    buildDateStrip();
                    loadAgendaForDate(selectedDate);
                }
            });

            calToday.addEventListener('click', () => {
                calendar.today();
                selectedDate = new Date();
                updateTitle();
                if (isMobile) {
                    buildDateStrip();
                    loadAgendaForDate(selectedDate);
                }
            });

            // View toggle buttons
            viewBtns.forEach(btn => {
                btn.addEventListener('click', () => switchView(btn.dataset.view));
            });

            // Date click in month view (mobile) - show agenda for that date
            calendar.on('dateClick', function(info) {
                if (isMobile && currentView === 'dayGridMonth') {
                    selectedDate = info.date;
                    loadAgendaForDate(info.date);
                }
            });

            // Filter change handlers
            typeFilter.addEventListener('change', () => calendar.refetchEvents());
            assigneeFilter.addEventListener('change', () => calendar.refetchEvents());

            // Agenda add job button
            document.getElementById('agenda-add-job')?.addEventListener('click', () => {
                const localDatetime = selectedDate.toISOString().slice(0, 16);
                scheduledAtInput.value = localDatetime;
                modal.classList.remove('hidden');
                document.getElementById('quick_customer_phone').focus();
            });

            // Close modal helper
            function closeModal() {
                modal.classList.add('hidden');
                quickForm.reset();
            }

            // Modal handlers
            document.getElementById('cancel-quick-create').addEventListener('click', closeModal);
            document.getElementById('cancel-quick-create-x').addEventListener('click', closeModal);

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // Quick create form submit
            quickForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = {
                    job_type: document.querySelector('input[name="quick_job_type"]:checked').value,
                    customer_phone: document.getElementById('quick_customer_phone').value,
                    customer_name: document.getElementById('quick_customer_name').value,
                    title: document.getElementById('quick_title').value,
                    scheduled_at: document.getElementById('quick_scheduled_at').value
                };

                fetch('{{ route('jobs.quick-create') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        modal.classList.add('hidden');
                        quickForm.reset();
                        calendar.refetchEvents();
                        // Optionally redirect to job
                        // window.location.href = data.redirect;
                    } else {
                        alert('Failed to create job');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to create job');
                });
            });
        });
    </script>
</x-app-layout>
