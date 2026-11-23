{{-- Language toggle: EN | বাংলা | සිංහල --}}
<form method="POST" action="{{ route('desk.locale') }}" class="inline-flex rounded-lg border border-gray-300 bg-white overflow-hidden text-sm" aria-label="{{ __('Language') }}">
    @csrf
    @foreach(config('job_desk.locales') as $code => $name)
        <button type="submit" name="locale" value="{{ $code }}"
                class="px-3 py-1.5 font-medium {{ app()->getLocale() === $code ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}"
                @if(app()->getLocale() === $code) aria-pressed="true" @endif>
            {{ $code === 'en' ? 'EN' : $name }}
        </button>
    @endforeach
</form>
