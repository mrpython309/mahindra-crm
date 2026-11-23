<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Segment;
use App\Models\SubSegment;

class SegmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $segments = [
            ['code' => 'PV', 'name' => 'Personal Vehicle'],
            ['code' => 'CV', 'name' => 'Commercial Vehicle'],
            ['code' => 'EV', 'name' => 'Electric Vehicle'],
        ];

        $subSegmentsByCode = [
            'PV' => [
                'Hatchback',
                'Sedan',
                'SUV',
                'MUV',
                'Crossover',
            ],
            'CV' => [
                'LCV',
                'MCV',
                'HCV',
                'Pickup',
                'Mini Truck',
            ],
            'EV' => [
                'Electric Two Wheeler',
                'Electric Car',
                'Electric SUV',
                'Electric Commercial',
            ],
        ];

        foreach ($segments as $segmentData) {
            $segment = Segment::updateOrCreate(
                ['code' => $segmentData['code']],
                $segmentData
            );

            $subSegmentNames = $subSegmentsByCode[$segmentData['code']] ?? [];
            foreach ($subSegmentNames as $subSegmentName) {
                SubSegment::updateOrCreate(
                    [
                        'segment_id'       => $segment->id,
                        'subsegment_name'  => $subSegmentName,
                    ],
                    ['status' => 'active']
                );
            }
        }
    }
}
