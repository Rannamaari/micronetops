{{-- Job Desk: fonts for Bangla and Sinhala, plus the language toggle --}}
@push('styles')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=noto-sans-bengali:400,600|noto-sans-sinhala:400,600&display=swap" rel="stylesheet" />
    <style>
        html:lang(bn) body { font-family: 'Noto Sans Bengali', 'Figtree', sans-serif; }
        html:lang(si) body { font-family: 'Noto Sans Sinhala', 'Figtree', sans-serif; }
    </style>
@endpush
