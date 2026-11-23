<?php

/*
|--------------------------------------------------------------------------
| Job Desk alert timings
|--------------------------------------------------------------------------
| Alerts are worked out live every time a Job Desk page is opened, so they
| keep working even if the server's scheduler stops.
*/

return [
    // Assigned staff member has not pressed "Accept"
    'accept_warn_minutes' => (int) env('JOB_DESK_ACCEPT_WARN_MINUTES', 30),
    'accept_escalate_minutes' => (int) env('JOB_DESK_ACCEPT_ESCALATE_MINUTES', 60),

    // Promised time is coming up
    'due_soon_minutes' => (int) env('JOB_DESK_DUE_SOON_MINUTES', 120),

    // In progress / waiting parts with no remark or status change
    'no_update_hours' => (int) env('JOB_DESK_NO_UPDATE_HOURS', 24),

    // Completed but invoice not given to the customer
    'not_invoiced_hours' => (int) env('JOB_DESK_NOT_INVOICED_HOURS', 24),

    // "Customer must be called" escalation
    'call_team_minutes' => (int) env('JOB_DESK_CALL_TEAM_MINUTES', 30),
    'call_admin_minutes' => (int) env('JOB_DESK_CALL_ADMIN_MINUTES', 60),

    // Languages offered in the toggle
    'locales' => [
        'en' => 'English',
        'bn' => 'বাংলা',
        'si' => 'සිංහල',
    ],
];
