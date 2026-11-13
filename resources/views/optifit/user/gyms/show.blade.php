@extends('layouts.optifit-user')
@section('title', $gym->gym_name.' | OptiFit APEX')
@section('eyebrow', 'USER / GYM DETAILS')
@section('page-heading', $gym->gym_name)
@section('actions')<a class="apx-secondary" href="{{ route('gyms.index') }}">عودة للبحث</a>@endsection
@section('content')

@push('head')
<style>
    .gym-rating-stars {
        display: flex;
        flex-direction: row-reverse;
        direction: rtl;
        justify-content: center;
        align-items: center;
        gap: 6px;
        width: 100%;
    }

    .gym-rating-stars input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
    }

    .gym-rating-stars label {
        font-size: 42px;
        line-height: 1;
        color: #667067;
        cursor: pointer;
        transition: color 0.15s ease, transform 0.15s ease;
    }

    .gym-rating-stars label:hover {
        transform: scale(1.12);
    }

    .gym-rating-stars label:hover,
    .gym-rating-stars label:hover~label,
    .gym-rating-stars input:checked~label {
        color: #ffbf36;
    }

    .gym-rating-stars input:focus-visible+label {
        outline: 2px solid #c6ff43;
        outline-offset: 3px;
        border-radius: 4px;
    }
</style>
@endpush

<section class="apx-page-hero">
    <div><span class="apx-eyebrow">APPROVED GYM / #{{ $gym->gym_id }}</span>
        <h1>{{ $gym->gym_name }} <em>بالتفصيل.</em></h1>
        <p>{{ $gym->description ?: 'لم يضف النادي وصفًا بعد.' }}</p>
    </div>
    <form method="POST" action="{{ $isFavorite ? route('favorites.destroy',$gym) : route('favorites.store',$gym) }}">@csrf @if($isFavorite) @method('DELETE') @endif<button class="{{ $isFavorite?'apx-danger':'apx-primary' }}" type="submit"><svg>
                <use href="#apx-heart" />
            </svg>{{ $isFavorite?'إزالة من المفضلة':'إضافة للمفضلة' }}</button></form>
</section>
@if($gym->images->isNotEmpty())<div class="apx-image-grid" style="margin-bottom:18px">@foreach($gym->images as $image)<article class="apx-image-card"><img src="{{ asset('storage/'.$image->image_path) }}" alt="صورة {{ $gym->gym_name }}" loading="lazy"></article>@endforeach</div>@endif
<div class="apx-detail-grid">
    <section class="apx-panel">
        <header class="apx-panel-head">
            <div>
                <h2>بيانات النادي</h2>
                <p>معلومات موثقة من صاحب النادي.</p>
            </div>
        </header>
        <div class="apx-panel-body">
            <dl class="apx-detail-list">
                <div>
                    <dt>الموقع</dt>
                    <dd>{{ collect([$gym->city,$gym->area,$gym->address])->filter()->join('، ') }}</dd>
                </div>
                <div>
                    <dt>الهاتف</dt>
                    <dd dir="ltr">{{ $gym->phone ?: 'غير مضاف' }}</dd>
                </div>
                <div>
                    <dt>التقييم</dt>
                    <dd>{{ number_format((float)($gym->reviews_avg_rating??0),1) }} / 5 ({{ $gym->reviews->count() }})</dd>
                </div>
                <div>
                    <dt>المفضلة</dt>
                    <dd>{{ $gym->favorited_by_users_count }} مستخدم</dd>
                </div>@if($gym->website_url)<div>
                    <dt>الموقع الإلكتروني</dt>
                    <dd><a href="{{ $gym->website_url }}" target="_blank" rel="noopener noreferrer">فتح الموقع</a></dd>
                </div>@endif
            </dl>
            <h3>الأنشطة</h3>
            <div class="apx-chips">@forelse($gym->activities as $item)<span class="apx-chip">{{ $item->activity_name }}</span>@empty<span class="apx-help">غير محددة</span>@endforelse</div>
            <h3>المرافق</h3>
            <div class="apx-chips">@forelse($gym->facilities as $item)<span class="apx-chip">{{ $item->facility_name }}</span>@empty<span class="apx-help">غير محددة</span>@endforelse</div>
        </div>
    </section>
    <aside>
        <section class="apx-panel">
            <header class="apx-panel-head">
                <div>
                    <h2>الاشتراكات</h2>
                </div>
            </header>
            <div class="apx-panel-body">@forelse($gym->memberships->sortBy('duration_months') as $membership)<div class="apx-detail-list">
                    <div>
                        <dt>{{ $membership->duration_months }} شهر</dt>
                        <dd>{{ number_format($membership->price_jod,2) }} د.أ</dd>
                    </div>
                </div>@empty<p class="apx-help">لا توجد أسعار مضافة.</p>@endforelse</div>
        </section>
        <section class="apx-panel">
            <header class="apx-panel-head">
                <div>
                    <h2>ساعات العمل</h2>
                </div>
            </header>
            <div class="apx-panel-body">@forelse($gym->workingHours as $hour)<div class="apx-detail-list">
                    <div>
                        <dt>{{ ['Sunday'=>'الأحد','Monday'=>'الاثنين','Tuesday'=>'الثلاثاء','Wednesday'=>'الأربعاء','Thursday'=>'الخميس','Friday'=>'الجمعة','Saturday'=>'السبت'][$hour->day_of_week]??$hour->day_of_week }}</dt>
                        <dd dir="ltr">{{ substr($hour->open_time,0,5) }} — {{ substr($hour->close_time,0,5) }}</dd>
                    </div>
                </div>@empty<p class="apx-help">لا توجد ساعات عمل مضافة.</p>@endforelse</div>
        </section>
    </aside>
