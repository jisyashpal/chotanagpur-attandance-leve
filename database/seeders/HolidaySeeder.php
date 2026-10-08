<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * List of Leaves, 2026 for the State Universities of Jharkhand (Annexure-1).
     */
    public function run(): void
    {
        $description = 'List of Leaves, 2026 — State Universities of Jharkhand (Annexure-1)';

        $holidays = [
            ['name' => 'New Year Day', 'start' => '2026-01-01', 'end' => '2026-01-01'],
            ['name' => 'Sohrai', 'start' => '2026-01-12', 'end' => '2026-01-13'],
            ['name' => 'Makar Sankranti', 'start' => '2026-01-14', 'end' => '2026-01-14'],
            ['name' => 'Subhash Chandra Bose Jayanti / Basant Panchami', 'start' => '2026-01-23', 'end' => '2026-01-23'],
            ['name' => 'Republic Day', 'start' => '2026-01-26', 'end' => '2026-01-26'],
            ['name' => 'Ravidas Jayanti', 'start' => '2026-02-01', 'end' => '2026-02-01'],
            ['name' => 'Shab-e-Barat', 'start' => '2026-02-05', 'end' => '2026-02-05'],
            ['name' => 'Mahashivratri', 'start' => '2026-02-15', 'end' => '2026-02-15'],
            ['name' => 'Holi', 'start' => '2026-03-03', 'end' => '2026-03-05'],
            ['name' => 'Last Friday of Ramzán', 'start' => '2026-03-20', 'end' => '2026-03-20'],
            ['name' => 'Sarhul / Id-ul-Fitre', 'start' => '2026-03-21', 'end' => '2026-03-21'],
            ['name' => 'Ramnavami', 'start' => '2026-03-26', 'end' => '2026-03-26'],
            ['name' => 'Mahavir Jayanti', 'start' => '2026-03-31', 'end' => '2026-03-31'],
            ['name' => 'Good Friday', 'start' => '2026-04-03', 'end' => '2026-04-03'],
            ['name' => 'Easter Monday', 'start' => '2026-04-06', 'end' => '2026-04-06'],
            ['name' => 'Ambedkar Jayanti', 'start' => '2026-04-14', 'end' => '2026-04-14'],
            ['name' => 'Majdoor Diwas / Buddh Purnima', 'start' => '2026-05-01', 'end' => '2026-05-01'],
            ['name' => 'Id-ul-Juha (Bakrid)', 'start' => '2026-05-27', 'end' => '2026-05-27'],
            ['name' => 'Summer Vacation', 'start' => '2026-06-01', 'end' => '2026-06-20'],
            ['name' => 'Muharram', 'start' => '2026-06-26', 'end' => '2026-06-27'],
            ['name' => 'Hul Diwas', 'start' => '2026-06-30', 'end' => '2026-06-30'],
            ['name' => 'Rath Yatra', 'start' => '2026-07-16', 'end' => '2026-07-16'],
            ['name' => 'Vishwa Adiwasi Diwas', 'start' => '2026-08-09', 'end' => '2026-08-09'],
            ['name' => 'Independence Day', 'start' => '2026-08-15', 'end' => '2026-08-15'],
            ['name' => 'Last Monday of Shrawan', 'start' => '2026-08-24', 'end' => '2026-08-24'],
            ['name' => 'Eid-e-Milad-un-Nabi', 'start' => '2026-08-26', 'end' => '2026-08-26'],
            ['name' => 'Raksha Bandhan', 'start' => '2026-08-28', 'end' => '2026-08-28'],
            ['name' => 'Janmasthami', 'start' => '2026-09-04', 'end' => '2026-09-04'],
            ['name' => 'Ganesh Chaturthi', 'start' => '2026-09-14', 'end' => '2026-09-14'],
            ['name' => 'Vishwakarma Puja', 'start' => '2026-09-17', 'end' => '2026-09-17'],
            ['name' => 'Karma Puja', 'start' => '2026-09-22', 'end' => '2026-09-23'],
            ['name' => 'Anant Chaturdasi', 'start' => '2026-09-25', 'end' => '2026-09-25'],
            ['name' => 'Gandhi Jayanti', 'start' => '2026-10-02', 'end' => '2026-10-02'],
            ['name' => 'Mahalaya', 'start' => '2026-10-10', 'end' => '2026-10-10'],
            ['name' => 'Kalash Sthapana', 'start' => '2026-10-11', 'end' => '2026-10-11'],
            ['name' => 'Dussehra Vacation', 'start' => '2026-10-16', 'end' => '2026-10-21'],
            [
                'name' => 'Autumn Vacation (Deepawali, Bhagwan Birsa Munda Jayanti / Rajya Sthapana Diwas & Chhath Puja)',
                'start' => '2026-11-07',
                'end' => '2026-11-18',
            ],
            ['name' => 'Gurunanak Jayanti', 'start' => '2026-11-24', 'end' => '2026-11-24'],
            ['name' => 'Christmas / Winter Vacation', 'start' => '2026-12-25', 'end' => '2026-12-31'],
        ];

        foreach ($holidays as $holiday) {
            $period = CarbonPeriod::create(
                Carbon::parse($holiday['start']),
                Carbon::parse($holiday['end'])
            );

            foreach ($period as $date) {
                Holiday::updateOrCreate(
                    ['date' => $date->toDateString()],
                    [
                        'name' => $holiday['name'],
                        'description' => $description,
                    ]
                );
            }
        }
    }
}
