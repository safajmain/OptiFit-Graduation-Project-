@include('optifit_apex.partials.onboarding-top', [
'title' => 'المعلومات الشخصية', 'step' => 1,
'heading' => 'ابدأ من بياناتك.',
'description' => 'نستخدم موقعك وهدفك وميزانيتك لعرض أندية أقرب إلى احتياجاتك. يمكنك العودة وتعديل هذه المعلومات في أي وقت.',
])

<section class="apx-form-card">
    <header class="apx-form-card-header">
        <div>
            <span class="apx-eyebrow">PERSONAL PROFILE</span>
            <h2>المعلومات الأساسية</h2>
            <p>جميع الحقول مطلوبة وتُحفظ في ملفك داخل قاعدة البيانات.</p>
        </div>
        <span class="apx-count">1 / 4</span>
    </header>

    <form class="apx-form-card-body"
        method="POST"
        action="{{ route('user.personal.store') }}"
        data-apx-submit>
        @csrf

        <div class="apx-form-grid">

            <div class="apx-field apx-span-2 @error('location') has-error @enderror">
                <label for="location">المنطقة أو الموقع</label>
                <input id="location" name="location" type="text"
                    maxlength="255" required
                    autocomplete="address-level2"
                    placeholder="مثال: عمّان، الجبيهة"
                    value="{{ old('location', $preference?->location) }}">
                @error('location')
                <p class="apx-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="apx-field apx-span-2">
                <button type="button" id="openLocationMap"
                    style="background:#c7ff45; color:#202a1d;
               border:0; border-radius:12px;
               padding:12px 18px; font-weight:bold;
               cursor:pointer;">
                    📍 تحديد الموقع من الخريطة
                </button>
            </div>


            <div class="apx-field @error('gender') has-error @enderror">
                <label for="gender">الجنس</label>
                <select id="gender" name="gender" required>
                    <option value="">اختر</option>
                    <option value="male" @selected(old('gender', $preference?->gender) === 'male')>ذكر</option>
                    <option value="female" @selected(old('gender', $preference?->gender) === 'female')>أنثى</option>
                </select>
                @error('gender')
                <p class="apx-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="apx-field @error('fitnessGoal') has-error @enderror">
                <label for="fitnessGoal">الهدف الرياضي</label>
                <select id="fitnessGoal" name="fitnessGoal" required>
                    <option value="">اختر هدفك</option>
                    <option value="bulk" @selected(old('fitnessGoal', $preference?->fitness_goal) === 'bulk')>زيادة الكتلة العضلية</option>
                    <option value="cut" @selected(old('fitnessGoal', $preference?->fitness_goal) === 'cut')>خفض الدهون</option>
                    <option value="fitness" @selected(old('fitnessGoal', $preference?->fitness_goal) === 'fitness')>لياقة عامة</option>
                </select>
                @error('fitnessGoal')
                <p class="apx-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="apx-field @error('height') has-error @enderror">
                <label for="height">الطول (سم)</label>
                <input id="height" name="height" type="number"
                    min="50" max="260" step="0.1"
                    required inputmode="decimal"
                    value="{{ old('height', $preference?->height_cm) }}">
                @error('height')
                <p class="apx-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="apx-field @error('weight') has-error @enderror">
                <label for="weight">الوزن (كغ)</label>
                <input id="weight" name="weight" type="number"
                    min="15" max="450" step="0.1"
                    required inputmode="decimal"
                    value="{{ old('weight', $preference?->weight_kg) }}">
                @error('weight')
                <p class="apx-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- BMI CARD -->
            <div class="apx-field apx-span-2">
                <label>مؤشر كتلة الجسم (BMI)</label>

                <div class="bmi-card" aria-live="polite">

                    <p class="bmi-heading">مؤشر كتلة الجسم (BMI)</p>

                    <div id="bmi-number" class="bmi-number">--</div>

                    <div id="bmi-category" class="bmi-category">
                        أدخل الطول والوزن
                    </div>

                    <div class="bmi-scale" aria-hidden="true">
                        <span class="bmi-underweight"></span>
                        <span class="bmi-normal"></span>
                        <span class="bmi-overweight"></span>
                        <span class="bmi-obese"></span>
                    </div>

                    <div class="bmi-labels">
                        <span>نحافة</span>
                        <span>طبيعي</span>
                        <span>زيادة وزن</span>
                        <span>سمنة</span>
                    </div>

                    <p class="bmi-note">
                        مؤشر تقريبي للبالغين وليس تشخيصًا طبيًا
                    </p>
                </div>
            </div>

            <div class="apx-field apx-span-2 @error('maxBudget') has-error @enderror">
                <label for="maxBudget">الميزانية الشهرية القصوى (دينار)</label>

                <div style="background:#202a1d; border:1px solid #3b4938; border-radius:16px; padding:20px;">
                    <div style="text-align:center; margin-bottom:15px; color:#c7ff45; font-size:26px; font-weight:bold;">
                        <span id="budgetValue">{{ old('maxBudget', $preference?->max_budget ?? 60) }}</span> د.أ
                    </div>

                    <input id="maxBudget" name="maxBudget" type="range"
                        min="0" max="300" step="5" required
                        style="width:100%; accent-color:#c7ff45; cursor:pointer;"
                        value="{{ old('maxBudget', $preference?->max_budget ?? 60) }}"
                        oninput="document.getElementById('budgetValue').textContent = this.value">

                    <div style="display:flex; justify-content:space-between; color:#b5c1b0; font-size:12px; margin-top:10px;">
                        <span>0 د.أ</span>
                        <span>300 د.أ</span>
                    </div>
                </div>

                @error('maxBudget')
                <p class="apx-error">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <div class="apx-form-actions">
            <a class="apx-secondary" href="{{ route('dashboard') }}">
                العودة للرئيسية
            </a>
            <button class="apx-primary" type="submit">
                <span data-submit-label>حفظ والمتابعة</span>
                <svg>
                    <use href="#apx-arrow" />
                </svg>
            </button>
        </div>
    </form>
