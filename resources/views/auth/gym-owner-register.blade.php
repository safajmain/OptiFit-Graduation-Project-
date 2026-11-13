<!DOCTYPE html>
<html lang="ar" dir="rtl" data-apx-theme="volt">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0d120f">

    <script src="{{ asset('optifit-apex-assets/js/apex-theme.js') }}"></script>

    <title>تسجيل النادي | OptiFit APEX</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link href="{{ asset('optifit-apex-assets/css/apex-shared.css') }}" rel="stylesheet">
    <link href="{{ asset('optifit-apex-assets/css/apex-register.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        .owner-steps {
            display: flex;
            gap: 8px;
            margin: 22px 0
        }

        .owner-step {
            flex: 1;
            min-width: 0;
            text-align: center;
            font-size: 12px;
            opacity: .55
        }

        .owner-step b {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border: 1px solid currentColor;
            border-radius: 50%;
            margin: 0 auto 7px
        }

        .owner-step.is-current,
        .owner-step.is-done {
            opacity: 1
        }

        .owner-step.is-current b,
        .owner-step.is-done b {
            background: #18a999;
            color: white;
            border-color: #18a999
        }

        .owner-panel[hidden],
        #owner-prev[hidden],
        #owner-next[hidden],
        #owner-submit[hidden],
        #owner-map-error[hidden] {
            display: none !important
        }

        .owner-panel .apx-form-grid {
            width: 100%
        }

        .owner-controls {
            display: flex;
            gap: 12px;
            margin-top: 20px
        }

        .owner-controls button {
            flex: 1;
            justify-content: center
        }

        #owner-map {
            height: 330px;
            width: 100%;
            border-radius: 14px;
            margin: 14px 0;
            z-index: 0
        }

        .owner-coordinates {
            direction: ltr;
            text-align: center;
            margin: 10px 0;
            font-size: 13px
        }

        .owner-map-error {
            color: #f87171
        }

        @media(max-width:480px) {
            .owner-step {
                font-size: 10px
            }

            #owner-map {
                height: 280px
            }
        }
    </style>
</head>

