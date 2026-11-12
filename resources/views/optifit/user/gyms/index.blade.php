@extends('layouts.optifit-user')

@section('title', 'استكشف النوادي | OptiFit APEX')
@section('eyebrow', 'USER / DISCOVER')
@section('page-heading', 'استكشف النوادي')

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">

<style>
    .discover-map {
        position: relative;
        z-index: 1;
        width: 100%;
        height: 420px;
        border-radius: 18px;
        overflow: hidden;
        background: #162d40;
    }

    .discover-map.leaflet-container {
        cursor: grab;
    }

    .discover-map.leaflet-dragging {
        cursor: grabbing;
    }

    .discover-actions,
    .discover-location-controls,
    .discover-compare {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin: 15px 0;
    }

    .discover-location-controls select {
        min-width: 180px;
        padding: 10px;
        border-radius: 10px;
        background: #162d40;
        color: white;
        border: 1px solid #477083;
    }

    .discover-status {
        margin: 12px 0;
        color: #a9c4d1;
    }

    .discover-results-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin: 22px 0 14px;
    }

    .discover-choice {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
        cursor: pointer;
    }

    .discover-choice input {
        width: 17px;
        height: 17px;
        accent-color: #5ed7f5;
    }

    .discover-hidden {
        display: none !important;
    }

    .discover-compare button:disabled,
    .discover-actions button:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    /* اختبار تفاعل الخريطة */
    #discover-map {
        pointer-events: auto !important;
        touch-action: auto !important;
    }

    #discover-map .leaflet-map-pane {
        pointer-events: auto !important;
    }

    #discover-map .leaflet-tile-pane {
        pointer-events: none !important;
    }
</style>
@endpush

@section('content')

<section class="apx-page-hero">
    <div>
        <span class="apx-eyebrow">DISCOVER / MAP / COMPARE</span>
        <h1>استكشف ناديك <em>المناسب.</em></h1>
        <p>
            ابحث حسب تفضيلاتك، أو اختر موقعًا على الخريطة
            لتشاهد النوادي القريبة منه.
        </p>
    </div>

    <a class="apx-secondary" href="{{ route('gyms.compare') }}">
        مقارنة النوادي
    </a>
</section>



<section class="apx-panel" style="margin-top:20px;padding:18px;">

    <h2>اختر منطقة على الخريطة</h2>

    <p>
        اسحب الخريطة أو اضغط على أي مكان لاختيار موقع.
        بعدها اضغط «عرض النوادي حول الموقع».
        نطاق البحث 5 كم.
    </p>

    <div class="discover-location-controls">
        <label for="discover-area-select">
            الانتقال لمنطقة:
        </label>

        <select id="discover-area-select">
            <option value="">اختاري منطقة</option>
            <option value="31.9539,35.9106">وسط عمّان</option>
            <option value="31.872,36.006">سحاب</option>
            <option value="32.014,35.826">دابوق</option>
            <option value="32.0728,36.087">الزرقاء</option>
            <option value="32.5569,35.8479">إربد</option>
        </select>
    </div>

    <div class="discover-actions">
        <button
            id="discover-locate"
            type="button"
            class="apx-secondary">
            📍 تحديد موقعي
        </button>

        <button
            id="discover-pick-center"
            type="button"
            class="apx-secondary">
            اختيار مركز الخريطة
        </button>

        <button
            id="discover-search-area"
            type="button"
            class="apx-primary"
            disabled>
            عرض النوادي حول الموقع
        </button>

        <button
            id="discover-recommendations"
            type="button"
            class="apx-secondary"
            disabled>
            ✨ توصيات لهذه المنطقة
        </button>

        <button
            id="discover-show-all"
            type="button"
            class="apx-secondary">
            عرض كل النتائج
        </button>
    </div>

    <div
        id="discover-map"
        class="discover-map"
        role="region"
        aria-label="خريطة استكشاف النوادي"
        data-map-gyms='@json($mapGyms ?? [])'></div>

    <p
        id="discover-map-status"
        class="discover-status"
        role="status"
        aria-live="polite">
        جاري تحميل الخريطة...
    </p>

</section>

<div class="discover-results-head" id="discover-results">
    <h2>النوادي المتاحة</h2>
    <strong id="discover-result-count">
        {{ $gyms->total() }} نتيجة
    </strong>
</div>

<p id="discover-area-note" class="discover-status" hidden></p>

@if($gyms->isEmpty())

<section class="apx-panel apx-empty">
    <h2>لا توجد نتائج مطابقة</h2>
    <p>جرّب تغيير الفلاتر أو السعر.</p>
</section>

@else

