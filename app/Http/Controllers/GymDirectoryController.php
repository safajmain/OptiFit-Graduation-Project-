<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Facility;
use App\Models\Gym;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GymDirectoryController extends Controller
{

    public function index(Request $request): View
    {
        // عرض توصيات النوادي تلقائيًا حسب بيانات بروفايل المستخدم.
        return $this->assistant($request);
    }


    public function show(Request $request, Gym $gym): View
    {
        $this->authorize('view', $gym);

        $gym->load([
            'owner',
            'activities',
            'facilities',
            'memberships',
            'workingHours',
            'images',
            'reviews.user',
        ])->loadCount('favoritedByUsers')
            ->loadAvg('reviews', 'rating');

        $isFavorite = $request->user()
            ->favoriteGyms()
            ->where('gyms.gym_id', $gym->gym_id)
            ->exists();

        $myReview = $gym->reviews()
            ->where('user_id', $request->user()->id)
            ->first();

        return view('optifit.user.gyms.show', compact(
            'gym',
            'isFavorite',
            'myReview'
        ));
    }

    public function compare(Request $request): View
    {
        $data = $request->validate([
            'gyms' => ['nullable', 'array', 'max:3'],
            'gyms.*' => [
                'integer',
                'distinct',
                'exists:gyms,gym_id',
            ],
        ]);

        $ids = collect($data['gyms'] ?? [])
            ->map(fn($id) => (int) $id)
            ->all();

        $gyms = $ids
            ? $this->approvedQuery()
            ->whereIn('gym_id', $ids)
            ->get()
            ->sortBy(
                fn(Gym $gym) =>
                array_search((int) $gym->gym_id, $ids, true)
            )
            ->values()
            : collect();

        $availableGyms = Gym::query()
            ->where('verification_status', 'approved')
            ->orderBy('gym_name')
            ->get(['gym_id', 'gym_name']);

        return view('optifit.user.gyms.compare', compact(
            'gyms',
            'availableGyms',
            'ids'
        ));
    }

    public function map(): View
    {
        $gyms = $this->approvedQuery()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [-90, 90])
            ->whereBetween('longitude', [-180, 180])
            ->get();

        $mapGyms = $gyms->map(
            fn(Gym $gym): array => [
                'id' => (int) $gym->gym_id,
                'name' => $gym->gym_name,
                'lat' => (float) $gym->latitude,
                'lng' => (float) $gym->longitude,
                'url' => route('gyms.show', $gym),
                'city' => $gym->city,
                'area' => $gym->area,
            ]
        )->values();

        return view('optifit.user.gyms.map', compact(
            'gyms',
            'mapGyms'
        ));
    }

    public function assistant(Request $request): View
    {
        $user = $request->user();

        $preference = $user->preferences()->first();

        $activityNames = $user->preferredActivities()
            ->pluck('activity_name');

        $facilityNames = $user->preferredFacilities()
            ->pluck('facility_name');

        $budget = $preference?->max_budget !== null
            ? (float) $preference->max_budget
            : null;

        $location = mb_strtolower(
            trim((string) $preference?->location)
        );

        $hasPreferences =
            $activityNames->isNotEmpty()
            || $facilityNames->isNotEmpty()
            || $budget !== null
            || $location !== '';

        $recommendations = collect();

        if ($hasPreferences) {
            $recommendations = $this->approvedQuery()
                ->get()
                ->map(function (Gym $gym) use (
                    $activityNames,
                    $facilityNames,
                    $budget,
                    $location
                ): Gym {
                    $matchedActivities = $gym->activities
                        ->pluck('activity_name')
                        ->intersect($activityNames)
                        ->values();

                    $matchedFacilities = $gym->facilities
                        ->pluck('facility_name')
                        ->intersect($facilityNames)
                        ->values();

                    $activityMatches = $matchedActivities->count();
                    $facilityMatches = $matchedFacilities->count();

                    $price = $gym->memberships->min('price_jod');

                    $locationMatch = $location !== ''
                        && collect([
                            $gym->city,
                            $gym->area,
                            $gym->address,
                        ])
                        ->filter()
                        ->contains(
                            fn($value) => str_contains(
                                mb_strtolower((string) $value),
                                $location
                            )
                        );

                    $budgetMatch = $budget !== null
                        && $price !== null
                        && (float) $price <= $budget;

                    $score =
                        ($activityMatches * 3)
                        + ($facilityMatches * 2)
                        + ((int) $locationMatch * 2)
                        + (int) $budgetMatch;

                    $reasons = [];

                    if ($matchedActivities->isNotEmpty()) {
                        $reasons[] = 'أنشطة مناسبة: '
                            . $matchedActivities->implode('، ');
                    }

                    if ($matchedFacilities->isNotEmpty()) {
                        $reasons[] = 'مرافق مناسبة: '
                            . $matchedFacilities->implode('، ');
                    }

                    if ($locationMatch) {
                        $reasons[] = 'موقع النادي يناسب منطقتك';
                    }

                    if ($budgetMatch) {
                        $reasons[] = 'السعر ضمن ميزانيتك';
                    }

                    $gym->setAttribute('match_score', $score);

                    $gym->setAttribute(
                        'match_reasons',
                        $reasons
                    );

                    return $gym;
                })
                ->filter(
                    fn(Gym $gym) =>
                    $gym->match_score > 0
                )
                ->sortByDesc('match_score')
                ->values();
        }

        return view('optifit.user.gyms.assistant', compact(
            'recommendations',
            'preference',
            'activityNames',
            'facilityNames',
            'hasPreferences'
        ));
    }

    private function approvedQuery(): Builder
    {
        return Gym::query()
            ->where('verification_status', 'approved')
            ->with([
                'owner',
                'activities',
                'facilities',
                'memberships',
                'workingHours',
                'images',
            ])
            ->withCount([
                'reviews',
                'favoritedByUsers',
            ])
            ->withAvg('reviews', 'rating')
            ->withMin('memberships', 'price_jod');
    }
}
