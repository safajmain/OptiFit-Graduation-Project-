<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AirportRoadDemoDetailsSeeder extends Seeder
{
    public function run(): void
    {
        // الاسم، السعر الشهري، الأنشطة، المرافق
        $gyms = [
            [
                'Olive Fitness Demo 01',
                25,
                ['كمال الأجسام', 'رفع الأثقال'],
                ['أجهزة رياضية', 'غرف تبديل', 'خزائن'],
            ],
            [
                'Campus Strength Demo 02',
                30,
                ['رفع الأثقال', 'كمال الأجسام'],
                ['أجهزة رياضية', 'مدرب شخصي', 'مواقف سيارات'],
            ],
            [
                'Zaytoonah Active Demo 03',
                20,
                ['كارديو', 'تمارين جماعية'],
                ['أجهزة رياضية', 'غرف تبديل', 'واي فاي'],
            ],
            [
                'Power Zone Demo 04',
                35,
                ['كمال الأجسام', 'رفع الأثقال', 'كارديو'],
                ['أجهزة رياضية', 'مدرب شخصي', 'خزائن'],
            ],
            [
                'Flex Studio Demo 05',
                28,
                ['يوغا', 'تمارين جماعية'],
                ['غرف تبديل', 'واي فاي', 'مدرب شخصي'],
            ],
            [
                'Campus Cardio Demo 06',
                22,
                ['كارديو', 'تمارين جماعية'],
                ['أجهزة رياضية', 'خزائن', 'مواقف سيارات'],
            ],
            [
                'Olive Wellness Demo 07',
                40,
                ['يوغا', 'كارديو', 'سباحة'],
                ['مسبح', 'ساونا', 'غرف تبديل', 'خزائن'],
            ],
            [
                'Airport Fit Demo 08',
                32,
                ['كمال الأجسام', 'كارديو', 'تمارين جماعية'],
                ['أجهزة رياضية', 'مواقف سيارات', 'مدرب شخصي'],
            ],
            [
                'Runway Gym Demo 09',
                18,
                ['كمال الأجسام', 'رفع الأثقال'],
                ['أجهزة رياضية', 'غرف تبديل'],
            ],
            [
                'Skyline Fitness Demo 10',
                45,
                ['سباحة', 'يوغا', 'كارديو', 'كمال الأجسام'],
                ['مسبح', 'ساونا', 'مدرب شخصي', 'مواقف سيارات'],
            ],
        ];

        DB::transaction(function () use ($gyms) {
            foreach ($gyms as [$name, $price, $activities, $facilities]) {
                $gym = DB::table('gyms')
                    ->where('gym_name', $name)
                    ->first();

                if (!$gym) {
                    throw new RuntimeException(
                        "النادي التجريبي غير موجود: {$name}"
                    );
                }

                // الاشتراك الشهري التجريبي
                DB::table('gym_memberships')
                    ->where('gym_id', $gym->gym_id)
                    ->where('duration_months', 1)
                    ->delete();

                DB::table('gym_memberships')->insert([
                    'gym_id' => $gym->gym_id,
                    'duration_months' => 1,
                    'price_jod' => $price,
                ]);

                // الأنشطة: إضافة الاسم إن لم يكن موجودًا
                foreach ($activities as $activityName) {
                    DB::table('activities')->insertOrIgnore([
                        'activity_name' => $activityName,
                    ]);

                    $activityId = DB::table('activities')
                        ->where('activity_name', $activityName)
                        ->value('activity_id');

                    DB::table('gym_activities')->insertOrIgnore([
                        'gym_id' => $gym->gym_id,
                        'activity_id' => $activityId,
                    ]);
                }

                // المرافق: إضافة الاسم إن لم يكن موجودًا
                foreach ($facilities as $facilityName) {
                    $now = now();

                    DB::table('facilities')->insertOrIgnore([
                        'facility_name' => $facilityName,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $facilityId = DB::table('facilities')
                        ->where('facility_name', $facilityName)
                        ->value('facility_id');

                    DB::table('gym_facilities')->insertOrIgnore([
                        'gym_id' => $gym->gym_id,
                        'facility_id' => $facilityId,
                    ]);
                }

                // ساعات العمل التجريبية
                $days = [
                    'Saturday',
                    'Sunday',
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                ];

                foreach ($days as $day) {
                    DB::table('gym_working_hours')
                        ->where('gym_id', $gym->gym_id)
                        ->where('day_of_week', $day)
                        ->delete();

                    DB::table('gym_working_hours')->insert([
                        'gym_id' => $gym->gym_id,
                        'day_of_week' => $day,
                        'open_time' => $day === 'Friday'
                            ? '14:00:00'
                            : '07:00:00',
                        'close_time' => $day === 'Friday'
                            ? '22:00:00'
                            : '23:00:00',
                    ]);
                }
            }
        });

        $this->command?->info(
            'تم تجهيز بيانات 10 نوادٍ تجريبية بنجاح.'
        );
    }
}
