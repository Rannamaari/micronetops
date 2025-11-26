<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\InventoryItem;
use App\Models\Job;
use App\Models\PettyCash;
use Illuminate\Support\Facades\Auth;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating sample customers...');
        $this->createCustomers();

        $this->command->info('Creating sample inventory items...');
        $this->createInventoryItems();

        $this->command->info('Creating sample jobs...');
        $this->createJobs();

        $this->command->info('Creating sample petty cash transactions...');
        $this->createPettyCashTransactions();

        $this->command->info('Sample data created successfully!');
    }

    private function createCustomers()
    {
        $maldivianNames = [
            'Ahmed', 'Mohamed', 'Ali', 'Hassan', 'Ibrahim',
            'Aminath', 'Fathimath', 'Aishath', 'Mariyam', 'Hawwa',
            'Hussain', 'Ismail', 'Abdul', 'Moosa', 'Abdulla',
            'Khadeeja', 'Nashida', 'Shaheema', 'Reema', 'Sofiya',
            'Rasheed', 'Shahid', 'Imran', 'Naeem', 'Sameer',
            'Zara', 'Laila', 'Nisha', 'Raisa', 'Faiza',
            'Asif', 'Riyaz', 'Shan', 'Yoosuf', 'Zakariyya',
            'Muna', 'Shifa', 'Hana', 'Sara', 'Aya',
            'Hamza', 'Umar', 'Bilal', 'Khalid', 'Faisal',
            'Zainab', 'Hafsa', 'Rukhsar', 'Safia', 'Nadiya'
        ];

        $roads = [
            'Boduthakurufaanu Magu', 'Majeedhee Magu', 'Ameenee Magu', 'Orchid Magu',
            'Sosun Magu', 'Lily Magu', 'Violet Magu', 'Rahdhebai Magu', 'Buruzu Magu',
            'Fareedhee Magu', 'Medhuziyaarai Magu', 'Chaandhanee Magu', 'Maafannu Goalhi'
        ];

        $areas = ['Malé', 'Hulhumalé Phase 1', 'Hulhumalé Phase 2', 'Vilimalé', 'Thilafushi'];

        for ($i = 1; $i <= 60; $i++) {
            $name = $maldivianNames[array_rand($maldivianNames)] . ' ' . $maldivianNames[array_rand($maldivianNames)];
            $phone = '7' . str_pad(rand(600000, 999999), 6, '0', STR_PAD_LEFT);
            $category = ['moto', 'ac', 'both'][array_rand(['moto', 'ac', 'both'])];

            $customer = Customer::create([
                'name' => $name,
                'phone' => $phone,
                'email' => rand(0, 1) ? strtolower(str_replace(' ', '', $name)) . '@gmail.com' : null,
                'address' => $roads[array_rand($roads)] . ', ' . $areas[array_rand($areas)],
                'category' => $category,
                'notes' => rand(0, 1) ? 'Regular customer' : null,
            ]);

            // Add vehicles for moto customers
            if (in_array($category, ['moto', 'both'])) {
                $brands = ['Honda', 'Yamaha', 'Suzuki', 'TVS', 'Hero', 'Bajaj'];
                $models = ['CB150', 'YZF-R15', 'Gixxer', 'Apache', 'Splendor', 'Pulsar'];

                Vehicle::create([
                    'customer_id' => $customer->id,
                    'brand' => $brands[array_rand($brands)],
                    'model' => $models[array_rand($models)],
                    'registration_number' => strtoupper(chr(rand(65, 90))) . rand(1000, 9999),
                    'year' => rand(2015, 2024),
                    'mileage' => rand(5000, 50000),
                ]);
            }
        }
    }

    private function createInventoryItems()
    {
        // Motorcycle parts
        $motoParts = [
            ['name' => 'Engine Oil 10W-40', 'brand' => 'Castrol', 'unit' => 'ltr', 'cost' => 85, 'sell' => 120],
            ['name' => 'Engine Oil 20W-50', 'brand' => 'Shell', 'unit' => 'ltr', 'cost' => 90, 'sell' => 130],
            ['name' => 'Brake Pads Front', 'brand' => 'EBC', 'unit' => 'set', 'cost' => 250, 'sell' => 350],
            ['name' => 'Brake Pads Rear', 'brand' => 'EBC', 'unit' => 'set', 'cost' => 200, 'sell' => 300],
            ['name' => 'Air Filter', 'brand' => 'KN', 'unit' => 'pcs', 'cost' => 150, 'sell' => 220],
            ['name' => 'Spark Plug', 'brand' => 'NGK', 'unit' => 'pcs', 'cost' => 45, 'sell' => 75],
            ['name' => 'Chain Sprocket Kit', 'brand' => 'DID', 'unit' => 'set', 'cost' => 800, 'sell' => 1200],
            ['name' => 'Battery 12V 7Ah', 'brand' => 'Yuasa', 'unit' => 'pcs', 'cost' => 350, 'sell' => 500],
            ['name' => 'Front Tyre 90/90-17', 'brand' => 'MRF', 'unit' => 'pcs', 'cost' => 600, 'sell' => 850],
            ['name' => 'Rear Tyre 120/80-17', 'brand' => 'MRF', 'unit' => 'pcs', 'cost' => 750, 'sell' => 1050],
            ['name' => 'Clutch Cable', 'brand' => 'Genuine', 'unit' => 'pcs', 'cost' => 80, 'sell' => 130],
            ['name' => 'Throttle Cable', 'brand' => 'Genuine', 'unit' => 'pcs', 'cost' => 75, 'sell' => 120],
            ['name' => 'Head Light Bulb H4', 'brand' => 'Philips', 'unit' => 'pcs', 'cost' => 120, 'sell' => 180],
            ['name' => 'Tail Light Bulb', 'brand' => 'Philips', 'unit' => 'pcs', 'cost' => 35, 'sell' => 60],
            ['name' => 'Side Mirror Left', 'brand' => 'Generic', 'unit' => 'pcs', 'cost' => 150, 'sell' => 250],
            ['name' => 'Side Mirror Right', 'brand' => 'Generic', 'unit' => 'pcs', 'cost' => 150, 'sell' => 250],
            ['name' => 'Clutch Lever', 'brand' => 'Generic', 'unit' => 'pcs', 'cost' => 100, 'sell' => 170],
            ['name' => 'Brake Lever', 'brand' => 'Generic', 'unit' => 'pcs', 'cost' => 100, 'sell' => 170],
            ['name' => 'Speedometer Cable', 'brand' => 'Generic', 'unit' => 'pcs', 'cost' => 90, 'sell' => 150],
            ['name' => 'Fuel Filter', 'brand' => 'Mann', 'unit' => 'pcs', 'cost' => 55, 'sell' => 95],
        ];

        // AC parts
        $acParts = [
            ['name' => 'R410A Refrigerant Gas', 'brand' => 'Honeywell', 'unit' => 'kg', 'cost' => 400, 'sell' => 600],
            ['name' => 'R22 Refrigerant Gas', 'brand' => 'DuPont', 'unit' => 'kg', 'cost' => 350, 'sell' => 550],
            ['name' => 'AC Compressor 1.5 Ton', 'brand' => 'Panasonic', 'unit' => 'pcs', 'cost' => 3500, 'sell' => 5000],
            ['name' => 'AC Compressor 2 Ton', 'brand' => 'Daikin', 'unit' => 'pcs', 'cost' => 4200, 'sell' => 6000],
            ['name' => 'Copper Pipe 1/4"', 'brand' => 'Generic', 'unit' => 'mtr', 'cost' => 45, 'sell' => 75],
            ['name' => 'Copper Pipe 3/8"', 'brand' => 'Generic', 'unit' => 'mtr', 'cost' => 65, 'sell' => 100],
            ['name' => 'AC Capacitor 35uF', 'brand' => 'Generic', 'unit' => 'pcs', 'cost' => 120, 'sell' => 200],
            ['name' => 'AC Capacitor 45uF', 'brand' => 'Generic', 'unit' => 'pcs', 'cost' => 150, 'sell' => 240],
            ['name' => 'Condenser Fan Motor', 'brand' => 'Generic', 'unit' => 'pcs', 'cost' => 800, 'sell' => 1200],
            ['name' => 'Evaporator Fan Motor', 'brand' => 'Generic', 'unit' => 'pcs', 'cost' => 650, 'sell' => 950],
            ['name' => 'AC Remote Control', 'brand' => 'Universal', 'unit' => 'pcs', 'cost' => 200, 'sell' => 350],
            ['name' => 'PCB Board', 'brand' => 'Generic', 'unit' => 'pcs', 'cost' => 1500, 'sell' => 2200],
            ['name' => 'Thermostat', 'brand' => 'Generic', 'unit' => 'pcs', 'cost' => 250, 'sell' => 400],
            ['name' => 'AC Drain Pipe', 'brand' => 'Generic', 'unit' => 'mtr', 'cost' => 15, 'sell' => 30],
            ['name' => 'AC Insulation Tape', 'brand' => '3M', 'unit' => 'roll', 'cost' => 85, 'sell' => 140],
        ];

        // Services
        $services = [
            ['name' => 'Full Service - Motorcycle', 'sell' => 500],
            ['name' => 'Oil Change Service', 'sell' => 200],
            ['name' => 'Brake Service', 'sell' => 300],
            ['name' => 'Chain Cleaning & Lubrication', 'sell' => 150],
            ['name' => 'AC Gas Top-up', 'sell' => 800],
            ['name' => 'AC Cleaning Service', 'sell' => 600],
            ['name' => 'AC Installation', 'sell' => 1500],
            ['name' => 'AC Relocation', 'sell' => 1200],
            ['name' => 'AC Repair - Minor', 'sell' => 500],
            ['name' => 'AC Repair - Major', 'sell' => 1500],
        ];

        foreach ($motoParts as $part) {
            InventoryItem::create([
                'category' => 'moto',
                'name' => $part['name'],
                'brand' => $part['brand'],
                'sku' => 'MOTO-' . strtoupper(substr(md5($part['name']), 0, 6)),
                'unit' => $part['unit'],
                'quantity' => rand(5, 50),
                'cost_price' => $part['cost'],
                'sell_price' => $part['sell'],
                'low_stock_limit' => 5,
                'is_active' => true,
                'is_service' => false,
                'has_gst' => rand(0, 1) ? true : false, // Some items have GST
            ]);
        }

        foreach ($acParts as $part) {
            InventoryItem::create([
                'category' => 'ac',
                'name' => $part['name'],
                'brand' => $part['brand'],
                'sku' => 'AC-' . strtoupper(substr(md5($part['name']), 0, 6)),
                'unit' => $part['unit'],
                'quantity' => rand(3, 30),
                'cost_price' => $part['cost'],
                'sell_price' => $part['sell'],
                'low_stock_limit' => 3,
                'is_active' => true,
                'is_service' => false,
                'has_gst' => rand(0, 1) ? true : false, // Some items have GST
            ]);
        }

        foreach ($services as $service) {
            InventoryItem::create([
                'category' => $service['name'],
                'name' => $service['name'],
                'brand' => null,
                'sku' => 'SVC-' . strtoupper(substr(md5($service['name']), 0, 6)),
                'unit' => 'service',
                'quantity' => 0,
                'cost_price' => 0,
                'sell_price' => $service['sell'],
                'low_stock_limit' => 0,
                'is_active' => true,
                'is_service' => true,
                'has_gst' => false,
            ]);
        }
    }

    private function createJobs()
    {
        $customers = Customer::with('vehicles')->get();
        $userId = \App\Models\User::first()->id ?? 1;

        foreach ($customers->take(30) as $customer) {
            if ($customer->category === 'moto' || $customer->category === 'both') {
                $vehicle = $customer->vehicles->first();

                if ($vehicle) {
                    Job::create([
                        'job_type' => 'moto',
                        'job_category' => ['walkin', 'pickup'][array_rand(['walkin', 'pickup'])],
                        'customer_id' => $customer->id,
                        'vehicle_id' => $vehicle->id,
                        'address' => $customer->address,
                        'assigned_user_id' => $userId,
                        'status' => ['pending', 'in_progress', 'completed'][array_rand(['pending', 'in_progress', 'completed'])],
                        'payment_status' => 'unpaid',
                        'problem_description' => 'Regular service and maintenance',
                        'labour_total' => rand(200, 800),
                        'parts_total' => rand(300, 1500),
                        'travel_charges' => rand(0, 100),
                        'discount' => 0,
                        'total_amount' => rand(500, 2300),
                        'created_at' => now()->subDays(rand(1, 60)),
                    ]);
                }
            }
        }
    }

    private function createPettyCashTransactions()
    {
        $userId = \App\Models\User::first()->id ?? 1;
        $categories = ['fuel', 'parts', 'food', 'misc'];

        // Create topups
        for ($i = 0; $i < 10; $i++) {
            PettyCash::create([
                'user_id' => $userId,
                'type' => 'topup',
                'amount' => rand(5, 20) * 1000,
                'category' => 'Bank withdrawal',
                'purpose' => 'Petty cash top-up',
                'status' => 'approved',
                'approved_by' => $userId,
                'paid_at' => now()->subDays(rand(1, 90)),
                'created_at' => now()->subDays(rand(1, 90)),
            ]);
        }

        // Create expenses
        for ($i = 0; $i < 60; $i++) {
            PettyCash::create([
                'user_id' => $userId,
                'type' => 'expense',
                'amount' => rand(50, 500),
                'category' => $categories[array_rand($categories)],
                'purpose' => 'Daily operational expense',
                'status' => 'approved',
                'approved_by' => $userId,
                'paid_at' => now()->subDays(rand(1, 90)),
                'created_at' => now()->subDays(rand(1, 90)),
            ]);
        }
    }
}