</section>

<style>
    .bmi-card {
        background: #202a1d;
        border: 1px solid #3b4938;
        border-radius: 18px;
        padding: 24px;
        text-align: center;
        direction: rtl;
    }

    .bmi-heading {
        color: #b5c1b0;
        font-size: 14px;
        margin: 0 0 10px;
    }

    .bmi-number {
        font-size: 46px;
        font-weight: 800;
        line-height: 1.3;
        color: #c7ff45;
    }

    .bmi-category {
        display: inline-block;
        margin-top: 8px;
        padding: 7px 16px;
        border-radius: 25px;
        background: rgba(199, 255, 69, 0.1);
        color: #c7ff45;
        font-size: 15px;
        font-weight: 700;
    }

    .bmi-scale {
        display: flex;
        direction: ltr;
        gap: 4px;
        margin-top: 25px;
        height: 12px;
        border-radius: 20px;
        overflow: hidden;
    }

    .bmi-scale span {
        flex: 1;
        transition: opacity 0.2s ease;
    }

    .bmi-underweight {
        background: #60a5fa;
    }

    .bmi-normal {
        background: #a3e635;
    }

    .bmi-overweight {
        background: #facc15;
    }

    .bmi-obese {
        background: #fb7185;
    }

    .bmi-labels {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        direction: ltr;
        gap: 4px;
        margin-top: 10px;
        color: #b5c1b0;
        font-size: 11px;
        text-align: center;
    }

    .bmi-labels span {
        direction: rtl;
    }

    .bmi-note {
        color: #899687;
        font-size: 11px;
        margin: 18px 0 0;
    }

    @media (max-width: 480px) {
        .bmi-card {
            padding: 16px 12px;
        }

        .bmi-number {
            font-size: 38px;
        }

        .bmi-labels {
            font-size: 10px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const heightInput = document.getElementById('height');
        const weightInput = document.getElementById('weight');
        const bmiNumber = document.getElementById('bmi-number');
        const bmiCategory = document.getElementById('bmi-category');

        function calculateBMI() {
            const height = Number(heightInput.value);
            const weight = Number(weightInput.value);

            if (
                heightInput.value.trim() === '' ||
                weightInput.value.trim() === '' ||
                !Number.isFinite(height) ||
                !Number.isFinite(weight) ||
                height < 50 || height > 260 ||
                weight < 15 || weight > 450
            ) {
                bmiNumber.textContent = '--';
                bmiNumber.style.color = '#c7ff45';
                bmiCategory.textContent = 'أدخل الطول والوزن';
                bmiCategory.style.color = '#c7ff45';
                bmiCategory.style.background = 'rgba(199,255,69,0.1)';
                return;
            }

            const bmi = weight / Math.pow(height / 100, 2);

            let category;
            let color;

            if (bmi < 18.5) {
                category = 'نحافة';
                color = '#60a5fa';
            } else if (bmi < 25) {
                category = 'وزن طبيعي';
                color = '#a3e635';
            } else if (bmi < 30) {
                category = 'زيادة وزن';
                color = '#facc15';
            } else {
                category = 'سمنة';
                color = '#fb7185';
            }

            bmiNumber.textContent = bmi.toFixed(1);
            bmiNumber.style.color = color;

            bmiCategory.textContent = category;
            bmiCategory.style.color = color;
            bmiCategory.style.background = color + '20';
        }

        heightInput.addEventListener('input', calculateBMI);
        weightInput.addEventListener('input', calculateBMI);

        calculateBMI();
    });
