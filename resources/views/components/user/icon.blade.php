@props([
    'name',
    'size' => 20,
    'strokeWidth' => 2,
    'filled' => false,
])

<svg
    xmlns="http://www.w3.org/2000/svg"
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="{{ $filled ? 'currentColor' : 'none' }}"
    stroke="currentColor"
    stroke-width="{{ $strokeWidth }}"
    stroke-linecap="round"
    stroke-linejoin="round"
    {{ $attributes->merge(['class' => 'shrink-0']) }}
>
    @switch($name)
        @case('activity')
            <path d="M22 12h-4l-3 8-6-16-3 8H2" />
            @break

        @case('alert-triangle')
            <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" />
            <path d="M12 9v4" />
            <path d="M12 17h.01" />
            @break

        @case('bar-chart')
            <path d="M3 3v18h18" />
            <path d="M7 16V8" />
            <path d="M12 16V5" />
            <path d="M17 16v-6" />
            @break

        @case('bell')
            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 7 3 9H3c0-2 3-2 3-9" />
            <path d="M10 21h4" />
            @break

        @case('blocks')
            <rect width="7" height="7" x="3" y="3" rx="1" />
            <rect width="7" height="7" x="14" y="3" rx="1" />
            <rect width="7" height="7" x="14" y="14" rx="1" />
            <rect width="7" height="7" x="3" y="14" rx="1" />
            @break

        @case('book')
        @case('book-open')
            <path d="M2 4.5A2.5 2.5 0 0 1 4.5 2H11v20H4.5A2.5 2.5 0 0 1 2 19.5Z" />
            <path d="M22 4.5A2.5 2.5 0 0 0 19.5 2H13v20h6.5a2.5 2.5 0 0 0 2.5-2.5Z" />
            @break

        @case('calendar-check')
        @case('calendar-plus')
            <path d="M8 2v4" />
            <path d="M16 2v4" />
            <rect width="18" height="18" x="3" y="4" rx="2" />
            <path d="M3 10h18" />
            @if ($name === 'calendar-plus')
                <path d="M12 14v4" />
                <path d="M10 16h4" />
            @else
                <path d="m9 16 2 2 4-5" />
            @endif
            @break

        @case('check-circle')
        @case('check-circle-2')
            <circle cx="12" cy="12" r="10" />
            <path d="m9 12 2 2 4-4" />
            @break

        @case('check-square')
            <rect width="18" height="18" x="3" y="3" rx="2" />
            <path d="m9 12 2 2 4-4" />
            @break

        @case('chevron-down')
            <path d="m6 9 6 6 6-6" />
            @break

        @case('clock')
            <circle cx="12" cy="12" r="10" />
            <path d="M12 6v6l4 2" />
            @break

        @case('code')
            <path d="m16 18 6-6-6-6" />
            <path d="m8 6-6 6 6 6" />
            @break

        @case('database')
            <ellipse cx="12" cy="5" rx="9" ry="3" />
            <path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5" />
            <path d="M3 12c0 1.7 4 3 9 3s9-1.3 9-3" />
            @break

        @case('edit')
            <path d="M12 20h9" />
            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
            @break

        @case('eye')
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
            <circle cx="12" cy="12" r="3" />
            @break

        @case('file-text')
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
            <path d="M14 2v6h6" />
            <path d="M16 13H8" />
            <path d="M16 17H8" />
            <path d="M10 9H8" />
            @break

        @case('filter')
            <path d="M22 3H2l8 9.5V20l4 2v-9.5Z" />
            @break

        @case('graduation-cap')
            <path d="M22 10 12 5 2 10l10 5 10-5Z" />
            <path d="M6 12v5c3 2 9 2 12 0v-5" />
            @break

        @case('help-circle')
            <circle cx="12" cy="12" r="10" />
            <path d="M9.1 9a3 3 0 1 1 5.8 1c-.6 1.2-1.9 1.6-2.4 2.7" />
            <path d="M12 17h.01" />
            @break

        @case('history')
            <path d="M3 12a9 9 0 1 0 3-6.7" />
            <path d="M3 3v6h6" />
            <path d="M12 7v5l3 2" />
            @break

        @case('home')
            <path d="m3 10 9-7 9 7" />
            <path d="M5 10v10h14V10" />
            <path d="M9 20v-6h6v6" />
            @break

        @case('image')
            <rect width="18" height="18" x="3" y="3" rx="2" />
            <circle cx="9" cy="9" r="2" />
            <path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21" />
            @break

        @case('key')
            <circle cx="7.5" cy="15.5" r="5.5" />
            <path d="m14 10 6-6" />
            <path d="m18 6 2 2" />
            @break

        @case('layout-dashboard')
            <rect width="7" height="9" x="3" y="3" rx="1" />
            <rect width="7" height="5" x="14" y="3" rx="1" />
            <rect width="7" height="9" x="14" y="12" rx="1" />
            <rect width="7" height="5" x="3" y="16" rx="1" />
            @break

        @case('log-in')
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
            <path d="m10 17 5-5-5-5" />
            <path d="M15 12H3" />
            @break

        @case('log-out')
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <path d="m16 17 5-5-5-5" />
            <path d="M21 12H9" />
            @break

        @case('map-pin')
            <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z" />
            <circle cx="12" cy="10" r="3" />
            @break

        @case('more-vertical')
            <circle cx="12" cy="5" r="1" />
            <circle cx="12" cy="12" r="1" />
            <circle cx="12" cy="19" r="1" />
            @break

        @case('package')
            <path d="m7.5 4.3 9 5.2" />
            <path d="M21 8v8a2 2 0 0 1-1 1.7l-7 4a2 2 0 0 1-2 0l-7-4A2 2 0 0 1 3 16V8a2 2 0 0 1 1-1.7l7-4a2 2 0 0 1 2 0l7 4A2 2 0 0 1 21 8Z" />
            <path d="m3.3 7 8.7 5 8.7-5" />
            <path d="M12 22V12" />
            @break

        @case('plus')
            <path d="M12 5v14" />
            <path d="M5 12h14" />
            @break

        @case('plus-circle')
            <circle cx="12" cy="12" r="10" />
            <path d="M12 8v8" />
            <path d="M8 12h8" />
            @break

        @case('qr-code')
            <rect width="5" height="5" x="3" y="3" rx="1" />
            <rect width="5" height="5" x="16" y="3" rx="1" />
            <rect width="5" height="5" x="3" y="16" rx="1" />
            <path d="M16 16h.01" />
            <path d="M21 16h-2v3" />
            <path d="M16 21h2" />
            <path d="M21 21h.01" />
            @break

        @case('save')
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z" />
            <path d="M17 21v-8H7v8" />
            <path d="M7 3v5h8" />
            @break

        @case('school')
            <path d="m22 10-10-5-10 5 10 5 10-5Z" />
            <path d="M6 12v5c3 2 9 2 12 0v-5" />
            <path d="M22 10v6" />
            @break

        @case('search')
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.3-4.3" />
            @break

        @case('send')
            <path d="m22 2-7 20-4-9-9-4Z" />
            <path d="M22 2 11 13" />
            @break

        @case('settings')
            <path d="M12.2 2h-.4l-1 2.6a8 8 0 0 0-1.8.8L6.4 4.3l-2.1 2.1L5.4 9a8 8 0 0 0-.8 1.8L2 11.8v.4l2.6 1a8 8 0 0 0 .8 1.8l-1.1 2.6 2.1 2.1L9 18.6a8 8 0 0 0 1.8.8l1 2.6h.4l1-2.6a8 8 0 0 0 1.8-.8l2.6 1.1 2.1-2.1-1.1-2.6a8 8 0 0 0 .8-1.8l2.6-1v-.4l-2.6-1a8 8 0 0 0-.8-1.8l1.1-2.6-2.1-2.1L15 5.4a8 8 0 0 0-1.8-.8Z" />
            <circle cx="12" cy="12" r="3" />
            @break

        @case('shield')
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
            @break

        @case('shield-alert')
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
            <path d="M12 8v4" />
            <path d="M12 16h.01" />
            @break

        @case('upload')
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
            <path d="m17 8-5-5-5 5" />
            <path d="M12 3v12" />
            @break

        @case('user')
            <path d="M19 21a7 7 0 0 0-14 0" />
            <circle cx="12" cy="7" r="4" />
            @break

        @case('user-check')
            <path d="M16 21a6 6 0 0 0-12 0" />
            <circle cx="10" cy="7" r="4" />
            <path d="m16 11 2 2 4-4" />
            @break

        @case('user-circle')
            <circle cx="12" cy="12" r="10" />
            <circle cx="12" cy="10" r="3" />
            <path d="M7 20a5 5 0 0 1 10 0" />
            @break

        @case('users')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.9" />
            <path d="M16 3.1a4 4 0 0 1 0 7.8" />
            @break

        @case('x')
            <path d="M18 6 6 18" />
            <path d="m6 6 12 12" />
            @break

        @case('zap')
            <path d="M13 2 3 14h8l-1 8 10-12h-8Z" />
            @break

        @default
            <circle cx="12" cy="12" r="10" />
    @endswitch
</svg>