<div class="apx-cards" id="discover-gym-cards">

    @foreach($gyms as $gym)

    <article
        class="apx-gym-tile discover-gym-card"
        data-gym-id="{{ $gym->gym_id }}"
        data-lat="{{ $gym->latitude }}"
        data-lng="{{ $gym->longitude }}">

        <div class="apx-tile-media">

            @if($gym->images->first())
            <img
                src="{{ asset('storage/' . $gym->images->first()->image_path) }}"
                alt="صورة {{ $gym->gym_name }}"
                loading="lazy">
            @else
            <div class="apx-tile-placeholder">
                <svg>
                    <use href="#apx-dumbbell" />
                </svg>
            </div>
            @endif

        </div>

        <div class="apx-tile-body">

            <div class="apx-tile-meta">
                <span>
                    {{ $gym->city }}
                    @if($gym->area)
                    · {{ $gym->area }}
                    @endif
                </span>

                <span>
                    {{ number_format((float)($gym->reviews_avg_rating ?? 0), 1) }}
                    ★
                </span>
            </div>

            <h2>{{ $gym->gym_name }}</h2>

            <p>
                {{ \Illuminate\Support\Str::limit(
                            $gym->description ?: 'لا يوجد وصف مضاف.',
                            100
                        ) }}
            </p>

            <div class="apx-chips">
                @foreach($gym->activities->take(3) as $activity)
                <span class="apx-chip">
                    {{ $activity->activity_name }}
                </span>
                @endforeach
            </div>

            <div class="apx-tile-footer">

                <span>
                    {{ $gym->memberships_min_price_jod !== null
                                ? number_format($gym->memberships_min_price_jod, 2) . ' د.أ'
                                : 'السعر غير محدد' }}
                </span>

                <div class="apx-inline-actions">

                    @if($favoriteIds->contains((int)$gym->gym_id))

                    <form
                        method="POST"
                        action="{{ route('favorites.destroy', $gym) }}">
                        @csrf
                        @method('DELETE')

                        <button
                            class="apx-icon-danger"
                            type="submit"
                            aria-label="إزالة {{ $gym->gym_name }} من المفضلة">
                            <svg>
                                <use href="#apx-heart" />
                            </svg>
                        </button>
                    </form>

                    @else

                    <form
                        method="POST"
                        action="{{ route('favorites.store', $gym) }}">
                        @csrf

                        <button
                            class="apx-icon-button"
                            type="submit"
                            aria-label="إضافة {{ $gym->gym_name }} إلى المفضلة">
                            <svg>
                                <use href="#apx-heart" />
                            </svg>
                        </button>
                    </form>

                    @endif

                    <a
                        class="apx-primary"
                        href="{{ route('gyms.show', $gym) }}">
                        التفاصيل
                    </a>

                </div>

            </div>

            <label class="discover-choice">
                <input
                    type="checkbox"
                    class="discover-compare-check"
                    value="{{ $gym->gym_id }}">
                أضف النادي للمقارنة
            </label>

        </div>

    </article>

    @endforeach

</div>

<p
    id="discover-no-results"
    class="discover-status"
    hidden>
    لا توجد نوادٍ ضمن 5 كم من الموقع المختار
    في نتائج الصفحة الحالية.
</p>

<div class="discover-compare">

    <button
        id="discover-compare-btn"
        type="button"
        class="apx-primary"
        disabled>
        قارن النوادي المختارة (0/3)
    </button>

    <span id="discover-compare-hint">
        اختر ناديين أو ثلاثة للمقارنة.
    </span>

</div>

@if($gyms->hasPages())

<nav
    id="discover-pagination"
    class="apx-pagination"
    aria-label="صفحات النتائج">

    @if($gyms->onFirstPage())
    <span aria-disabled="true">السابق</span>
    @else
    <a href="{{ $gyms->previousPageUrl() }}">
        السابق
    </a>
    @endif

    <span>
        {{ $gyms->currentPage() }}
        /
        {{ $gyms->lastPage() }}
    </span>

    @if($gyms->hasMorePages())
    <a href="{{ $gyms->nextPageUrl() }}">
        التالي
    </a>
    @else
    <span aria-disabled="true">التالي</span>
    @endif

</nav>

@endif

@endif

@endsection

