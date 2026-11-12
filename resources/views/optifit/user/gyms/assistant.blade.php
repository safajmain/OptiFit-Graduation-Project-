@extends('layouts.optifit-user')

@section('title', 'توصيات OptiFit الذكية')
@section('eyebrow', 'USER / SMART MATCH')
@section('page-heading', 'المساعد الذكي')

@section('content')

<style>
    .smart-recommendation {
        padding: 26px;
        margin: 22px 0;
        border: 1px solid rgba(94, 234, 212, .3);
        border-radius: 20px;
        background: linear-gradient(135deg, #173842, #102b35);
    }

    .smart-recommendation h2 {
        margin: 12px 0;
        font-size: clamp(1.5rem, 3vw, 2.2rem);
    }

    .smart-recommendation p {
        line-height: 1.9;
    }

    .smart-reasons {
        margin: 16px 0;
        padding-inline-start: 24px;
        line-height: 2;
    }

    .smart-price {
        font-weight: bold;
        color: #5eead4;
        margin: 12px 0;
    }

    .smart-alternatives {
        margin-top: 28px;
    }

    .smart-alternatives h2 {
        margin-bottom: 16px;
    }

    .smart-alternative-card {
        padding: 20px;
        border-radius: 16px;
        background: #17313b;
        border: 1px solid rgba(255, 255, 255, .1);
    }

    .smart-alternative-card h3 {
        margin: 8px 0;
    }

    .smart-alternative-card ul {
        padding-inline-start: 22px;
        line-height: 1.9;
    }

    .smart-alternative-card .apx-primary,
    .smart-recommendation .apx-primary {
        display: inline-flex;
        margin-top: 14px;
    }
</style>

<section class="apx-page-hero">
    <div>
        <span class="apx-eyebrow">
            SMART MATCH / PERSONALIZED FOR YOU
        </span>

        <h1>اكتشف النادي <em>الأنسب إلك.</em></h1>

        <p>
            OptiFit بحلل تفضيلاتك المحفوظة بالبروفايل
            وبيرشحلك النوادي الأكثر توافقًا معها تلقائيًا.
        </p>
    </div>

    <a class="apx-secondary"
        href="{{ route('user.personal') }}">
        تحديث تفضيلاتي
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
        <span>ميزانيتك</span>
    </article>

    <article class="apx-mini-stat">
        <strong>{{ $recommendations->count() }}</strong>
        <span>نوادٍ متوافقة</span>
    </article>
</div>

@if (!$hasPreferences)

<section class="apx-panel apx-empty">
    <h2>خلينا نتعرف على تفضيلاتك أولًا</h2>

    <p>
        حددي أنشطتك ومرافقك المفضلة وميزانيتك
        حتى نقدر نرشحلك النادي الأنسب.
    </p>

    <a class="apx-primary"
        href="{{ route('user.personal') }}">
        تحديد تفضيلاتي
    </a>
</section>

@elseif ($recommendations->isEmpty())

<section class="apx-panel apx-empty">
    <h2>ما لقينا نادي متوافق حاليًا</h2>

    <p>
        ما في نوادٍ حصلت على نقاط تطابق
        مع تفضيلاتك المحفوظة.
    </p>

    <a class="apx-primary"
        href="{{ route('user.personal') }}">
        تعديل التفضيلات
    </a>
</section>

@else

@php
$bestGym = $recommendations->first();
$alternatives = $recommendations->skip(1)->take(3);
@endphp

<section class="smart-recommendation">

    <span class="apx-eyebrow">
        🏆 OPTIFIT TOP RECOMMENDATION
    </span>

    <h2>النادي الأنسب إلك</h2>

    <h2>{{ $bestGym->gym_name }}</h2>

    <p>
        {{ $bestGym->city }}
        @if ($bestGym->area)
        · {{ $bestGym->area }}
        @endif
    </p>

    <p>
        هذا النادي حصل على أعلى نقاط توافق
        مع تفضيلاتك المحفوظة من بين
        النوادي اللي تم تقييمها.
    </p>

    <div class="smart-price">
        {{ $bestGym->memberships_min_price_jod !== null
                ? number_format($bestGym->memberships_min_price_jod, 2) . ' د.أ'
                : 'السعر غير محدد' }}
    </div>

    <p>
        نقاط التوافق:
        <strong>{{ $bestGym->match_score }}</strong>
    </p>

    <h3>✨ ليش اخترنالك هذا النادي؟</h3>

    @if (!empty($bestGym->match_reasons))
    <ul class="smart-reasons">
        @foreach ($bestGym->match_reasons as $reason)
        <li>{{ $reason }}</li>
        @endforeach
    </ul>
    @else
    <p>هذا النادي حقق أعلى نتيجة في التقييم الحالي.</p>
    @endif

    <a class="apx-primary"
        href="{{ route('gyms.show', $bestGym) }}">
        اكتشف تفاصيل النادي
    </a>

</section>

@if ($alternatives->isNotEmpty())

<section class="smart-alternatives">

    <h2>✨ بدائل مناسبة إلك</h2>

    <p>
        هاي نوادٍ ثانية حصلت على نقاط توافق
        مع تفضيلاتك، مرتبة بعد الاختيار الأول.
    </p>

    <div class="apx-cards">

        @foreach ($alternatives as $gym)

        <article class="smart-alternative-card">

            <span class="apx-eyebrow">
                RECOMMENDED ALTERNATIVE
            </span>

            <h3>{{ $gym->gym_name }}</h3>

            <p>
                {{ $gym->city }}
                @if ($gym->area)
                · {{ $gym->area }}
                @endif
            </p>

            <p class="smart-price">
                {{ $gym->memberships_min_price_jod !== null
                                ? number_format($gym->memberships_min_price_jod, 2) . ' د.أ'
                                : 'السعر غير محدد' }}
            </p>

            <p>
                نقاط التوافق:
                <strong>{{ $gym->match_score }}</strong>
            </p>

            @if (!empty($gym->match_reasons))
            <ul>
                @foreach ($gym->match_reasons as $reason)
                <li>{{ $reason }}</li>
                @endforeach
            </ul>
            @endif

            <a class="apx-primary"
                href="{{ route('gyms.show', $gym) }}">
                عرض تفاصيل النادي
            </a>

        </article>

        @endforeach

    </div>

</section>

@endif

@endif

@endsection