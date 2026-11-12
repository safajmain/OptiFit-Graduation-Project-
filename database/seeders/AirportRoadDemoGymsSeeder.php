<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AirportRoadDemoGymsSeeder extends Seeder
{
    public function run(): void
    {
        // نستخدم مالكًا موجودًا حتى لا نخالف المفتاح الأجنبي.
        $ownerId = DB::table('gyms')
            ->whereNotNull('owner_id')
            ->value('owner_id');

        if (!$ownerId) {
            throw new RuntimeException(
                'لا يوجد مالك نادي حالي. أنشئي حساب مالك نادي أولًا.'
            );
        }

        // الإحداثيات تقريبية وتجريبية وليست مواقع نوادٍ فعلية.
        $gyms = [
            [
                'name' => 'Olive Fitness Demo 01',
                'area' => 'قرب جامعة الزيتونة',
                'lat' => 31.83710,
                'lng' => 35.90420,
            ],
            [
                'name' => 'Campus Strength Demo 02',
                'area' => 'قرب جامعة الزيتونة',
                'lat' => 31.83830,
                'lng' => 35.90610,
            ],
            [
                'name' => 'Zaytoonah Active Demo 03',
                'area' => 'قرب جامعة الزيتونة',
                'lat' => 31.83580,
                'lng' => 35.90800,
            ],
            [
                'name' => 'Power Zone Demo 04',
                'area' => 'قرب جامعة الزيتونة',
                'lat' => 31.84020,
                'lng' => 35.90290,
            ],
            [
                'name' => 'Flex Studio Demo 05',
                'area' => 'قرب جامعة الزيتونة',
                'lat' => 31.83460,
                'lng' => 35.90550,
            ],
            [
                'name' => 'Campus Cardio Demo 06',
                'area' => 'قرب جامعة الزيتونة',
                'lat' => 31.83940,
                'lng' => 35.90930,
            ],
            [
                'name' => 'Olive Wellness Demo 07',
                'area' => 'قرب جامعة الزيتونة',
                'lat' => 31.83620,
                'lng' => 35.90180,
            ],
            [
                'name' => 'Airport Fit Demo 08',
                'area' => 'طريق المطار',
                'lat' => 31.85620,
                'lng' => 35.91200,
            ],
            [
                'name' => 'Runway Gym Demo 09',
                'area' => 'طريق المطار',
                'lat' => 31.87750,
                'lng' => 35.90820,
            ],
            [
                'name' => 'Skyline Fitness Demo 10',
                'area' => 'طريق المطار',
                'lat' => 31.81650,
                'lng' => 35.91510,
            ],
        ];

        foreach ($gyms as $gym) {
            DB::table('gyms')->updateOrInsert(
                [
                    'gym_name' => $gym['name'],
                ],
                [
                    'owner_id' => $ownerId,
                    'description' =>
                    'نادي تجريبي لاختبار خريطة OptiFit '
                        . 'والبحث حسب الموقع والمقارنة والتوصيات.',
                    'city' => 'عمّان',
                    'area' => $gym['area'],
                    'address' =>
                    'موقع تجريبي - '
                        . $gym['area']
                        . ' - عمّان',
                    'latitude' => $gym['lat'],
                    'longitude' => $gym['lng'],
                    'verification_status' => 'approved',
                    'updated_at' => now(),
                ]
            );
        }

        $this->command?->info(
            'تمت معالجة 10 نوادٍ تجريبية بنجاح.'
        );
    }
}