</div>

<section class="apx-panel">
    <header class="apx-panel-head">
        <div>
            <h2>تقييمك</h2>
            <p>اختاري عدد النجوم واضغطي على حفظ التقييم.</p>
        </div>
    </header>

    <div class="apx-panel-body">

        <form method="POST" action="{{ route('reviews.store', $gym) }}">
            @csrf

            <div class="apx-grid-2">

                <div class="apx-field apx-span-all" style="text-align: center;">
                    <div class="gym-rating-stars" role="radiogroup" aria-label="تقييم النادي من 1 إلى 5">
                        @for($i = 1; $i <= 5; $i++) <input
                            type="radio"
                            id="rating-{{ $i }}"
                            name="rating"
                            value="{{ $i }}"
                            required
                            @checked((int) old('rating', $myReview?->rating ?? 0) === $i)
                            >

                            <label
                                for="rating-{{ $i }}"
                                title="{{ $i }} من 5"
                                aria-label="{{ $i }} نجوم">★</label>
                            @endfor
                    </div>

                    @error('rating')
                    <p class="apx-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="apx-field apx-span-all">
                    <label for="comment">تعليقك</label>
                    <textarea
                        id="comment"
                        name="comment"
                        maxlength="1500">{{ old('comment', $myReview?->comment) }}</textarea>

                    @error('comment')
                    <p class="apx-error">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="apx-form-actions">
                <button class="apx-primary" type="submit">
                    {{ $myReview ? 'تحديث التقييم' : 'حفظ التقييم' }}
                </button>
            </div>
        </form>

        @if($myReview)
        <form
            method="POST"
            action="{{ route('reviews.destroy', [$gym, $myReview]) }}"
            data-confirm="حذف تقييمك؟">
            @csrf
            @method('DELETE')

            <button class="apx-danger" type="submit">
                حذف تقييمي
            </button>
        </form>
        @endif

    </div>
</section>
<section class="apx-panel">
    <header class="apx-panel-head">
        <div>
            <h2>تقييمات المستخدمين</h2>
        </div>
    </header>
    <div class="apx-panel-body">@forelse($gym->reviews->sortByDesc('created_at') as $review)<article class="apx-review">
            <div class="apx-review-head"><strong>{{ $review->user->name }}</strong><span class="apx-stars">{{ str_repeat('★',$review->rating) }}{{ str_repeat('☆',5-$review->rating) }}</span></div>
            <p>{{ $review->comment ?: 'بدون تعليق.' }}</p>
        </article>@empty<div class="apx-empty">لا توجد تقييمات بعد.</div>@endforelse</div>
</section>
@endsection