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

        /* Mobile-specific calendar styles */
        @media (max-width: 640px) {
            .fc .fc-toolbar {
                flex-direction: column;
                gap: 0.75rem;
            }
            .fc .fc-toolbar-title {
                font-size: 1.1rem;
                font-weight: 600;
            }
            .fc .fc-button {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
            }
            .fc .fc-button-group {
                display: flex;
            }
            .fc .fc-button-group > .fc-button {
                flex: 1;
            }
            .fc-daygrid-day-number {
                padding: 4px 8px;
                font-size: 0.875rem;
            }
            .fc-event {
                font-size: 11px;
                padding: 3px 5px;
            }
            .fc-timegrid-slot {
                height: 3em;
            }
            .fc-timegrid-event {
                font-size: 11px;
            }
            /* Make day cells taller on mobile for easier tapping */
            .fc-daygrid-day-frame {
                min-height: 80px;
            }
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

    <div class="py-2 sm:py-4">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            {{-- Filters - horizontally scrollable on mobile --}}
            <div class="overflow-x-auto -mx-2 px-2 sm:mx-0 sm:px-0 mb-3">
                <div class="flex items-center gap-2 min-w-max">
                    <select id="type-filter"
                            class="px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm min-h-[44px]">
                        <option value="">All</option>
                        <option value="ac">AC</option>
                        <option value="moto">Bike</option>
                    </select>

                    <select id="assignee-filter"
                            class="px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm min-h-[44px]">
                        <option value="">All Techs</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                        @endforeach
                    </select>

                    <div class="flex items-center gap-3 ml-2 text-sm text-gray-600 dark:text-gray-400">
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-sky-500"></span>
                            <span class="hidden sm:inline">AC</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                            <span class="hidden sm:inline">Bike</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Calendar Container --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-2 sm:p-4">
                <div id="calendar"></div>
            </div>

            {{-- Mobile tip --}}
            <p class="sm:hidden text-xs text-gray-500 dark:text-gray-400 text-center mt-3 px-4">
                Tap a time slot to create a job. Tap a job to view details.
            </p>
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

            const isMobile = window.innerWidth < 640;

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: isMobile ? 'timeGridDay' : 'dayGridMonth',
                headerToolbar: isMobile ? {
                    left: 'prev,next',
                    center: 'title',
                    right: 'timeGridDay,dayGridMonth'
                } : {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'Today',
                    month: 'Month',
                    week: 'Week',
                    day: 'Day'
                },
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
                        .then(events => successCallback(events))
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

            // Filter change handlers
            typeFilter.addEventListener('change', () => calendar.refetchEvents());
            assigneeFilter.addEventListener('change', () => calendar.refetchEvents());

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