<body class="apx-auth-page">

    @include('optifit_apex.partials.icons')

    <a class="apx-skip" href="#owner-register-form">
        انتقل إلى نموذج تسجيل النادي
    </a>

    <header class="apx-auth-top">
        <a class="apx-brand" href="{{ url('/') }}">
            <span class="apx-brand-mark">
                <svg>
                    <use href="#apx-dumbbell" />
                </svg>
            </span>
            <span>OPTI<span>FIT</span> <small>APEX</small></span>
        </a>

        <div class="apx-auth-actions">
            <button
                class="apx-icon-button"
                type="button"
                data-apx-theme-open
                aria-label="تغيير مظهر الصفحة">
                <svg>
                    <use href="#apx-palette" />
                </svg>
            </button>

            <a class="apx-secondary" href="{{ route('register') }}">
                حساب مستخدم
            </a>

            <a class="apx-primary" href="{{ route('login') }}">
                تسجيل الدخول
            </a>
        </div>
    </header>

    <main class="apx-auth-layout">

        <section class="apx-auth-art" aria-labelledby="owner-heading">
            <div>
                <span class="apx-eyebrow">GROW WITH OPTIFIT</span>

                <h1 id="owner-heading">
                    ناديك يستحق <em>مساحة أقوى.</em>
                </h1>

                <p>
                    أنشئ حساب المالك وسجّل بيانات النادي في معاملة MySQL واحدة؛
                    بعدها يظهر الطلب بحالة قيد المراجعة.
                </p>
            </div>

            <div class="apx-auth-facts">
                <div class="apx-auth-fact">
                    <svg>
                        <use href="#apx-building" />
                    </svg>
                    حساب المالك والنادي يُحفظان معًا
                </div>

                <div class="apx-auth-fact">
                    <svg>
                        <use href="#apx-shield" />
                    </svg>
                    حالة البداية: قيد المراجعة
                </div>
            </div>
        </section>

        <section class="apx-auth-form-side">
            <div class="apx-card apx-auth-card">

                <header>
                    <span class="apx-eyebrow">GYM OWNER / REGISTRATION</span>

                    <h2>أضف ناديك.</h2>

                    <p>
                        الحقول المطلوبة تُرسل مباشرة إلى Laravel،
                        بدون حفظ متصفح بديل.
                    </p>
                </header>

                @if($errors->any())
                <div class="apx-error-summary" role="alert" tabindex="-1">
                    <h2>راجع الحقول التالية:</h2>

                    <ul>
                        @foreach($errors->keys() as $field)
                        <li>
                            <a href="#{{ $field }}">
                                {{ $errors->first($field) }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="owner-steps" aria-label="خطوات تسجيل النادي">
                    <div class="owner-step is-current" data-step-indicator="0"><b>1</b><span>المالك</span></div>
                    <div class="owner-step" data-step-indicator="1"><b>2</b><span>النادي</span></div>
                    <div class="owner-step" data-step-indicator="2"><b>3</b><span>الموقع</span></div>
                    <div class="owner-step" data-step-indicator="3"><b>4</b><span>الوثائق</span></div>
                </div>

                <form
                    id="owner-register-form"
                    method="POST"
                    action="{{ route('gym-owner.register.store') }}"
                    enctype="multipart/form-data"
                    data-apx-submit
                    novalidate
                    data-initial-step="{{ $errors->hasAny(['profession_license','establishment_certificate','commercial_registration']) ? 3 : ($errors->hasAny(['latitude','longitude']) ? 2 : ($errors->hasAny(['gym_name','city','area','address','phone','website_url','description']) ? 1 : 0)) }}">
                    @csrf

                    <section class="owner-panel" data-owner-step="0" aria-label="بيانات صاحب النادي">
                        <div class="apx-form-grid">
                            <!-- بيانات صاحب النادي -->

                            <div class="apx-form-section">
                                <h3>بيانات صاحب النادي</h3>
                                <p>تُستخدم لإنشاء حساب المالك.</p>
                            </div>

                            <div class="apx-field apx-span-2 @error('name') has-error @enderror">
                                <label for="name">الاسم الكامل</label>

                                <input
                                    id="name"
                                    name="name"
                                    value="{{ old('name') }}"
                                    autocomplete="name"
                                    required
                                    autofocus>

                                @error('name')
                                <p class="apx-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="apx-field apx-span-2 @error('email') has-error @enderror">
                                <label for="email">البريد الإلكتروني</label>

                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    autocomplete="username"
                                    dir="ltr"
                                    required>

                                @error('email')
                                <p class="apx-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="apx-field @error('password') has-error @enderror">
                                <label for="password">كلمة المرور</label>

                                <div class="apx-password">
                                    <input
                                        id="password"
                                        name="password"
                                        type="password"
                                        autocomplete="new-password"
                                        required>

                                    <button
                                        class="apx-password-toggle"
                                        type="button"
                                        data-password-toggle="password"
                                        aria-label="إظهار كلمة المرور">
                                        <svg>
                                            <use href="#apx-eye" />
                                        </svg>
                                    </button>
                                </div>

                                @error('password')
                                <p class="apx-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="apx-field">
                                <label for="password_confirmation">
                                    تأكيد كلمة المرور
                                </label>

                                <div class="apx-password">
                                    <input
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        type="password"
                                        autocomplete="new-password"
                                        required>

                                    <button
                                        class="apx-password-toggle"
                                        type="button"
                                        data-password-toggle="password_confirmation"
                                        aria-label="إظهار تأكيد كلمة المرور">
                                        <svg>
                                            <use href="#apx-eye" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </section>
                    <section class="owner-panel" data-owner-step="1" aria-label="بيانات النادي" hidden>
                        <div class="apx-form-grid">
                            <!-- بيانات النادي -->

                            <div class="apx-form-section">
                                <h3>بيانات النادي</h3>
                                <p>سيُحفظ النادي بحالة قيد المراجعة.</p>
                            </div>

                            <div class="apx-field apx-span-2 @error('gym_name') has-error @enderror">
                                <label for="gym_name">اسم النادي</label>

                                <input
                                    id="gym_name"
                                    name="gym_name"
                                    value="{{ old('gym_name') }}"
                                    maxlength="255"
                                    required>

                                @error('gym_name')
                                <p class="apx-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="apx-field @error('city') has-error @enderror">
                                <label for="city">المدينة</label>

                                <input
                                    id="city"
                                    name="city"
                                    value="{{ old('city') }}"
                                    maxlength="100"
                                    required>

                                @error('city')
                                <p class="apx-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="apx-field @error('area') has-error @enderror">
                                <label for="area">المنطقة (اختياري)</label>

                                <input
                                    id="area"
                                    name="area"
                                    value="{{ old('area') }}"
                                    maxlength="100">

                                @error('area')
                                <p class="apx-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="apx-field apx-span-2 @error('address') has-error @enderror">
                                <label for="address">العنوان التفصيلي</label>

                                <input
                                    id="address"
                                    name="address"
                                    value="{{ old('address') }}"
                                    maxlength="255"
                                    required>

                                @error('address')
                                <p class="apx-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="apx-field @error('phone') has-error @enderror">
                                <label for="phone">الهاتف (اختياري)</label>

                                <input
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    value="{{ old('phone') }}"
                                    maxlength="30"
                                    dir="ltr">

                                @error('phone')
                                <p class="apx-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="apx-field @error('website_url') has-error @enderror">
                                <label for="website_url">الموقع الإلكتروني (اختياري)</label>

                                <input
                                    id="website_url"
                                    name="website_url"
                                    type="url"
                                    value="{{ old('website_url') }}"
                                    maxlength="255"
                                    dir="ltr">

                                @error('website_url')
                                <p class="apx-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="apx-field apx-span-2 @error('description') has-error @enderror">
                                <label for="description">وصف النادي (اختياري)</label>

                                <textarea
                                    id="description"
                                    name="description"
                                    maxlength="2000">{{ old('description') }}</textarea>

                                @error('description')
                                <p class="apx-error">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                    </section>
                    <section class="owner-panel" data-owner-step="2" aria-label="موقع النادي" hidden>
                        <div class="apx-form-section">
                            <h3>موقع النادي على الخريطة</h3>
                            <p>اضغطي على الخريطة أو حرّكي الدبوس لتحديد موقع النادي.</p>
                        </div>
                        <div id="owner-map" aria-label="خريطة اختيار موقع النادي"></div>
                        <button class="apx-secondary" type="button" id="owner-use-location">استخدمي موقعي الحالي</button>
                        <p id="owner-coordinates" class="owner-coordinates" aria-live="polite">لم يتم تحديد الموقع بعد</p>
                        <p id="owner-map-error" class="owner-map-error" role="alert" hidden></p>
                        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                        @error('latitude') <p class="apx-error">{{ $message }}</p> @enderror
                        @error('longitude') <p class="apx-error">{{ $message }}</p> @enderror
                    </section>
                    <section class="owner-panel" data-owner-step="3" aria-label="الوثائق الرسمية" hidden>
                        <div class="apx-form-grid">
                            <!-- وثائق التحقق الرسمية -->

                            <div class="apx-form-section">
                                <h3>وثائق التحقق الرسمية</h3>

                                <p>
                                    يجب رفع الوثائق الثلاثة لإثبات تسجيل النادي رسميًا.
                                    ستراجعها الإدارة قبل اعتماد النادي.
                                </p>
                            </div>

                            <div class="apx-field apx-span-2 @error('profession_license') has-error @enderror">
                                <label for="profession_license">
                                    رخصة المهن (إلزامي)
                                </label>

                                <input
                                    id="profession_license"
                                    name="profession_license"
                                    type="file"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    required>

                                @error('profession_license')
                                <p class="apx-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="apx-field apx-span-2 @error('establishment_certificate') has-error @enderror">
                                <label for="establishment_certificate">
                                    شهادة تسجيل المنشأة (إلزامي)
                                </label>

                                <input
                                    id="establishment_certificate"
                                    name="establishment_certificate"
                                    type="file"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    required>

                                @error('establishment_certificate')
                                <p class="apx-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="apx-field apx-span-2 @error('commercial_registration') has-error @enderror">
                                <label for="commercial_registration">
                                    السجل التجاري (إلزامي)
                                </label>

                                <input
                                    id="commercial_registration"
                                    name="commercial_registration"
                                    type="file"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    required>

                                @error('commercial_registration')
                                <p class="apx-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="apx-field apx-span-2">
                                <p>
                                    الصيغ المسموحة: PDF وJPG وPNG.
                                    الحد الأقصى: 5MB لكل وثيقة.
                                </p>
                            </div>

                        </div>
                    </section>
                    <div class="owner-controls">
                        <button class="apx-secondary" type="button" id="owner-prev" hidden>السابق</button>
                        <button class="apx-primary" type="button" id="owner-next">التالي</button>
                    </div>
                    <button class="apx-primary apx-submit" id="owner-submit" type="submit" hidden>
                        <span data-submit-label>
                            إنشاء الحساب وإرسال النادي
                        </span>

                        <svg>
                            <use href="#apx-arrow" />
                        </svg>
                    </button>

                    <p class="apx-owner-note">
                        <strong>مهم:</strong>
                        الإرسال لا يعني اعتماد النادي؛
                        يبقى طلبه قيد المراجعة وفق الصلاحيات الحالية.
                    </p>

                </form>

                <p class="apx-switch">
                    تريد حساب مستخدم؟
                    <a href="{{ route('register') }}">سجّل من هنا</a>
                    · لديك حساب؟
                    <a href="{{ route('login') }}">تسجيل الدخول</a>
                </p>

            </div>
        </section>

    </main>

    @include('optifit_apex.partials.theme-studio')

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('owner-register-form');
            const panels = Array.from(form.querySelectorAll('[data-owner-step]'));
            const indicators = Array.from(document.querySelectorAll('[data-step-indicator]'));
            const next = document.getElementById('owner-next');
            const prev = document.getElementById('owner-prev');
            const submit = document.getElementById('owner-submit');
            const lat = document.getElementById('latitude');
            const lng = document.getElementById('longitude');
            const coords = document.getElementById('owner-coordinates');
            const error = document.getElementById('owner-map-error');
            let step = 0,
                map = null,
                marker = null;

            function setPin(latitude, longitude) {
                lat.value = latitude.toFixed(7);
                lng.value = longitude.toFixed(7);
                coords.textContent = 'Latitude: ' + lat.value + ' | Longitude: ' + lng.value;
                error.hidden = true;
                if (marker) marker.setLatLng([latitude, longitude]);
                else marker = L.marker([latitude, longitude], {
                    draggable: true
                }).addTo(map).on('dragend', function(event) {
                    const point = event.target.getLatLng();
                    setPin(point.lat, point.lng);
                });
            }

            function initMap() {
                if (map) {
                    setTimeout(function() {
                        map.invalidateSize();
                    }, 100);
                    return;
                }
                if (typeof L === 'undefined') {
                    error.textContent = 'تعذر تحميل الخريطة. تحققي من اتصال الإنترنت.';
                    error.hidden = false;
                    return;
                }
                const saved = lat.value !== '' && lng.value !== '' && Number.isFinite(Number(lat.value)) && Number.isFinite(Number(lng.value));
                const center = saved ? [Number(lat.value), Number(lng.value)] : [31.9539, 35.9106];
                map = L.map('owner-map').setView(center, saved ? 15 : 11);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);
                map.on('click', function(event) {
                    setPin(event.latlng.lat, event.latlng.lng);
                });
                if (saved) setPin(center[0], center[1]);
                setTimeout(function() {
                    map.invalidateSize();
                }, 150);
            }

            function showStep(index) {
                step = index;
                panels.forEach(function(panel, i) {
                    panel.hidden = i !== index;
                });
                indicators.forEach(function(indicator, i) {
                    indicator.classList.toggle('is-current', i === index);
                    indicator.classList.toggle('is-done', i < index);
                    if (i === index) indicator.setAttribute('aria-current', 'step');
                    else indicator.removeAttribute('aria-current');
                });
                prev.hidden = index === 0;
                next.hidden = index === 3;
                submit.hidden = index !== 3;
                if (index === 2) initMap();
            }

            function validateStep(index) {
                if (index === 2) {
                    if (!lat.value || !lng.value || !Number.isFinite(Number(lat.value)) || !Number.isFinite(Number(lng.value)) || Math.abs(Number(lat.value)) > 90 || Math.abs(Number(lng.value)) > 180) {
                        error.textContent = 'حددي موقع النادي على الخريطة أولًا.';
                        error.hidden = false;
                        return false;
                    }
                    return true;
                }
                for (const field of panels[index].querySelectorAll('input, textarea, select')) {
                    field.setCustomValidity('');
                    if (!field.checkValidity()) {
                        field.reportValidity();
                        field.focus();
                        return false;
                    }
                    if (field.type === 'file' && field.files.length) {
                        const file = field.files[0];
                        const ext = file.name.split('.').pop().toLowerCase();
                        if (file.size > 5 * 1024 * 1024 || !['pdf', 'jpg', 'jpeg', 'png'].includes(ext)) {
                            field.setCustomValidity('الملف يجب أن يكون PDF أو JPG أو PNG ولا يتجاوز 5MB.');
                            field.reportValidity();
                            field.setCustomValidity('');
                            field.focus();
                            return false;
                        }
                    }
                }
                if (index === 0) {
                    const password = document.getElementById('password');
                    const confirmation = document.getElementById('password_confirmation');
                    if (password.value !== confirmation.value) {
                        confirmation.setCustomValidity('تأكيد كلمة المرور غير مطابق.');
                        confirmation.reportValidity();
                        confirmation.setCustomValidity('');
                        confirmation.focus();
                        return false;
                    }
                }
                return true;
            }
            next.addEventListener('click', function() {
                if (validateStep(step)) showStep(step + 1);
            });
            prev.addEventListener('click', function() {
                showStep(step - 1);
            });
            document.getElementById('owner-use-location').addEventListener('click', function() {
                if (!navigator.geolocation) {
                    error.textContent = 'المتصفح لا يدعم تحديد الموقع.';
                    error.hidden = false;
                    return;
                }
                navigator.geolocation.getCurrentPosition(function(position) {
                    initMap();
                    if (!map) return;
                    map.setView([position.coords.latitude, position.coords.longitude], 16);
                    setPin(position.coords.latitude, position.coords.longitude);
                }, function() {
                    error.textContent = 'تعذر تحديد موقعك، اختاريه يدويًا من الخريطة.';
                    error.hidden = false;
                });
            });
            form.addEventListener('submit', function(event) {
                for (let i = 0; i < panels.length; i++) {
                    showStep(i);
                    if (!validateStep(i)) {
                        event.preventDefault();
                        event.stopImmediatePropagation();
                        return;
                    }
                }
                showStep(3);
            }, true);
            document.querySelectorAll('.apx-error-summary a[href^="#"]').forEach(function(link) {
                link.addEventListener('click', function(event) {
                    const target = document.getElementById(link.getAttribute('href').slice(1));
                    if (!target) return;
                    const panel = target.closest('[data-owner-step]');
                    if (!panel) return;
                    event.preventDefault();
                    showStep(panels.indexOf(panel));
                    target.focus();
                    target.scrollIntoView({
                        block: 'center'
                    });
                });
            });
            showStep(Number(form.dataset.initialStep) || 0);
        });
    </script>

    <script
        defer
        src="{{ asset('optifit-apex-assets/js/apex-shared.js') }}"></script>

</body>

</html>