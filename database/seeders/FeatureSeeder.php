<?php
// database/seeders/FeatureSeeder.php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            // ─── Technology ──────────────────────────────────
            ['name' => 'Bluetooth',       ],
            ['name' => 'Apple CarPlay',    ],
            ['name' => 'Android Auto',     ],
            ['name' => 'GPS',              ],
            ['name' => 'AUX Input',        ],
            ['name' => 'USB Charger',      ],
            ['name' => 'USB Input',        ],
            ['name' => 'Backup Camera',   ],

            // ─── Safety ──────────────────────────────────────
            ['name' => 'Blind Spot Warning', ],
            ['name' => 'All-Wheel Drive',  ],
            ['name' => 'Snow Tires or Chains',],
            ['name' => 'Toll Pass',         ],

            // ─── Comfort ─────────────────────────────────────
            ['name' => 'Air Conditioning', ],
            ['name' => 'Sunroof',          ],
            ['name' => 'Heated Seats',     ],
            ['name' => 'Convertible',      ],

            // ─── Accessibility ───────────────────────────────
            ['name' => 'Wheelchair Accessible', ],
            ['name' => 'Child Seat',            ],
            ['name' => 'Bike Rack',            ],
        ];

        foreach ($features as $feature) {
            Feature::updateOrCreate(
                ['name' => $feature['name']],
            );
        }

        $this->command->info('✅ Features seeded successfully! Total: ' . count($features));
    }
}