@push('scripts')

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin="anonymous"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const mapElement = document.getElementById('discover-map');

        const status = document.getElementById('discover-map-status');
        const searchButton = document.getElementById('discover-search-area');
        const locateButton = document.getElementById('discover-locate');
        const centerButton = document.getElementById('discover-pick-center');
        const areaSelect = document.getElementById('discover-area-select');
        const showAllButton = document.getElementById('discover-show-all');
        const recommendationsButton = document.getElementById('discover-recommendations');

        const count = document.getElementById('discover-result-count');
        const note = document.getElementById('discover-area-note');
        const noResults = document.getElementById('discover-no-results');
        const pagination = document.getElementById('discover-pagination');

        const compareButton = document.getElementById('discover-compare-btn');
        const compareHint = document.getElementById('discover-compare-hint');

        const compareBoxes = Array.from(
            document.querySelectorAll('.discover-compare-check')
        );

        const cards = Array.from(
            document.querySelectorAll('.discover-gym-card')
        );

        const radiusKm = 5;

        let allMapGyms = [];

        try {
            allMapGyms = JSON.parse(
                mapElement.dataset.mapGyms || '[]'
            );
        } catch (error) {
            console.error('Map data error:', error);
            allMapGyms = [];
        }

        let map = null;
        let selectedPosition = null;
        let selectionMarker = null;
        let userMarker = null;

        const gymPoints = cards.map(function(card) {

            const latText = card.dataset.lat;
            const lngText = card.dataset.lng;

            const lat = Number(latText);
            const lng = Number(lngText);

            const valid =
                latText !== '' &&
                lngText !== '' &&
                Number.isFinite(lat) &&
                Number.isFinite(lng) &&
                lat >= -90 &&
                lat <= 90 &&
                lng >= -180 &&
                lng <= 180;

            return {
                card: card,
                lat: lat,
                lng: lng,
                valid: valid
            };
        });

        function updateCompare() {

            const selected = compareBoxes.filter(function(box) {
                return box.checked;
            });

            compareButton.disabled =
                selected.length < 2 || selected.length > 3;

            compareButton.textContent =
                'قارن النوادي المختارة (' +
                selected.length +
                '/3)';

            compareHint.textContent =
                selected.length > 3 ?
                'يمكنك اختيار 3 نوادٍ كحد أقصى.' :
                'اختر ناديين أو ثلاثة للمقارنة.';

            compareBoxes.forEach(function(box) {
                box.disabled = !box.checked && selected.length >= 3;
            });
        }

        compareBoxes.forEach(function(box) {
            box.addEventListener('change', updateCompare);
        });

        if (compareButton) {

            compareButton.addEventListener('click', function() {

                const ids = compareBoxes
                    .filter(function(box) {
                        return box.checked;
                    })
                    .map(function(box) {
                        return box.value;
                    });

                if (ids.length < 2 || ids.length > 3) {
                    return;
                }

                const url = new URL(
                    document.querySelector(
                        '.apx-page-hero .apx-secondary'
                    ).href
                );

                ids.forEach(function(id) {
                    url.searchParams.append('gyms[]', id);
                });

                window.location.href = url.toString();
            });
        }

        recommendationsButton.addEventListener('click', function() {
            if (!selectedPosition) {
                return;
            }

            const url = new URL("{{ route('assistant.index') }}", window.location.origin);
            url.searchParams.set('lat', selectedPosition.lat.toFixed(7));
            url.searchParams.set('lng', selectedPosition.lng.toFixed(7));
            window.location.href = url.toString();
        });

        function showAll() {

            cards.forEach(function(card) {
                card.classList.remove('discover-hidden');
            });

            if (noResults) {
                noResults.hidden = true;
            }

            if (note) {
                note.hidden = true;
            }

            if (pagination) {
                pagination.hidden = false;
            }

            if (count) {
                count.textContent =
                    cards.length + ' نادي في الصفحة الحالية';
            }
        }

        showAllButton.addEventListener('click', function() {

            showAll();

            if (!map) {
                return;
            }

            const positions = allMapGyms
                .filter(function(gym) {
                    return Number.isFinite(Number(gym.lat)) &&
                        Number.isFinite(Number(gym.lng));
                })
                .map(function(gym) {
                    return [Number(gym.lat), Number(gym.lng)];
                });

            if (positions.length === 1) {
                map.setView(positions[0], 14);
            } else if (positions.length > 1) {
                map.fitBounds(positions, {
                    padding: [30, 30],
                    maxZoom: 14
                });
            }
        });

        function distanceKm(lat1, lng1, lat2, lng2) {

            const rad = Math.PI / 180;

            const a =
                Math.sin((lat2 - lat1) * rad / 2) ** 2 +
                Math.cos(lat1 * rad) *
                Math.cos(lat2 * rad) *
                Math.sin((lng2 - lng1) * rad / 2) ** 2;

            return 6371 * 2 *
                Math.atan2(
                    Math.sqrt(a),
                    Math.sqrt(1 - a)
                );
        }

        function filterAroundSelected() {

            if (!selectedPosition) {
                return;
            }

            let visible = 0;

            gymPoints.forEach(function(point) {

                const matches =
                    point.valid &&
                    distanceKm(
                        selectedPosition.lat,
                        selectedPosition.lng,
                        point.lat,
                        point.lng
                    ) <= radiusKm;

                point.card.classList.toggle(
                    'discover-hidden',
                    !matches
                );

                if (matches) {
                    visible++;
                }
            });

            if (count) {
                count.textContent =
                    visible + ' نادي ضمن 5 كم';
            }

            if (note) {
                note.hidden = false;
                note.textContent =
                    'النتائج ضمن 5 كم من الموقع المختار، ' +
                    'من النوادي الموجودة في الصفحة الحالية.';
            }

            if (noResults) {
                noResults.hidden = visible !== 0;
            }

            if (pagination) {
                pagination.hidden = true;
            }

            document.getElementById(
                'discover-results'
            ).scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        searchButton.addEventListener(
            'click',
            filterAroundSelected
        );

        if (typeof L === 'undefined') {

            status.textContent =
                'تعذر تحميل الخريطة. تأكدي من اتصال الإنترنت.';

            return;
        }

        function chooseLocation(latlng) {

            selectedPosition = L.latLng(latlng);

            if (selectionMarker) {
                map.removeLayer(selectionMarker);
            }

            selectionMarker = L.circleMarker(
                selectedPosition, {
                    radius: 9,
                    color: '#00b9e8',
                    fillColor: '#59d7f5',
                    fillOpacity: 0.85,
                    interactive: false
                }
            ).addTo(map);

            searchButton.disabled = false;
            recommendationsButton.disabled = false;

            status.textContent =
                'تم اختيار الموقع. اضغطي «عرض النوادي حول الموقع».';
        }

        try {

            map = L.map(mapElement, {
                dragging: true,
                touchZoom: true,
                scrollWheelZoom: true,
                doubleClickZoom: true,
                boxZoom: true,
                keyboard: true
            });

            map.setView([31.9, 35.9], 11);

            L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }
            ).addTo(map);

            const positions = [];

            allMapGyms.forEach(function(gym) {

                const lat = Number(gym.lat);
                const lng = Number(gym.lng);

                if (
                    !Number.isFinite(lat) ||
                    !Number.isFinite(lng) ||
                    lat < -90 || lat > 90 ||
                    lng < -180 || lng > 180
                ) {
                    return;
                }

                positions.push([lat, lng]);

                const link = document.createElement('a');
                link.textContent = gym.name || 'تفاصيل النادي';

                if (gym.url) {
                    link.href = gym.url;
                }

                L.marker([lat, lng])
                    .addTo(map)
                    .bindPopup(link);
            });

            if (positions.length === 1) {

                map.setView(positions[0], 14);

            } else if (positions.length > 1) {

                map.fitBounds(positions, {
                    padding: [30, 30],
                    maxZoom: 14
                });
            }

            map.dragging.enable();

            map.on('click', function(event) {
                chooseLocation(event.latlng);
            });

            map.whenReady(function() {
                setTimeout(function() {
                    map.invalidateSize();
                }, 150);
            });

            status.textContent =
                'اسحبي الخريطة أو اضغطي على أي مكان لتحديد موقع جديد.';

        } catch (error) {

            console.error('Discover map error:', error);

            status.textContent =
                'تعذر تشغيل الخريطة. افحصي أخطاء Console.';

            return;
        }

        centerButton.addEventListener('click', function() {

            if (!map) {
                return;
            }

            chooseLocation(map.getCenter());
        });

        areaSelect.addEventListener('change', function() {

            if (!map || !this.value) {
                return;
            }

            const coords = this.value
                .split(',')
                .map(Number);

            map.setView(coords, 13);

            chooseLocation(coords);
        });

        locateButton.addEventListener('click', function() {

            if (!navigator.geolocation) {

                status.textContent =
                    'المتصفح لا يدعم تحديد الموقع.';

                return;
            }

            locateButton.disabled = true;

            status.textContent =
                'جاري تحديد موقعك...';

            navigator.geolocation.getCurrentPosition(

                function(position) {

                    const latlng = [
                        position.coords.latitude,
                        position.coords.longitude
                    ];

                    if (userMarker) {
                        map.removeLayer(userMarker);
                    }

                    userMarker = L.circleMarker(
                        latlng, {
                            radius: 9,
                            color: '#2266db',
                            fillColor: '#2266db',
                            fillOpacity: 0.9
                        }
                    ).addTo(map);

                    userMarker.bindPopup('موقعك الحالي');

                    map.setView(latlng, 14);

                    chooseLocation(latlng);

                    locateButton.disabled = false;

                    status.textContent =
                        'تم تحديد موقعك. يمكنك اختيار أي مكان آخر.';
                },

                function() {

                    locateButton.disabled = false;

                    status.textContent =
                        'تعذر تحديد موقعك أو لم يتم منح الإذن. ' +
                        'يمكنك اختيار موقع يدويًا.';
                },

                {
                    enableHighAccuracy: true,
                    timeout: 15000
                }
            );
        });

        updateCompare();

    });
</script>

@endpush