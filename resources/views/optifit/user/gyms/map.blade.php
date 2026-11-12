@extends('layouts.optifit-user')

@section('title', 'خريطة النوادي | OptiFit APEX')
@section('eyebrow', 'USER / MAP')
@section('page-heading', 'خريطة النوادي')

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
@endpush

@section('content')
<section class="apx-page-hero">
    <div>
        <span class="apx-eyebrow">OPENSTREETMAP / NO PAID KEY</span>
        <h1>الأندية على <em>الخريطة.</em></h1>
        <p>استكشف النوادي المسجّلة والمعتمدة في OptiFit، وحدد موقعك أو تنقّل على الخريطة بحرية.</p>
    </div>
</section>

@if($gyms->isEmpty())
<section class="apx-panel apx-empty">
    <svg aria-hidden="true">
        <use href="#apx-map"></use>
    </svg>
    <h2>لا توجد نوادٍ متاحة على الخريطة</h2>
    <p>لا توجد حاليًا نوادٍ معتمدة لديها إحداثيات.</p>
    <a class="apx-primary" href="{{ route('gyms.index') }}">البحث عن النوادي</a>
</section>
@else
<div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px;">
    <button type="button" id="locate-me-btn" class="apx-primary">📍 تحديد موقعي</button>
    <button type="button" id="show-all-gyms-btn" class="apx-primary">🏋️ عرض جميع النوادي</button>
    <button type="button" id="search-selected-btn" class="apx-primary" disabled>🔎 عرض النوادي حول الموقع المختار</button>
</div>

<p id="location-message" role="status" aria-live="polite" style="margin-bottom: 12px;">اضغطي على أي نقطة بالخريطة لتحديد منطقة البحث (ضمن 5 كم)، ثم اضغطي زر عرض النوادي حول الموقع المختار.</p>
<section class="apx-panel" style="margin-bottom: 14px; padding: 16px;" aria-label="نتائج البحث حول الموقع المختار">
    <h2 style="margin: 0 0 8px; font-size: 1.1rem;">النوادي في المنطقة المختارة</h2>
    <div id="nearby-results" aria-live="polite">اختاري نقطة على الخريطة لعرض النوادي القريبة منها.</div>
</section>

{{-- تُخزّن البيانات في HTML حتى لا تختلط تعبيرات Blade مع JavaScript --}}
<div id="gyms-data" hidden data-gyms="{{ $mapGyms->toJson(JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}"></div>

<div id="gym-map" class="apx-map-canvas" role="region" aria-label="خريطة النوادي" style="min-height: 450px;"></div>

<section id="map-fallback" class="apx-panel apx-map-fallback" role="alert">
    <div class="apx-panel-body">
        <h2>تعذر تحميل الخريطة</h2>
        <p>بيانات النوادي متاحة، لكن تعذر تحميل الخريطة. يمكنك فتح تفاصيل النوادي مباشرة:</p>
        <div class="apx-chips">
            @foreach($gyms as $gym)
            <a class="apx-chip" href="{{ route('gyms.show', $gym) }}">
                {{ $gym->gym_name }} — {{ collect([$gym->city, $gym->area])->filter()->join('، ') }}
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection

