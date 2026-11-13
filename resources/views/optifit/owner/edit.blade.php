@extends('layouts.optifit-apex')

@section('title', 'بروفايل صاحب النادي | OptiFit APEX')
@section('eyebrow', 'GYM OWNER / ACCOUNT')
@section('page-heading', 'ملفي الشخصي')

@push('head')
<style>
    .owner-steps {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
    }

    .owner-step {
        flex: 1;
        padding: 14px 10px;
        border: 1px solid #39433b;
        border-radius: 14px;
        background: transparent;
        color: inherit;
        font: inherit;
        cursor: pointer;
        text-align: center;
    }

    .owner-step.is-active {
        background: #c6ff43;
        color: #14200a;
        border-color: #c6ff43;
        font-weight: 800;
    }

    .owner-step-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: 1px solid currentColor;
        margin-left: 8px;
    }

    .owner-step-panel[hidden] {
        display: none !important;
    }

    .owner-step-actions {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    @media (max-width: 600px) {
        .owner-steps {
            gap: 6px;
        }

        .owner-step {
            font-size: 12px;
            padding: 10px 5px;
        }

        .owner-step-number {
            margin-left: 0;
            margin-bottom: 5px;
        }
    }
</style>
@endpush

@section('content')

<section class="apx-page-hero">
    <div>
        <span class="apx-eyebrow">OWNER ACCOUNT</span>
        <h1>حسابك، <em>بأمان.</em></h1>
        <p>إدارة بيانات صاحب النادي ورقم التواصل المرتبط بالنادي.</p>
    </div>
</section>

@if(session('status') === 'profile-updated')
<p class="apx-help" role="status">
    تم حفظ التعديلات بنجاح.
</p>
@endif

{{-- تحديد الخطوة الأولى عند فتح الصفحة --}}
<div
    data-owner-initial-step="{{ $errors->updatePassword->any() || session('status') === 'password-updated' ? 3 : 1 }}"
    hidden></div>

{{-- أزرار الخطوات --}}
<div class="owner-steps" role="group" aria-label="خطوات الملف الشخصي">

    <button
        type="button"
        class="owner-step is-active"
        data-owner-step="1"
        aria-current="step">
        <span class="owner-step-number">1</span>
        معلومات الحساب
    </button>

    <button
        type="button"
        class="owner-step"
        data-owner-step="2">
        <span class="owner-step-number">2</span>
        بيانات النادي
    </button>

    <button
        type="button"
        class="owner-step"
        data-owner-step="3">
        <span class="owner-step-number">3</span>
        كلمة المرور
    </button>

</div>

{{-- =====================================
    الخطوة الأولى: معلومات صاحب النادي
===================================== --}}

<section
    class="apx-panel owner-step-panel"
    data-owner-panel="1">
    <header class="apx-panel-head">
        <div>
            <h2>معلومات صاحب النادي</h2>
            <p>الاسم والبريد ورقم الهاتف.</p>
        </div>
    </header>

    <div class="apx-panel-body">

        <form
            method="POST"
            action="{{ route('owner.profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="apx-grid-2">

                <div class="apx-field apx-span-all @error('name') has-error @enderror">
                    <label for="name">الاسم الكامل</label>

                    <input
                        id="name"
                        name="name"
                        type="text"
                        required
                        maxlength="255"
                        autocomplete="name"
                        value="{{ old('name', $user->name) }}">

                    @error('name')
                    <p class="apx-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="apx-field apx-span-all @error('email') has-error @enderror">
                    <label for="email">البريد الإلكتروني</label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        maxlength="255"
                        autocomplete="username"
                        value="{{ old('email', $user->email) }}">

                    @error('email')
                    <p class="apx-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="apx-field apx-span-all @error('phone') has-error @enderror">
                    <label for="phone">رقم الهاتف</label>

                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        maxlength="30"
                        autocomplete="tel"
                        value="{{ old('phone', $gym?->phone ?? '') }}"
                        @disabled(!$gym)>

                    @error('phone')
                    <p class="apx-error">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            @unless($gym)
            <p class="apx-help">
                لا يوجد نادي مرتبط بحسابك حاليًا.
            </p>
            @endunless

            <div class="apx-form-actions">
                <button class="apx-primary" type="submit">
                    حفظ معلومات الحساب
                </button>
            </div>

        </form>

        @if(
        $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail
        && ! $user->hasVerifiedEmail()
        )
        <p class="apx-help">
            البريد الإلكتروني غير موثّق.
        </p>

        <form
            method="POST"
            action="{{ route('verification.send') }}">
            @csrf

            <button class="apx-secondary" type="submit">
                إعادة إرسال رابط التوثيق
            </button>
        </form>
        @endif

        <div class="owner-step-actions">
            <span></span>

            <button
                type="button"
                class="apx-secondary"
                data-owner-next="2">
                التالي ←
            </button>
        </div>

    </div>
</section>

{{-- =====================================
    الخطوة الثانية: معلومات النادي
===================================== --}}

<section
    class="apx-panel owner-step-panel"
    data-owner-panel="2"
    hidden>
    <header class="apx-panel-head">
        <div>
            <h2>النادي المرتبط بالحساب</h2>
            <p>بيانات النادي المسجلة في النظام.</p>
        </div>
    </header>

    <div class="apx-panel-body">

        @if($gym)

        <div class="apx-grid-2">

            <div class="apx-field">
                <label>اسم النادي</label>
                <p>{{ $gym->gym_name }}</p>
            </div>

            <div class="apx-field">
                <label>المدينة</label>
                <p>{{ $gym->city }}</p>
            </div>

            <div class="apx-field">
                <label>المنطقة</label>
                <p>{{ $gym->area ?: 'غير محددة' }}</p>
            </div>

            <div class="apx-field">
                <label>حالة اعتماد النادي</label>

                <p>
                    {{ match ($gym->verification_status) {
                            'pending' => 'قيد المراجعة',
                            'approved' => 'معتمد',
                            'rejected' => 'مرفوض',
                            default => $gym->verification_status
                        } }}
                </p>
            </div>

        </div>

        @else

        <p class="apx-help">
            ما في نادي مرتبط بهذا الحساب حاليًا.
        </p>

        @endif

        <div class="owner-step-actions">

            <button
                type="button"
                class="apx-secondary"
                data-owner-next="1">
                السابق →
            </button>

            <button
                type="button"
                class="apx-primary"
                data-owner-next="3">
                التالي ←
            </button>

        </div>

    </div>
</section>

{{-- =====================================
    الخطوة الثالثة: تغيير كلمة المرور
===================================== --}}

<section
    class="apx-panel owner-step-panel"
    data-owner-panel="3"
    hidden>
    <header class="apx-panel-head">
        <div>
            <h2>تغيير كلمة المرور</h2>
            <p>تحديث كلمة المرور لحماية حسابك.</p>
        </div>
    </header>

    <form
        class="apx-panel-body"
        method="POST"
        action="{{ route('password.update') }}">
        @csrf
        @method('PUT')

        <div class="apx-grid-2">

            <div class="apx-field apx-span-all">
                <label for="update_password_current_password">
                    كلمة المرور الحالية
                </label>

                <input
                    id="update_password_current_password"
                    name="current_password"
                    type="password"
                    autocomplete="current-password"
                    required>

                @if($errors->updatePassword->has('current_password'))
                <p class="apx-error">
                    {{ $errors->updatePassword->first('current_password') }}
                </p>
                @endif
            </div>

            <div class="apx-field">
                <label for="update_password_password">
                    كلمة المرور الجديدة
                </label>

                <input
                    id="update_password_password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    required>

                @if($errors->updatePassword->has('password'))
                <p class="apx-error">
                    {{ $errors->updatePassword->first('password') }}
                </p>
                @endif
            </div>

            <div class="apx-field">
                <label for="update_password_password_confirmation">
                    تأكيد كلمة المرور
                </label>

                <input
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    required>

                @if($errors->updatePassword->has('password_confirmation'))
                <p class="apx-error">
                    {{ $errors->updatePassword->first('password_confirmation') }}
                </p>
                @endif
            </div>

        </div>

        @if(session('status') === 'password-updated')
        <p class="apx-help" role="status">
            تم تغيير كلمة المرور بنجاح.
        </p>
        @endif

        <div class="owner-step-actions">

            <button
                type="button"
                class="apx-secondary"
                data-owner-next="2">
                السابق →
            </button>

            <button class="apx-primary" type="submit">
                تحديث كلمة المرور
            </button>

        </div>

    </form>
</section>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const buttons = document.querySelectorAll('[data-owner-step]');
        const panels = document.querySelectorAll('[data-owner-panel]');

        function showStep(step) {

            buttons.forEach(function(button) {
                const active =
                    button.dataset.ownerStep === String(step);

                button.classList.toggle('is-active', active);

                if (active) {
                    button.setAttribute('aria-current', 'step');
                } else {
                    button.removeAttribute('aria-current');
                }
            });

            panels.forEach(function(panel) {
                panel.hidden =
                    panel.dataset.ownerPanel !== String(step);
            });
        }

        buttons.forEach(function(button) {
            button.addEventListener('click', function() {
                showStep(button.dataset.ownerStep);
            });
        });

        document.querySelectorAll('[data-owner-next]').forEach(function(button) {
            button.addEventListener('click', function() {
                showStep(button.dataset.ownerNext);
            });
        });

        const initialStepElement =
            document.querySelector('[data-owner-initial-step]');

        const initialStep =
            initialStepElement ?
            initialStepElement.dataset.ownerInitialStep :
            '1';

        showStep(initialStep);

    });
</script>
@endpush