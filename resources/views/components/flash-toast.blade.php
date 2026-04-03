@php
    $toastMessages = [];

    foreach (['success', 'sucesso'] as $key) {
        if (session()->has($key) && filled((string) session($key))) {
            $toastMessages[] = ['type' => 'success', 'text' => (string) session($key)];
        }
    }

    foreach (['error', 'erro'] as $key) {
        if (session()->has($key) && filled((string) session($key))) {
            $toastMessages[] = ['type' => 'error', 'text' => (string) session($key)];
        }
    }

    if (session()->has('warning') && filled((string) session('warning'))) {
        $toastMessages[] = ['type' => 'warning', 'text' => (string) session('warning')];
    }

    if (session()->has('info') && filled((string) session('info'))) {
        $toastMessages[] = ['type' => 'info', 'text' => (string) session('info')];
    }

    if ($errors->any()) {
        foreach ($errors->all() as $errorMsg) {
            if (filled((string) $errorMsg)) {
                $toastMessages[] = ['type' => 'error', 'text' => (string) $errorMsg];
            }
        }
    }
@endphp

@if (!empty($toastMessages))
    <div id="ff-toast-stack" class="ff-toast-stack" aria-live="polite" aria-atomic="true">
        @foreach ($toastMessages as $toast)
            <div class="ff-toast ff-toast--{{ $toast['type'] }}" data-timeout="5000" role="status">
                <div class="ff-toast__content">{{ $toast['text'] }}</div>
                <button type="button" class="ff-toast__close" aria-label="Fechar">×</button>
            </div>
        @endforeach
    </div>

    <style>
        .ff-toast-stack {
            position: fixed;
            bottom: 16px;
            right: 16px;
            z-index: 3000;
            display: grid;
            gap: 10px;
            max-width: min(92vw, 420px);
        }

        .ff-toast {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: start;
            gap: 10px;
            padding: 12px 12px;
            border-radius: 12px;
            color: #fff;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.22);
            animation: ffToastIn .25s ease-out;
        }

        .ff-toast--success { background: linear-gradient(135deg, #16a34a, #15803d); }
        .ff-toast--error { background: linear-gradient(135deg, #dc2626, #b91c1c); }
        .ff-toast--warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .ff-toast--info { background: linear-gradient(135deg, #2563eb, #1d4ed8); }

        .ff-toast__content {
            font-size: .95rem;
            line-height: 1.35;
            font-weight: 600;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .ff-toast__close {
            border: none;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            width: 26px;
            height: 26px;
            border-radius: 999px;
            cursor: pointer;
            font-size: 18px;
            line-height: 1;
            padding: 0;
        }

        .ff-toast__close:hover {
            background: rgba(255, 255, 255, 0.32);
        }

        .ff-toast.is-hiding {
            animation: ffToastOut .2s ease-in forwards;
        }

        @keyframes ffToastIn {
            from { opacity: 0; transform: translateY(-8px) translateX(10px); }
            to { opacity: 1; transform: translateY(0) translateX(0); }
        }

        @keyframes ffToastOut {
            to { opacity: 0; transform: translateY(-8px) translateX(10px); }
        }

        @media (max-width: 640px) {
            .ff-toast-stack {
                right: 12px;
                left: 12px;
                bottom: calc(12px + env(safe-area-inset-bottom, 0px));
                max-width: none;
            }
        }
    </style>

@endif