@if($gyms->isNotEmpty())
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const mapElement = document.getElementById('gym-map');
        const dataElement = document.getElementById('gyms-data');
        const fallback = document.getElementById('map-fallback');
        const locateButton = document.getElementById('locate-me-btn');
        const showAllButton = document.getElementById('show-all-gyms-btn');
        const searchSelectedButton = document.getElementById('search-selected-btn');
        const nearbyResults = document.getElementById('nearby-results');
        const locationMessage = document.getElementById('location-message');

        const showFallback = () => fallback?.classList.add('is-visible');
        const setMessage = (message) => {
            if (locationMessage) locationMessage.textContent = message;
        };

        let gyms;
        try {
            gyms = JSON.parse(dataElement?.dataset.gyms || '[]');
        } catch (error) {
            console.error('Invalid gym map data', error);
            showFallback();
            return;
        }

        if (!mapElement || !window.L || !Array.isArray(gyms) || gyms.length === 0) {
            showFallback();
            return;
        }

        try {
            const map = L.map(mapElement, {
                scrollWheelZoom: true
            });
            const tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            });
            tiles.on('tileerror', showFallback);
            tiles.addTo(map);

            const points = [];
            const gymRecords = [];
            const gymLayer = L.layerGroup().addTo(map);
            const searchRadiusMeters = 5000;
            let selectedPoint = null;
            let selectedMarker = null;
            let selectedCircle = null;

            gyms.forEach((gym) => {
                const lat = Number(gym.lat);
                const lng = Number(gym.lng);
                if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
                if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return;

                const position = [lat, lng];
                points.push(position);

                const popup = document.createElement('div');
                const name = document.createElement('strong');
                const location = document.createElement('p');
                const link = document.createElement('a');
                name.textContent = gym.name || 'نادي رياضي';
                location.textContent = [gym.city, gym.area].filter(Boolean).join('، ');
                link.textContent = 'عرض تفاصيل النادي';
                link.href = gym.url;
                popup.append(name, location, link);

                const marker = L.marker(position).bindPopup(popup);
                gymRecords.push({
                    gym,
                    position,
                    marker
                });
                marker.addTo(gymLayer);
            });

            function clearSelection() {
                selectedPoint = null;
                if (selectedMarker) map.removeLayer(selectedMarker);
                if (selectedCircle) map.removeLayer(selectedCircle);
                selectedMarker = null;
                selectedCircle = null;
                if (searchSelectedButton) searchSelectedButton.disabled = true;
            }

            function showAllGyms() {
                gymLayer.clearLayers();
                gymRecords.forEach((record) => record.marker.addTo(gymLayer));
                clearSelection();
                if (nearbyResults) {
                    nearbyResults.textContent = 'تظهر جميع النوادي المسجّلة على الخريطة. اضغطي على أي نقطة لاختيار منطقة جديدة.';
                }
                if (points.length === 1) {
                    map.setView(points[0], 14);
                } else if (points.length > 1) {
                    map.fitBounds(points, {
                        padding: [35, 35],
                        maxZoom: 14
                    });
                } else {
                    map.setView([31.9, 35.9], 12);
                }
            }

            // الضغط على الخريطة يحدد موقع بحث اختياري، ولا يغيّر موقع المستخدم الحقيقي.
            map.on('click', (event) => {
                selectedPoint = event.latlng;
                if (selectedMarker) map.removeLayer(selectedMarker);
                if (selectedCircle) map.removeLayer(selectedCircle);
                selectedMarker = L.circleMarker(selectedPoint, {
                    radius: 9,
                    color: '#ffffff',
                    weight: 3,
                    fillColor: '#e11d48',
                    fillOpacity: 1
                }).addTo(map).bindPopup('📌 الموقع المختار').openPopup();
                selectedCircle = L.circle(selectedPoint, {
                    radius: searchRadiusMeters,
                    color: '#e11d48',
                    fillColor: '#fb7185',
                    fillOpacity: 0.08
                }).addTo(map);
                if (searchSelectedButton) searchSelectedButton.disabled = false;
                setMessage('تم اختيار موقع على الخريطة. اضغطي «عرض النوادي حول الموقع المختار» للبحث ضمن 5 كم.');
            });

            searchSelectedButton?.addEventListener('click', () => {
                if (!selectedPoint) return;
                const nearby = gymRecords
                    .map((record) => ({
                        ...record,
                        distance: map.distance(selectedPoint, record.position)
                    }))
                    .filter((record) => record.distance <= searchRadiusMeters)
                    .sort((a, b) => a.distance - b.distance);

                gymLayer.clearLayers();
                nearby.forEach((record) => record.marker.addTo(gymLayer));

                if (nearbyResults) {
                    nearbyResults.replaceChildren();
                    if (nearby.length === 0) {
                        nearbyResults.textContent = 'لا توجد نوادٍ مسجّلة ومعتمدة ضمن 5 كم من الموقع المختار.';
                    } else {
                        const summary = document.createElement('p');
                        summary.textContent = 'عدد النوادي ضمن 5 كم: ' + nearby.length;
                        nearbyResults.appendChild(summary);
                        const list = document.createElement('ul');
                        nearby.forEach((record) => {
                            const item = document.createElement('li');
                            const link = document.createElement('a');
                            link.href = record.gym.url;
                            link.textContent = (record.gym.name || 'نادي رياضي') +
                                ' — ' + (record.distance / 1000).toFixed(1) + ' كم';
                            item.appendChild(link);
                            list.appendChild(item);
                        });
                        nearbyResults.appendChild(list);
                    }
                }
                setMessage('تم عرض نتائج النوادي حول الموقع المختار. يمكنك اختيار نقطة أخرى والبحث من جديد.');
            });

            showAllGyms();
            showAllButton?.addEventListener('click', () => {
                showAllGyms();
                setMessage('تم عرض جميع النوادي المسجّلة على الخريطة.');
            });

            let userMarker = null;
            let accuracyCircle = null;

            locateButton?.addEventListener('click', () => {
                if (!navigator.geolocation) {
                    setMessage('متصفحك لا يدعم تحديد الموقع.');
                    return;
                }

                locateButton.disabled = true;
                setMessage('جاري تحديد موقعك...');

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const accuracy = position.coords.accuracy;

                        if (userMarker) map.removeLayer(userMarker);
                        if (accuracyCircle) map.removeLayer(accuracyCircle);

                        userMarker = L.circleMarker([lat, lng], {
                            radius: 9,
                            color: '#ffffff',
                            weight: 3,
                            fillColor: '#2563eb',
                            fillOpacity: 1
                        }).addTo(map).bindPopup('📍 أنت هنا').openPopup();

                        accuracyCircle = L.circle([lat, lng], {
                            radius: accuracy,
                            color: '#2563eb',
                            fillColor: '#3b82f6',
                            fillOpacity: 0.12
                        }).addTo(map);

                        map.setView([lat, lng], 15);
                        setMessage('تم تحديد موقعك. يمكنك تحريك الخريطة لاستكشاف النوادي.');
                        locateButton.disabled = false;
                    },
                    (error) => {
                        if (error.code === 1) {
                            setMessage('يرجى السماح للمتصفح بالوصول إلى موقعك.');
                        } else if (error.code === 2) {
                            setMessage('تعذر معرفة موقعك الحالي. حاول مرة أخرى.');
                        } else if (error.code === 3) {
                            setMessage('انتهت مهلة تحديد الموقع. حاول مرة أخرى.');
                        } else {
                            setMessage('تعذر تحديد موقعك. يمكنك تحريك الخريطة يدويًا.');
                        }
                        locateButton.disabled = false;
                    }, {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 60000
                    }
                );
            });
        } catch (error) {
            console.error('OptiFit map initialization failed', error);
            showFallback();
        }
    });
</script>
@endpush
@endif