</script>

<!-- Location Map Modal -->
<div id="locationMapModal"
    style="display:none; position:fixed; inset:0;
            background:rgba(0,0,0,.75); z-index:99999;
            padding:20px; align-items:center;
            justify-content:center;">

    <div style="background:#202a1d; padding:20px;
                border-radius:18px; width:100%;
                max-width:650px;">

        <div style="display:flex; justify-content:space-between;
                    align-items:center; margin-bottom:15px;">
            <h3 style="color:white; margin:0;">
                📍 اختر موقعك
            </h3>

            <button type="button" id="closeLocationMap"
                style="background:transparent; border:0;
                       color:white; font-size:28px;
                       cursor:pointer;">
                ×
            </button>
        </div>

        <div id="locationPickerMap"
            style="height:350px; width:100%;
                    border-radius:12px; overflow:hidden;">
        </div>

        <p id="selectedLocationText"
            style="color:#b5c1b0; font-size:13px;">
            اضغط على المكان المطلوب داخل الخريطة
        </p>

        <button type="button" id="confirmLocation"
            disabled
            style="width:100%; background:#c7ff45;
                       color:#202a1d; border:0;
                       padding:13px; border-radius:12px;
                       font-weight:bold; cursor:pointer;">
            تأكيد الموقع
        </button>
    </div>
</div>


<link rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const openButton = document.getElementById('openLocationMap');
        const closeButton = document.getElementById('closeLocationMap');
        const confirmButton = document.getElementById('confirmLocation');
        const modal = document.getElementById('locationMapModal');
        const locationInput = document.getElementById('location');
        const selectedText = document.getElementById('selectedLocationText');

        let map = null;
        let marker = null;
        let selectedCoordinates = null;

        openButton.addEventListener('click', function() {
            modal.style.display = 'flex';

            if (typeof L === 'undefined') {
                selectedText.textContent = 'تعذّر تحميل الخريطة. تأكد من اتصال الإنترنت.';
                return;
            }

            if (!map) {
                map = L.map('locationPickerMap').setView([31.95, 35.93], 11);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                map.on('click', function(event) {
                    selectedCoordinates = event.latlng;

                    if (marker) {
                        marker.setLatLng(event.latlng);
                    } else {
                        marker = L.marker(event.latlng).addTo(map);
                    }

                    selectedText.textContent =
                        'الموقع المحدد: ' +
                        event.latlng.lat.toFixed(6) + ', ' +
                        event.latlng.lng.toFixed(6);

                    confirmButton.disabled = false;
                });
            }

            setTimeout(function() {
                map.invalidateSize();
            }, 100);
        });

        function closeMap() {
            modal.style.display = 'none';
        }

        closeButton.addEventListener('click', closeMap);

        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeMap();
            }
        });

        confirmButton.addEventListener('click', async function() {
            if (!selectedCoordinates) return;

            const lat = selectedCoordinates.lat;
            const lng = selectedCoordinates.lng;

            const coordinates = lat.toFixed(6) + ', ' + lng.toFixed(6);

            confirmButton.disabled = true;
            selectedText.textContent = 'جاري تحديد اسم المنطقة...';

            try {
                const url = new URL(
                    'https://nominatim.openstreetmap.org/reverse'
                );

                url.searchParams.set('format', 'json');
                url.searchParams.set('lat', lat);
                url.searchParams.set('lon', lng);
                url.searchParams.set('accept-language', 'ar');

                const response = await fetch(url.toString());

                if (!response.ok) {
                    throw new Error('Address lookup failed');
                }

                const data = await response.json();

                locationInput.value = data.display_name || coordinates;
            } catch (error) {
                locationInput.value = coordinates;
            }

            locationInput.dispatchEvent(
                new Event('input', {
                    bubbles: true
                })
            );

            confirmButton.disabled = false;
            closeMap();
        });
    });
</script>

@include('optifit_apex.partials.onboarding-bottom')