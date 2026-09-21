{{--
    Shows Laravel flash messages (redirect()->with('success'|'error'|'warning'|'info', ...) or session()->flash(...))
    as a Mary UI toast, in the position configured on the layout's <x-toast>.
    Included by every layout; @once keeps it to a single toast per request even if a page includes it as well.
--}}
@once
    @php
        // Highest priority first: only one toast is shown at a time.
        $levels = [
            'error' => ['alert-error', 'o-x-circle', 6000],
            'warning' => ['alert-warning', 'o-exclamation-triangle', 5000],
            'success' => ['alert-success', 'o-check-circle', 3500],
            'info' => ['alert-info', 'o-information-circle', 4000],
        ];
        $payload = null;
        foreach ($levels as $type => [$css, $icon, $timeout]) {
            $message = session($type);
            if (is_string($message) && $message !== '') {
                $payload = ['toast' => [
                    'type' => $type,
                    'title' => e($message),   // Mary renders the title as HTML, so escape it
                    'description' => null,
                    'position' => 'toast-bottom toast-end',
                    'icon' => \Illuminate\Support\Facades\Blade::render("<x-mary-icon class='w-7 h-7' name='{$icon}' />"),
                    'css' => $css,
                    'timeout' => $timeout,
                    'noProgress' => false,
                    'progressClass' => null,
                ]];
                break;
            }
        }
    @endphp

    @if ($payload)
        {{--
            Wait a moment so the layout's <x-toast> has finished starting before the event fires.
            A page and its layout can both include this partial (Livewire renders them separately), so an identical
            toast arriving within 2 seconds is ignored.
        --}}
        <div x-data x-init="setTimeout(() => {
            const payload = {{ \Illuminate\Support\Js::from($payload) }};
            const key = payload.toast.type + '|' + payload.toast.title;
            if (window.__flashKey === key && Date.now() - window.__flashAt < 2000) return;
            window.__flashKey = key; window.__flashAt = Date.now();
            window.toast && window.toast(payload);
        }, 150)"></div>
    @endif
@endonce
