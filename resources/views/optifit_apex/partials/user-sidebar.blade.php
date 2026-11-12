@php
$userNavigation = [
[['dashboard'], 'dashboard', 'apx-home', 'الرئيسية'],
[['gyms.index', 'gyms.show'], 'gyms.index', 'apx-search', 'البحث عن النوادي'],
[['gyms.map'], 'gyms.map', 'apx-map', 'الخريطة'],
[['gyms.compare'], 'gyms.compare', 'apx-compare', 'المقارنة'],
[['assistant.*'], 'assistant.index', 'apx-spark', 'التوصيات الذكية'],
[['favorites.*'], 'favorites.index', 'apx-heart', 'المفضلة'],
[['reminders.*'], 'reminders.index', 'apx-bell', 'التذكيرات'],
];

$profileNavigation = [
[['user.personal*'], 'user.personal', 'apx-user', 'المعلومات الشخصية'],
[['user.activities*'], 'user.activities', 'apx-target', 'الأنشطة المفضلة'],
[['user.facilities*'], 'user.facilities', 'apx-check', 'المرافق المفضلة'],
[['user.times*'], 'user.times', 'apx-clock', 'الأوقات المتاحة'],
];

$profileActive = request()->routeIs(
'user.personal*',
'user.activities*',
'user.facilities*',
'user.times*'
);
@endphp

<style>
    .sidebar-profile-group {
        width: 100%;
    }

    .sidebar-profile-group>summary {
        cursor: pointer;
        list-style: none;
    }

    .sidebar-profile-group>summary::-webkit-details-marker {
        display: none;
    }

    .sidebar-profile-subnav {
        padding-inline-start: 14px;
        margin-block: 4px 8px;
    }

    .sidebar-profile-subnav .sidebar-link {
        font-size: 0.9em;
        opacity: 0.88;
    }

    .sidebar-profile-subnav .sidebar-link:hover,
    .sidebar-profile-subnav .sidebar-link.is-active {
        opacity: 1;
    }
</style>


<aside class="sidebar is-expanded" id="dashboard-sidebar" data-sidebar aria-label="التنقل الرئيسي">
    <div class="sidebar-head">
        <a class="sidebar-brand" href="{{ route('dashboard') }}" aria-label="OptiFit APEX - لوحة المستخدم">
            <span class="brand-mark">
                <svg aria-hidden="true">
                    <use href="#apx-dumbbell" />
                </svg>
            </span>
            <span class="brand-name">OPTI<span>FIT</span> <small>APEX</small></span>
        </a>

        <button class="sidebar-collapse" type="button" data-sidebar-collapse aria-label="طي القائمة" aria-expanded="true">
            <svg aria-hidden="true">
                <use href="#apx-chevron" />
            </svg>
        </button>
    </div>

    <p class="sidebar-label">YOUR SPACE / مساحتك</p>

    <nav class="sidebar-nav">
        @foreach($userNavigation as [$patterns, $routeName, $icon, $label])
        @php($active = request()->routeIs(...$patterns))
        <a class="sidebar-link {{ $active ? 'is-active' : '' }}"
            href="{{ route($routeName) }}"
            @if($active) aria-current="page" @endif>
            <svg aria-hidden="true">
                <use href="#{{ $icon }}" />
            </svg>
            <span>{{ $label }}</span>
        </a>
        @endforeach

        {{-- ملفي الشخصي وتفضيلاتي --}}
        <details class="sidebar-profile-group" @if($profileActive) open @endif>
            <summary class="sidebar-link {{ $profileActive ? 'is-active' : '' }}">
                <svg aria-hidden="true">
                    <use href="#apx-user" />
                </svg>
                <span>ملفي الشخصي </span>
            </summary>

            <div class="sidebar-profile-subnav">
                @foreach($profileNavigation as [$patterns, $routeName, $icon, $label])
                @php($active = request()->routeIs(...$patterns))
                <a class="sidebar-link {{ $active ? 'is-active' : '' }}"
                    href="{{ route($routeName) }}"
                    @if($active) aria-current="page" @endif>
                    <svg aria-hidden="true">
                        <use href="#{{ $icon }}" />
                    </svg>
                    <span>{{ $label }}</span>
                </a>
                @endforeach
            </div>
        </details>

        <a class="sidebar-link {{ request()->routeIs('profile.*') ? 'is-active' : '' }}"
            href="{{ route('profile.edit') }}"
            @if(request()->routeIs('profile.*')) aria-current="page" @endif>
            <svg aria-hidden="true">
                <use href="#apx-settings" />
            </svg>
            <span>إعدادات الحساب</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <button class="sidebar-link palette-trigger" type="button" data-theme-open>
            <svg aria-hidden="true">
                <use href="#apx-palette" />
            </svg>
            <span>المظهر والألوان</span><i></i>
        </button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="sidebar-link logout-link" type="submit">
                <svg aria-hidden="true">
                    <use href="#apx-logout" />
                </svg>
                <span>تسجيل الخروج</span>
            </button>
        </form>
    </div>
</aside>