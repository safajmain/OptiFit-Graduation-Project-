@extends('layouts.optifit-user')

@section('title', 'توصيات تناسبك | OptiFit APEX')
@section('eyebrow', 'USER / SMART MATCH')
@section('page-heading', 'توصيات تناسبك')

@section('content')

<section class="apx-page-hero">
    <div>
        <span class="apx-eyebrow">SMART MATCH / YOUR PREFERENCES</span>
        <h1>نوادي تناسب <em>تفضيلاتك.</em></h1>
        <p>
            نرشح لك النوادي المعتمدة بناءً على أنشطتك المفضلة،
            المرافق، المنطقة والميزانية المحفوظة.
        </p>
    </div>

    <a class="apx-secondary" href="{{ route('user.personal') }}">
        تحديث التفضيلات
    </a>
</section>

<div class="apx-stat-strip">
    <article class="apx-mini-stat">
        <strong>{{ $activityNames->count() }}</strong>
        <span>أنشطة مفضلة</span>
    </article>

    <article class="apx-mini-stat">
        <strong>{{ $facilityNames->count() }}</strong>
        <span>مرافق مفضلة</span>
    </article>

    <article class="apx-mini-stat">
        <strong>
            {{ $preference?->max_budget !== null
                ? number_format($preference->max_budget, 0) . ' د.أ'
                : '—' }}
        </strong>
        <span>الميزانية</span>
    </article>

    <article class="apx-mini-stat">
        <strong>{{ $recommendations->count() }}</strong>
        <span>نوادٍ مناسبة</span>
    </article>
</div>

@if (!$hasPreferences)

<section class="apx-panel apx-empty">
    <h2>خلينا نتعرف على تفضيلاتك أولًا</h2>
    <p>
        حددي الأنشطة والمرافق والميزانية أو المنطقة
        حتى نقدر نقترح عليك نوادي مناسبة.
    </p>
    <a class="apx-primary" href="{{ route('user.personal') }}">
        تحديد تفضيلاتي
    </a>
</section>

@elseif ($recommendations->isEmpty())

<section class="apx-panel apx-empty">
    <h2>ما لقينا نوادي مطابقة حاليًا</h2>
    <p>
        جرّبي تعديل تفضيلاتك أو استكشفي
        النوادي المعتمدة بنفسك.
    </p>

    <div class="apx-inline-actions">
        <a class="apx-primary" href="{{ route('user.personal') }}">
            تعديل التفضيلات
        </a>

        <a class="apx-secondary" href="{{ route('gyms.index') }}">
            استكشف النوادي
        </a>
    </div>
</section>

@else

<section class="apx-panel" style="margin-bottom: 20px;">
    <h2>اختاري النوادي اللي حابة تقارني بينها</h2>
    <p>
        النتائج مرتبة حسب درجة التوافق مع تفضيلاتك.
        اختاري ناديين أو ثلاثة، ثم اضغطي مقارنة.
    </p>
</section>

<form
    id="recommendations-compare-form"
    method="GET"
    action="{{ route('gyms.compare') }}">
    <div class="apx-cards">

        @foreach ($recommendations as $gym)

        <article class="apx-gym-tile">

            <div class="apx-tile-media">
                @if ($gym->images->first())
                <img
                    src="{{ asset('storage/' . $gym->images->first()->image_path) }}"
                    alt="صورة {{ $gym->gym_name }}"
                    loading="lazy">
                @else
                <div class="apx-tile-placeholder">
                    <svg>
                        <use href="#apx-spark" />
                    </svg>
                </div>
                @endif
            </div>

            <div class="apx-tile-body">

                <div class="apx-tile-meta">
                    <span>
                        {{ $gym->city }}
                        @if ($gym->area)
                        · {{ $gym->area }}
                        @endif
                    </span>

                    <span class="apx-status-chip approved">
                        {{ $gym->match_score }} نقاط توافق
                    </span>
                </div>

                <h2>{{ $gym->gym_name }}</h2>

                <p>
                    {{ \Illuminate\Support\Str::limit(
                                $gym->description ?: 'لا يوجد وصف مضاف.',
                                100
                            ) }}
                </p>

                @if (!empty($gym->match_reasons))
                <div style="margin: 14px 0;">
                    <strong>ليش رشحنا لك هذا النادي؟</strong>

                    <ul style="padding-inline-start: 20px; margin-top: 8px;">
                        @foreach ($gym->match_reasons as $reason)
                        <li>{{ $reason }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="apx-tile-footer">

                    <span>
                        {{ $gym->memberships_min_price_jod !== null
                                    ? number_format($gym->memberships_min_price_jod, 2) . ' د.أ'
                                    : 'السعر غير محدد' }}
                    </span>

                    <a
                        class="apx-primary"
                        href="{{ route('gyms.show', $gym) }}">
                        التفاصيل
                    </a>

                </div>

                <label
                    style="display: flex; align-items: center; gap: 8px; margin-top: 16px;">
                    <input
                        type="checkbox"
                        name="gyms[]"
                        value="{{ $gym->gym_id }}"
                        class="recommendation-compare-checkbox">
                    <span>اختيار للمقارنة</span>
                </label>

            </div>

        </article>

        @endforeach

    </div>

    <div
        class="apx-panel"
        style="margin-top: 22px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
        <span id="compare-selected-count">
            تم اختيار 0 من 3 نوادٍ
        </span>

        <button
            id="recommendations-compare-button"
            class="apx-primary"
            type="submit"
            disabled>
            قارن النوادي المختارة
        </button>

        <a class="apx-secondary" href="{{ route('gyms.index') }}">
            استكشف المزيد
        </a>
    </div>

</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById(
            'recommendations-compare-form'
        );

        const checkboxes = Array.from(
            document.querySelectorAll(
                '.recommendation-compare-checkbox'
            )
        );

        const countLabel = document.getElementById(
            'compare-selected-count'
        );

        const compareButton = document.getElementById(
            'recommendations-compare-button'
        );

        function updateComparison() {
            const selected = checkboxes.filter(
                checkbox => checkbox.checked
            );

            countLabel.textContent =
                'تم اختيار ' + selected.length + ' من 3 نوادٍ';

            compareButton.disabled =
                selected.length < 2 || selected.length > 3;

            checkboxes.forEach(function(checkbox) {
                checkbox.disabled = !checkbox.checked && selected.length >= 3;
            });
        }

        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener(
                'change',
                updateComparison
            );
        });

        form.addEventListener('submit', function(event) {
            const selectedCount = checkboxes.filter(
                checkbox => checkbox.checked
            ).length;

            if (selectedCount < 2 || selectedCount > 3) {
                event.preventDefault();
            }
        });

        updateComparison();
    });
</script>

@endif

@endsection