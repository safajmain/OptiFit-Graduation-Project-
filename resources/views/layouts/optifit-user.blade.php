<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="volt">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0d120f">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="{{ asset('optifit-apex-assets/js/apex-theme.js') }}"></script>

    <title>@yield('title', 'OptiFit APEX')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link href="{{ asset('optifit-apex-assets/css/apex-shared.css') }}" rel="stylesheet">
    <link href="{{ asset('optifit-apex-assets/css/user-dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('optifit-apex-assets/css/apex-features.css') }}" rel="stylesheet">
    <link href="{{ asset('optifit-apex-assets/css/user-shell.css') }}" rel="stylesheet">

    <style>
        /* Floating AI Chat */

        .optifit-chat-launcher {
            position: fixed;
            left: 24px;
            bottom: 24px;
            z-index: 9999;

            width: 62px;
            height: 62px;
            border: none;
            border-radius: 50%;

            background: #59d7f5;
            color: #071522;

            box-shadow: 0 8px 30px rgba(0, 0, 0, .35);

            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 28px;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .optifit-chat-launcher:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(89, 215, 245, .25);
        }

        .optifit-chat-panel {
            position: fixed;
            left: 24px;
            bottom: 100px;
            z-index: 9999;

            width: min(370px, calc(100vw - 32px));
            height: min(500px, calc(100dvh - 135px));

            display: flex;
            flex-direction: column;

            background: #0d2030;
            color: #fff;

            border: 1px solid #2d5368;
            border-radius: 20px;
            overflow: hidden;

            box-shadow: 0 15px 45px rgba(0, 0, 0, .5);
            font-family: 'Cairo', sans-serif;
        }

        .optifit-chat-panel[hidden] {
            display: none !important;
        }

        .optifit-chat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;

            padding: 15px 17px;
            background: #143249;
            border-bottom: 1px solid #2d5368;
        }

        .optifit-chat-header strong {
            display: block;
            font-size: 15px;
            color: #fff;
        }

        .optifit-chat-header small {
            display: block;
            margin-top: 3px;
            color: #a6c4d2;
            font-size: 11px;
        }

        .optifit-chat-close {
            background: transparent;
            border: 1px solid #42657a;
            border-radius: 9px;
            color: white;
            cursor: pointer;
            width: 32px;
            height: 32px;
            font-size: 22px;
        }

        .optifit-chat-messages {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 16px;

            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .optifit-chat-message {
            max-width: 88%;
            padding: 11px 13px;
            border-radius: 14px;
            line-height: 1.8;
            font-size: 13px;
            overflow-wrap: anywhere;
            white-space: pre-wrap;
        }

        .optifit-chat-message.bot {
            align-self: flex-start;
            background: #1d4056;
            color: #eaf7fc;
            border-bottom-right-radius: 4px;
        }

        .optifit-chat-message.user {
            align-self: flex-end;
            background: #59d7f5;
            color: #071522;
            border-bottom-left-radius: 4px;
        }

        .optifit-chat-form {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 12px;

            background: #102b3f;
            border-top: 1px solid #2d5368;
        }

        .optifit-chat-input {
            flex: 1;
            min-width: 0;
            padding: 11px 12px;

            border-radius: 11px;
            border: 1px solid #3a6075;

            background: #0a1c2b;
            color: #fff;
            font-family: 'Cairo', sans-serif;
            font-size: 12px;
            outline: none;
        }

        .optifit-chat-input:focus {
            border-color: #59d7f5;
        }

        .optifit-chat-send {
            border: none;
            border-radius: 11px;
            padding: 10px 13px;

            background: #59d7f5;
            color: #071522;
            font-family: 'Cairo', sans-serif;
            font-weight: 800;
            cursor: pointer;
        }

        @media (max-width: 480px) {
            .optifit-chat-launcher {
                left: 16px;
                bottom: 16px;
                width: 56px;
                height: 56px;
            }

            .optifit-chat-panel {
                left: 16px;
                bottom: 85px;
                width: calc(100vw - 32px);
                height: min(480px, calc(100dvh - 110px));
            }
        }
    </style>

    @stack('head')
</head>

<body>

    @include('optifit_apex.partials.icons')

    <a class="skip-link" href="#dashboard-main">
        انتقل إلى المحتوى الرئيسي
    </a>

    <div class="dashboard-shell" data-dashboard-shell>

        <button
            class="mobile-shade"
            type="button"
            data-sidebar-close
            aria-label="إغلاق القائمة"></button>

        @include('optifit_apex.partials.user-sidebar')

        <main class="dashboard-main" id="dashboard-main" tabindex="-1">

            <header class="dashboard-topbar">

                <div class="topbar-welcome">

                    <button
                        class="icon-button menu-button"
                        type="button"
                        data-sidebar-open
                        aria-label="فتح القائمة"
                        aria-controls="dashboard-sidebar"
                        aria-expanded="false">
                        <svg aria-hidden="true">
                            <use href="#apx-menu" />
                        </svg>
                    </button>

                    <div>
                        <span class="eyebrow">
                            @yield('eyebrow', 'OPTIFIT / YOUR SPACE')
                        </span>

                        <h1>
                            @yield('page-heading', 'مساحتك الرياضية')
                        </h1>
                    </div>

                </div>

                <div class="topbar-actions">

                    @yield('actions')

                    <button
                        class="icon-button theme-button"
                        type="button"
                        data-theme-open
                        aria-label="فتح إعدادات المظهر">
                        <svg aria-hidden="true">
                            <use href="#apx-palette" />
                        </svg>
                    </button>

                    <a
                        class="user-avatar"
                        href="{{ route('profile.edit') }}"
                        aria-label="فتح إعدادات حساب {{ auth()->user()->name }}">
                        {{ mb_strtoupper(mb_substr(trim(auth()->user()->name), 0, 1)) ?: 'O' }}
                    </a>

                </div>

            </header>

            @if(session('success'))
            <div class="status-message" role="status">
                <svg aria-hidden="true">
                    <use href="#apx-check" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if(session('status') === 'profile-updated')
            <div class="status-message" role="status">
                <svg aria-hidden="true">
                    <use href="#apx-check" />
                </svg>
                <span>تم تحديث معلومات الحساب.</span>
            </div>
            @endif

            @if(session('status') === 'password-updated')
            <div class="status-message" role="status">
                <svg aria-hidden="true">
                    <use href="#apx-check" />
                </svg>
                <span>تم تحديث كلمة المرور.</span>
            </div>
            @endif

            @if($errors->any())
            <div class="apx-error-summary" role="alert" tabindex="-1">
                <h2>تعذر إكمال العملية:</h2>

                <ul>
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="user-page-content">
                @yield('content')
            </div>

        </main>

    </div>

    @include('optifit_apex.partials.user-theme-studio')

    <!-- Floating Chat Button -->
    <button
        id="optifit-chat-launcher"
        class="optifit-chat-launcher"
        type="button"
        aria-label="فتح شات OptiFit"
        aria-controls="optifit-chat-panel"
        aria-expanded="false">
        💬
    </button>

    <!-- Floating Chat Panel -->
    <section
        id="optifit-chat-panel"
        class="optifit-chat-panel"
        aria-label="محادثة OptiFit"
        hidden>
        <div class="optifit-chat-header">

            <div>
                <strong>مساعد OptiFit الذكي ✨</strong>
                <small>اسألني عن النوادي والاشتراكات والتفضيلات</small>
            </div>

            <button
                id="optifit-chat-close"
                class="optifit-chat-close"
                type="button"
                aria-label="إغلاق الشات">
                ×
            </button>

        </div>

        <div
            id="optifit-chat-messages"
            class="optifit-chat-messages"
            aria-live="polite">
            <div class="optifit-chat-message bot">
                أهلًا فيك في OptiFit! 👋
                كيف بقدر أساعدك تختار النادي المناسب؟
            </div>
        </div>

        <form id="optifit-chat-form" class="optifit-chat-form">

            <input
                id="optifit-chat-input"
                class="optifit-chat-input"
                type="text"
                placeholder="اكتب سؤالك هون..."
                aria-label="رسالتك"
                maxlength="1000"
                autocomplete="off"
                required>

            <button class="optifit-chat-send" type="submit">
                إرسال
            </button>

        </form>

    </section>

    <script
        defer
        src="{{ asset('optifit-apex-assets/js/user-dashboard.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const launcher = document.getElementById('optifit-chat-launcher');
            const panel = document.getElementById('optifit-chat-panel');
            const closeButton = document.getElementById('optifit-chat-close');
            const form = document.getElementById('optifit-chat-form');
            const input = document.getElementById('optifit-chat-input');
            const messages = document.getElementById('optifit-chat-messages');

            function openChat() {
                panel.hidden = false;
                launcher.setAttribute('aria-expanded', 'true');
                input.focus();
            }

            function closeChat() {
                panel.hidden = true;
                launcher.setAttribute('aria-expanded', 'false');
                launcher.focus();
            }

            launcher.addEventListener('click', function() {
                if (panel.hidden) {
                    openChat();
                } else {
                    closeChat();
                }
            });

            closeButton.addEventListener('click', closeChat);

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && !panel.hidden) {
                    closeChat();
                }
            });

            function addMessage(text, sender) {
                const message = document.createElement('div');

                message.className = 'optifit-chat-message ' + sender;
                message.textContent = text;

                messages.appendChild(message);
                messages.scrollTop = messages.scrollHeight;
            }

            form.addEventListener('submit', function(event) {
                event.preventDefault();

                const text = input.value.trim();

                if (!text) {
                    return;
                }

                addMessage(text, 'user');

                input.value = '';

                addMessage(
                    'وصلت رسالتك! واجهة الشات جاهزة، ولسه بدنا نربطها بالمساعد الذكي حتى أقدر أجاوبك عن النوادي.',
                    'bot'
                );
            });

        });
    </script>

    @stack('scripts')

</body>

</html>