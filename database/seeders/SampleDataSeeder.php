<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\AcUnit;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\Job;
use App\Models\JobItem;
use App\Models\Payment;
use App\Models\DailySalesLog;
use App\Models\DailySalesLine;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\PettyCash;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\EmployeeLoan;
use App\Models\Lead;
use App\Models\LeadInteraction;
use App\Models\EodReconciliation;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SampleDataSeeder extends Seeder
{
    private array $users = [];
    private array $customers = [];
    private array $vehicles = [];
    private array $acUnits = [];
    private array $inventoryCategories = [];
    private array $motoItems = [];
    private array $acItems = [];
    private array $motoServices = [];
    private array $acServices = [];
    private array $expenseCategories = [];
    private array $vendors = [];
    private array $accounts = [];

    public function run(): void
    {
        $this->command->info('Creating users...');
        $this->createUsers();

        $this->command->info('Creating expense categories...');
        $this->createExpenseCategories();

        $this->command->info('Creating vendors...');
        $this->createVendors();

        $this->command->info('Creating accounts...');
        $this->createAccounts();

        $this->command->info('Creating customers, vehicles & AC units...');
        $this->createCustomers();

        $this->command->info('Creating inventory categories & items...');
        $this->createInventory();

        $this->command->info('Creating jobs with items & payments...');
        $this->createJobs();

        $this->command->info('Creating daily sales logs...');
        $this->createDailySalesLogs();

        $this->command->info('Creating expenses...');
        $this->createExpenses();

        $this->command->info('Creating petty cash transactions...');
        $this->createPettyCash();

        $this->command->info('Creating employees, salaries & loans...');
        $this->createEmployees();

        $this->command->info('Creating leads & interactions...');
        $this->createLeads();

        $this->command->info('Creating EOD reconciliations...');
        $this->createEodReconciliations();

        $this->command->info('Sample data seeded successfully!');
    }

    // ─── 1. Users ─────────────────────────────────────────────────

    private function createUsers(): void
    {
        $roles = [
            ['name' => 'Ahmed Shareef',    'email' => 'admin@test.com',          'role' => 'admin'],
            ['name' => 'Mohamed Naseer',   'email' => 'manager@test.com',        'role' => 'manager'],
            ['name' => 'Ali Rasheed',      'email' => 'moto_mechanic@test.com',  'role' => 'moto_mechanic'],
            ['name' => 'Hassan Waheed',    'email' => 'ac_mechanic@test.com',    'role' => 'ac_mechanic'],
            ['name' => 'Fathimath Shiuna', 'email' => 'cashier@test.com',        'role' => 'cashier'],
            ['name' => 'Ibrahim Fazeel',   'email' => 'hr@test.com',             'role' => 'hr'],
            ['name' => 'Aminath Leena',    'email' => 'customer@test.com',       'role' => 'customer'],
        ];

        foreach ($roles as $data) {
            $this->users[$data['role']] = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'phone'    => '7' . rand(100000, 999999),
                    'password' => Hash::make('password'),
                    'role'     => $data['role'],
                ]
            );
        }
    }

    // ─── 2. Expense Categories ────────────────────────────────────

    private function createExpenseCategories(): void
    {
        $categories = [
            // COGS
            ['name' => 'Spare Parts Purchase',  'type' => 'cogs'],
            ['name' => 'Refrigerant Gas',       'type' => 'cogs'],
            ['name' => 'Consumables',            'type' => 'cogs'],
            // Operating
            ['name' => 'Rent',                   'type' => 'operating'],
            ['name' => 'Utilities',              'type' => 'operating'],
            ['name' => 'Transportation',         'type' => 'operating'],
            ['name' => 'Office Supplies',        'type' => 'operating'],
            ['name' => 'Marketing',              'type' => 'operating'],
            ['name' => 'Miscellaneous',          'type' => 'other'],
        ];

        foreach ($categories as $cat) {
            $this->expenseCategories[] = ExpenseCategory::firstOrCreate(
                ['name' => $cat['name']],
                ['type' => $cat['type'], 'is_active' => true]
            );
        }
    }

    // ─── 3. Vendors ───────────────────────────────────────────────

    private function createVendors(): void
    {
        $vendorData = [
            ['name' => 'Malé Auto Parts Pvt Ltd',       'contact_name' => 'Ismail Hameed',   'phone' => '7700101'],
            ['name' => 'Cool Breeze Trading',            'contact_name' => 'Abdulla Riyaz',   'phone' => '7700202'],
            ['name' => 'Island Motor Supplies',          'contact_name' => 'Hussain Naeem',   'phone' => '7700303'],
            ['name' => 'Dhivehi Refrigeration Co.',      'contact_name' => 'Moosa Anwar',     'phone' => '7700404'],
            ['name' => 'Sun Hardware & Tools',            'contact_name' => 'Ahmed Shifau',    'phone' => '7700505'],
        ];

        foreach ($vendorData as $v) {
            $this->vendors[] = Vendor::firstOrCreate(
                ['name' => $v['name']],
                ['phone' => $v['phone'], 'contact_name' => $v['contact_name'], 'address' => 'Malé, Maldives', 'is_active' => true]
            );
        }
    }

    // ─── 4. Accounts ─────────────────────────────────────────────

    private function createAccounts(): void
    {
        $accts = [
            ['name' => 'BML Business Account',  'type' => 'business',  'balance' => 45000],
            ['name' => 'Petty Cash Float',       'type' => 'business',  'balance' => 5000],
            ['name' => 'Owner Personal',         'type' => 'personal',  'balance' => 12000],
        ];

        foreach ($accts as $a) {
            $this->accounts[] = Account::firstOrCreate(
                ['name' => $a['name']],
                ['type' => $a['type'], 'is_active' => true, 'is_system' => false, 'balance' => $a['balance']]
            );
        }
    }

    // ─── 5. Customers + Vehicles + AC Units ───────────────────────

    private function createCustomers(): void
    {
        $firstNames = [
            'Ahmed', 'Mohamed', 'Ali', 'Hassan', 'Ibrahim', 'Hussain', 'Ismail', 'Abdulla',
            'Moosa', 'Rasheed', 'Shahid', 'Imran', 'Naeem', 'Sameer', 'Asif', 'Riyaz',
            'Aminath', 'Fathimath', 'Aishath', 'Mariyam', 'Hawwa', 'Khadeeja', 'Nashida',
            'Zara', 'Laila', 'Nisha', 'Muna', 'Shifa', 'Hana', 'Sara',
        ];

        $lastNames = [
            'Shareef', 'Naseer', 'Waheed', 'Fazeel', 'Hameed', 'Riyaz', 'Anwar', 'Shifau',
            'Naeem', 'Manik', 'Fulhu', 'Didi', 'Maniku', 'Thakuru', 'Kalefaan',
        ];

        $roads = [
            'Boduthakurufaanu Magu', 'Majeedhee Magu', 'Ameenee Magu', 'Orchid Magu',
            'Sosun Magu', 'Lily Magu', 'Violet Magu', 'Rahdhebai Magu', 'Buruzu Magu',
            'Fareedhee Magu', 'Medhuziyaarai Magu', 'Chaandhanee Magu',
        ];

        $areas = ['Malé', 'Hulhumalé Phase 1', 'Hulhumalé Phase 2', 'Vilimalé'];

        $vehicleBrands  = ['Honda', 'Yamaha', 'Suzuki', 'TVS', 'Hero', 'Bajaj'];
        $vehicleModels  = [
            'Honda'  => ['Wave 125', 'CB150R', 'PCX 160', 'Scoopy'],
            'Yamaha' => ['YZF-R15', 'NMAX', 'Aerox 155', 'FZ-S'],
            'Suzuki' => ['Gixxer SF', 'Address 110', 'Burgman Street'],
            'TVS'    => ['Apache RTR 160', 'Jupiter', 'Ntorq'],
            'Hero'   => ['Splendor Plus', 'Glamour', 'Destini 125'],
            'Bajaj'  => ['Pulsar NS200', 'Platina', 'CT 125X'],
        ];

        $acBrands = ['Daikin', 'Panasonic', 'Midea', 'Gree', 'LG', 'Samsung'];
        $btuOptions = ['9000', '12000', '18000', '24000'];
        $gasTypes   = ['R410A', 'R22', 'R32'];

        // Category distribution: 18 moto, 12 ac, 10 both
        $categoryPool = array_merge(
            array_fill(0, 18, 'moto'),
            array_fill(0, 12, 'ac'),
            array_fill(0, 10, 'both')
        );
        shuffle($categoryPool);

        for ($i = 0; $i < 40; $i++) {
            $first = $firstNames[array_rand($firstNames)];
            $last  = $lastNames[array_rand($lastNames)];
            $name  = $first . ' ' . $last;

            $phonePrefix = rand(0, 1) ? '7' : '9';
            $phone = $phonePrefix . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);

            $category = $categoryPool[$i];

            $customer = Customer::firstOrCreate(
                ['phone' => $phone],
                [
                    'name'     => $name,
                    'email'    => rand(0, 2) > 0 ? strtolower($first) . '.' . strtolower($last) . '@gmail.com' : null,
                    'address'  => $roads[array_rand($roads)] . ', ' . $areas[array_rand($areas)],
                    'category' => $category,
                    'notes'    => rand(0, 3) === 0 ? 'Regular customer' : null,
                ]
            );
            $this->customers[] = $customer;

            // Vehicles for moto / both
            if (in_array($category, ['moto', 'both'])) {
                $brand = $vehicleBrands[array_rand($vehicleBrands)];
                $models = $vehicleModels[$brand];

                $vehicle = Vehicle::create([
                    'customer_id'         => $customer->id,
                    'brand'               => $brand,
                    'model'               => $models[array_rand($models)],
                    'registration_number' => strtoupper(chr(rand(65, 90))) . rand(1000, 9999),
                    'year'                => rand(2017, 2025),
                    'mileage'             => rand(3000, 60000),
                ]);
                $this->vehicles[] = $vehicle;
            }

            // AC units for ac / both
            if (in_array($category, ['ac', 'both'])) {
                $acUnit = AcUnit::create([
                    'customer_id'         => $customer->id,
                    'brand'               => $acBrands[array_rand($acBrands)],
                    'btu'                 => $btuOptions[array_rand($btuOptions)],
                    'gas_type'            => $gasTypes[array_rand($gasTypes)],
                    'indoor_units'        => rand(1, 3),
                    'outdoor_units'       => 1,
                    'last_service_at'     => now()->subDays(rand(30, 365)),
                    'location_description'=> $areas[array_rand($areas)] . ', Floor ' . rand(1, 8),
                ]);
                $this->acUnits[] = $acUnit;
            }
        }
    }

    // ─── 6. Inventory Categories + Items ──────────────────────────

    private function createInventory(): void
    {
        // Categories
        $cats = [
            ['name' => 'Engine Parts',            'type' => 'moto'],
            ['name' => 'Electrical & Lighting',    'type' => 'moto'],
            ['name' => 'Tyres & Brakes',           'type' => 'moto'],
            ['name' => 'Refrigerants & Gas',        'type' => 'ac'],
            ['name' => 'Compressors & Motors',      'type' => 'ac'],
            ['name' => 'AC Accessories',             'type' => 'ac'],
            ['name' => 'Tools & Consumables',        'type' => 'both'],
        ];

        foreach ($cats as $c) {
            $this->inventoryCategories[$c['name']] = InventoryCategory::firstOrCreate(
                ['name' => $c['name']],
                ['type' => $c['type'], 'is_active' => true]
            );
        }

        // ── Moto parts ──
        $motoParts = [
            ['name' => 'Engine Oil 10W-40',     'brand' => 'Castrol',  'unit' => 'ltr', 'cost' => 85,  'sell' => 120, 'cat' => 'Engine Parts'],
            ['name' => 'Engine Oil 20W-50',     'brand' => 'Shell',    'unit' => 'ltr', 'cost' => 90,  'sell' => 130, 'cat' => 'Engine Parts'],
            ['name' => 'Air Filter',            'brand' => 'KN',       'unit' => 'pcs', 'cost' => 150, 'sell' => 220, 'cat' => 'Engine Parts'],
            ['name' => 'Fuel Filter',           'brand' => 'Mann',     'unit' => 'pcs', 'cost' => 55,  'sell' => 95,  'cat' => 'Engine Parts'],
            ['name' => 'Spark Plug',            'brand' => 'NGK',      'unit' => 'pcs', 'cost' => 45,  'sell' => 75,  'cat' => 'Engine Parts'],
            ['name' => 'Chain Sprocket Kit',    'brand' => 'DID',      'unit' => 'set', 'cost' => 800, 'sell' => 1200,'cat' => 'Engine Parts'],
            ['name' => 'Clutch Cable',          'brand' => 'Genuine',  'unit' => 'pcs', 'cost' => 80,  'sell' => 130, 'cat' => 'Engine Parts'],
            ['name' => 'Throttle Cable',        'brand' => 'Genuine',  'unit' => 'pcs', 'cost' => 75,  'sell' => 120, 'cat' => 'Engine Parts'],
            ['name' => 'Brake Pads Front',      'brand' => 'EBC',      'unit' => 'set', 'cost' => 250, 'sell' => 350, 'cat' => 'Tyres & Brakes'],
            ['name' => 'Brake Pads Rear',       'brand' => 'EBC',      'unit' => 'set', 'cost' => 200, 'sell' => 300, 'cat' => 'Tyres & Brakes'],
            ['name' => 'Front Tyre 90/90-17',   'brand' => 'MRF',      'unit' => 'pcs', 'cost' => 600, 'sell' => 850, 'cat' => 'Tyres & Brakes'],
            ['name' => 'Rear Tyre 120/80-17',   'brand' => 'MRF',      'unit' => 'pcs', 'cost' => 750, 'sell' => 1050,'cat' => 'Tyres & Brakes'],
            ['name' => 'Battery 12V 7Ah',       'brand' => 'Yuasa',    'unit' => 'pcs', 'cost' => 350, 'sell' => 500, 'cat' => 'Electrical & Lighting'],
            ['name' => 'Head Light Bulb H4',    'brand' => 'Philips',  'unit' => 'pcs', 'cost' => 120, 'sell' => 180, 'cat' => 'Electrical & Lighting'],
            ['name' => 'Tail Light Bulb',       'brand' => 'Philips',  'unit' => 'pcs', 'cost' => 35,  'sell' => 60,  'cat' => 'Electrical & Lighting'],
            ['name' => 'Side Mirror (pair)',     'brand' => 'Generic',  'unit' => 'set', 'cost' => 250, 'sell' => 400, 'cat' => 'Electrical & Lighting'],
            ['name' => 'Clutch Lever',          'brand' => 'Generic',  'unit' => 'pcs', 'cost' => 100, 'sell' => 170, 'cat' => 'Engine Parts'],
            ['name' => 'Brake Lever',           'brand' => 'Generic',  'unit' => 'pcs', 'cost' => 100, 'sell' => 170, 'cat' => 'Tyres & Brakes'],
            ['name' => 'Speedometer Cable',     'brand' => 'Generic',  'unit' => 'pcs', 'cost' => 90,  'sell' => 150, 'cat' => 'Electrical & Lighting'],
            ['name' => 'CDI Unit',              'brand' => 'Generic',  'unit' => 'pcs', 'cost' => 300, 'sell' => 480, 'cat' => 'Electrical & Lighting'],
        ];

        foreach ($motoParts as $p) {
            $item = InventoryItem::firstOrCreate(
                ['name' => $p['name'], 'category' => 'moto'],
                [
                    'inventory_category_id' => $this->inventoryCategories[$p['cat']]->id,
                    'brand'                 => $p['brand'],
                    'sku'                   => 'MOTO-' . strtoupper(substr(md5($p['name']), 0, 6)),
                    'unit'                  => $p['unit'],
                    'quantity'              => rand(5, 50),
                    'cost_price'            => $p['cost'],
                    'sell_price'            => $p['sell'],
                    'low_stock_limit'       => 5,
                    'is_active'             => true,
                    'is_service'            => false,
                    'has_gst'               => rand(0, 1) ? true : false,
                ]
            );
            $this->motoItems[] = $item;
        }

        // ── AC parts ──
        $acParts = [
            ['name' => 'R410A Refrigerant Gas',    'brand' => 'Honeywell',  'unit' => 'kg',  'cost' => 400,  'sell' => 600,  'cat' => 'Refrigerants & Gas'],
            ['name' => 'R22 Refrigerant Gas',      'brand' => 'DuPont',     'unit' => 'kg',  'cost' => 350,  'sell' => 550,  'cat' => 'Refrigerants & Gas'],
            ['name' => 'R32 Refrigerant Gas',      'brand' => 'Honeywell',  'unit' => 'kg',  'cost' => 420,  'sell' => 650,  'cat' => 'Refrigerants & Gas'],
            ['name' => 'AC Compressor 1.5T',       'brand' => 'Panasonic',  'unit' => 'pcs', 'cost' => 3500, 'sell' => 5000, 'cat' => 'Compressors & Motors'],
            ['name' => 'AC Compressor 2T',         'brand' => 'Daikin',     'unit' => 'pcs', 'cost' => 4200, 'sell' => 6000, 'cat' => 'Compressors & Motors'],
            ['name' => 'Condenser Fan Motor',      'brand' => 'Generic',    'unit' => 'pcs', 'cost' => 800,  'sell' => 1200, 'cat' => 'Compressors & Motors'],
            ['name' => 'Evaporator Fan Motor',     'brand' => 'Generic',    'unit' => 'pcs', 'cost' => 650,  'sell' => 950,  'cat' => 'Compressors & Motors'],
            ['name' => 'Copper Pipe 1/4"',         'brand' => 'Generic',    'unit' => 'mtr', 'cost' => 45,   'sell' => 75,   'cat' => 'AC Accessories'],
            ['name' => 'Copper Pipe 3/8"',         'brand' => 'Generic',    'unit' => 'mtr', 'cost' => 65,   'sell' => 100,  'cat' => 'AC Accessories'],
            ['name' => 'AC Capacitor 35uF',        'brand' => 'Generic',    'unit' => 'pcs', 'cost' => 120,  'sell' => 200,  'cat' => 'AC Accessories'],
            ['name' => 'AC Remote Control',        'brand' => 'Universal',  'unit' => 'pcs', 'cost' => 200,  'sell' => 350,  'cat' => 'AC Accessories'],
            ['name' => 'PCB Board',                'brand' => 'Generic',    'unit' => 'pcs', 'cost' => 1500, 'sell' => 2200, 'cat' => 'AC Accessories'],
            ['name' => 'Thermostat Sensor',        'brand' => 'Generic',    'unit' => 'pcs', 'cost' => 250,  'sell' => 400,  'cat' => 'AC Accessories'],
            ['name' => 'AC Drain Pipe',            'brand' => 'Generic',    'unit' => 'mtr', 'cost' => 15,   'sell' => 30,   'cat' => 'AC Accessories'],
            ['name' => 'AC Insulation Tape',       'brand' => '3M',         'unit' => 'roll','cost' => 85,   'sell' => 140,  'cat' => 'AC Accessories'],
        ];

        foreach ($acParts as $p) {
            $item = InventoryItem::firstOrCreate(
                ['name' => $p['name'], 'category' => 'ac'],
                [
                    'inventory_category_id' => $this->inventoryCategories[$p['cat']]->id,
                    'brand'                 => $p['brand'],
                    'sku'                   => 'AC-' . strtoupper(substr(md5($p['name']), 0, 6)),
                    'unit'                  => $p['unit'],
                    'quantity'              => rand(3, 30),
                    'cost_price'            => $p['cost'],
                    'sell_price'            => $p['sell'],
                    'low_stock_limit'       => 3,
                    'is_active'             => true,
                    'is_service'            => false,
                    'has_gst'               => rand(0, 1) ? true : false,
                ]
            );
            $this->acItems[] = $item;
        }

        // ── Services ──
        $services = [
            ['name' => 'Full Service - Motorcycle', 'sell' => 500,  'cat' => 'moto'],
            ['name' => 'Oil Change Service',         'sell' => 200,  'cat' => 'moto'],
            ['name' => 'Brake Service',              'sell' => 300,  'cat' => 'moto'],
            ['name' => 'Chain Cleaning & Lube',      'sell' => 150,  'cat' => 'moto'],
            ['name' => 'Electrical Diagnostics',     'sell' => 250,  'cat' => 'moto'],
            ['name' => 'AC Gas Top-up',              'sell' => 800,  'cat' => 'ac'],
            ['name' => 'AC Cleaning Service',        'sell' => 600,  'cat' => 'ac'],
            ['name' => 'AC Installation',            'sell' => 1500, 'cat' => 'ac'],
            ['name' => 'AC Relocation',              'sell' => 1200, 'cat' => 'ac'],
            ['name' => 'AC Repair - Major',          'sell' => 1500, 'cat' => 'ac'],
        ];

        foreach ($services as $s) {
            $item = InventoryItem::firstOrCreate(
                ['name' => $s['name'], 'category' => $s['cat']],
                [
                    'brand'      => null,
                    'sku'        => 'SVC-' . strtoupper(substr(md5($s['name']), 0, 6)),
                    'unit'       => 'service',
                    'quantity'   => 0,
                    'cost_price' => 0,
                    'sell_price' => $s['sell'],
                    'low_stock_limit' => 0,
                    'is_active'  => true,
                    'is_service' => true,
                    'has_gst'    => false,
                ]
            );

            if ($s['cat'] === 'moto') {
                $this->motoServices[] = $item;
            } else {
                $this->acServices[] = $item;
            }
        }
    }

    // ─── 7. Jobs + JobItems + Payments ────────────────────────────

    private function createJobs(): void
    {
        $admin   = $this->users['admin'];
        $manager = $this->users['manager'];
        $motoMech = $this->users['moto_mechanic'];
        $acMech   = $this->users['ac_mechanic'];

        $motoCustomers = collect($this->customers)->filter(fn($c) => in_array($c->category, ['moto', 'both']));
        $acCustomers   = collect($this->customers)->filter(fn($c) => in_array($c->category, ['ac', 'both']));

        $motoProblems = [
            'Engine making rattling noise',
            'Brakes squealing, need inspection',
            'Oil change and general service',
            'Chain adjustment and lubrication',
            'Battery not holding charge',
            'Headlight flickering',
            'Clutch slipping during acceleration',
            'Front tyre worn, needs replacement',
        ];

        $acProblems = [
            'AC not cooling properly',
            'Water leaking from indoor unit',
            'Compressor making loud noise',
            'Remote control not working',
            'AC gas needs top-up',
            'New AC installation required',
            'AC relocation to new room',
            'Strange smell when AC turns on',
        ];

        $statuses = ['new', 'scheduled', 'in_progress', 'completed', 'completed', 'completed', 'cancelled'];
        $priorities = ['normal', 'normal', 'normal', 'high', 'urgent', 'low'];

        // ── 18 Moto jobs ──
        foreach ($motoCustomers->take(18) as $idx => $customer) {
            $vehicle = collect($this->vehicles)->first(fn($v) => $v->customer_id === $customer->id);
            if (!$vehicle) continue;

            $status = $statuses[array_rand($statuses)];
            $daysAgo = rand(1, 60);
            $createdAt = now()->subDays($daysAgo);

            $job = Job::create([
                'job_date'            => $createdAt->toDateString(),
                'job_type'            => 'moto',
                'job_category'        => ['walkin', 'pickup', 'general'][array_rand(['walkin', 'pickup', 'general'])],
                'title'               => 'Moto Job #' . ($idx + 1),
                'customer_id'         => $customer->id,
                'customer_name'       => $customer->name,
                'customer_phone'      => $customer->phone,
                'customer_email'      => $customer->email,
                'vehicle_id'          => $vehicle->id,
                'address'             => $customer->address,
                'assigned_user_id'    => $motoMech->id,
                'status'              => $status,
                'priority'            => $priorities[array_rand($priorities)],
                'payment_status'      => 'unpaid',
                'problem_description' => $motoProblems[array_rand($motoProblems)],
                'travel_charges'      => rand(0, 1) ? rand(50, 200) : 0,
                'discount'            => rand(0, 3) === 0 ? rand(50, 200) : 0,
                'created_at'          => $createdAt,
                'scheduled_at'        => in_array($status, ['scheduled', 'in_progress', 'completed']) ? $createdAt->copy()->addDays(rand(0, 3)) : null,
                'started_at'          => in_array($status, ['in_progress', 'completed']) ? $createdAt->copy()->addDays(rand(1, 3)) : null,
                'completed_at'        => $status === 'completed' ? $createdAt->copy()->addDays(rand(2, 5)) : null,
                'cancelled_at'        => $status === 'cancelled' ? $createdAt->copy()->addDay() : null,
                'cancellation_reason' => $status === 'cancelled' ? 'customer_request' : null,
            ]);

            $this->addJobItems($job, 'moto');
            $job->recalculateTotals();

            // Add payment for completed jobs
            if ($status === 'completed') {
                $this->addJobPayment($job);
            }
        }

        // ── 12 AC jobs ──
        foreach ($acCustomers->take(12) as $idx => $customer) {
            $acUnit = collect($this->acUnits)->first(fn($u) => $u->customer_id === $customer->id);

            $status = $statuses[array_rand($statuses)];
            $daysAgo = rand(1, 60);
            $createdAt = now()->subDays($daysAgo);

            $job = Job::create([
                'job_date'            => $createdAt->toDateString(),
                'job_type'            => 'ac',
                'job_category'        => ['walkin', 'general'][array_rand(['walkin', 'general'])],
                'title'               => 'AC Job #' . ($idx + 1),
                'customer_id'         => $customer->id,
                'customer_name'       => $customer->name,
                'customer_phone'      => $customer->phone,
                'customer_email'      => $customer->email,
                'ac_unit_id'          => $acUnit?->id,
                'address'             => $customer->address,
                'location'            => $acUnit?->location_description,
                'assigned_user_id'    => $acMech->id,
                'status'              => $status,
                'priority'            => $priorities[array_rand($priorities)],
                'payment_status'      => 'unpaid',
                'problem_description' => $acProblems[array_rand($acProblems)],
                'travel_charges'      => rand(0, 1) ? rand(50, 300) : 0,
                'discount'            => rand(0, 4) === 0 ? rand(100, 300) : 0,
                'created_at'          => $createdAt,
                'scheduled_at'        => in_array($status, ['scheduled', 'in_progress', 'completed']) ? $createdAt->copy()->addDays(rand(0, 3)) : null,
                'started_at'          => in_array($status, ['in_progress', 'completed']) ? $createdAt->copy()->addDays(rand(1, 3)) : null,
                'completed_at'        => $status === 'completed' ? $createdAt->copy()->addDays(rand(2, 7)) : null,
                'cancelled_at'        => $status === 'cancelled' ? $createdAt->copy()->addDay() : null,
                'cancellation_reason' => $status === 'cancelled' ? 'parts_unavailable' : null,
            ]);

            $this->addJobItems($job, 'ac');
            $job->recalculateTotals();

            if ($status === 'completed') {
                $this->addJobPayment($job);
            }
        }
    }

    private function addJobItems(Job $job, string $type): void
    {
        $parts    = $type === 'moto' ? $this->motoItems : $this->acItems;
        $services = $type === 'moto' ? $this->motoServices : $this->acServices;

        // 1-3 parts
        $selectedParts = collect($parts)->random(min(rand(1, 3), count($parts)));
        foreach ($selectedParts as $part) {
            $qty = rand(1, 3);
            JobItem::create([
                'job_id'            => $job->id,
                'inventory_item_id' => $part->id,
                'item_name'         => $part->name,
                'is_service'        => false,
                'quantity'          => $qty,
                'unit_price'        => $part->sell_price,
                'subtotal'          => $qty * (float) $part->sell_price,
            ]);
        }

        // 1 service
        $service = $services[array_rand($services)];
        JobItem::create([
            'job_id'            => $job->id,
            'inventory_item_id' => $service->id,
            'item_name'         => $service->name,
            'is_service'        => true,
            'quantity'          => 1,
            'unit_price'        => $service->sell_price,
            'subtotal'          => (float) $service->sell_price,
        ]);
    }

    private function addJobPayment(Job $job): void
    {
        $total = (float) $job->total_amount;
        if ($total <= 0) return;

        // 70% fully paid, 30% partial
        $isFullyPaid = rand(1, 10) <= 7;
        $amount = $isFullyPaid ? $total : round($total * (rand(40, 80) / 100), 2);

        Payment::create([
            'job_id'    => $job->id,
            'amount'    => $amount,
            'method'    => ['cash', 'cash', 'transfer'][array_rand(['cash', 'cash', 'transfer'])],
            'reference' => rand(0, 1) ? 'REF-' . rand(10000, 99999) : null,
            'status'    => 'completed',
        ]);

        $job->updatePaymentStatus();
    }

    // ─── 8. Daily Sales Logs + Lines ──────────────────────────────

    private function createDailySalesLogs(): void
    {
        $admin = $this->users['admin'];
        $manager = $this->users['manager'];

        // 5 moto, 5 cool — spread over last 14 days
        $units = ['moto', 'cool', 'moto', 'cool', 'moto', 'cool', 'moto', 'cool', 'moto', 'cool'];

        for ($i = 0; $i < 10; $i++) {
            $daysAgo = $i + 1; // unique dates
            $unit = $units[$i];
            $isDraft = $i >= 7; // last 3 are draft

            $log = DailySalesLog::create([
                'date'          => now()->subDays($daysAgo)->toDateString(),
                'business_unit' => $unit,
                'status'        => $isDraft ? 'draft' : 'submitted',
                'created_by'    => $manager->id,
                'submitted_at'  => $isDraft ? null : now()->subDays($daysAgo),
                'submitted_by'  => $isDraft ? null : $manager->id,
                'customer_id'   => rand(0, 1) ? $this->customers[array_rand($this->customers)]->id : null,
                'payment_method'=> $isDraft ? null : ['cash', 'transfer'][array_rand(['cash', 'transfer'])],
                'notes'         => rand(0, 1) ? 'Walk-in customer' : null,
            ]);

            // 2-4 lines per log
            $items = $unit === 'moto' ? $this->motoItems : $this->acItems;
            $lineCount = rand(2, 4);
            $selectedItems = collect($items)->random(min($lineCount, count($items)));

            foreach ($selectedItems as $item) {
                $qty = rand(1, 3);
                $unitPrice = (float) $item->sell_price;
                $lineTotal = $qty * $unitPrice;
                $isGst = (bool) $item->has_gst;
                $gstAmount = $isGst ? round($lineTotal * 0.08, 2) : 0;

                DailySalesLine::create([
                    'daily_sales_log_id' => $log->id,
                    'inventory_item_id'  => $item->id,
                    'description'        => $item->name,
                    'qty'                => $qty,
                    'unit_price'         => $unitPrice,
                    'line_total'         => $lineTotal,
                    'is_stock_item'      => true,
                    'is_gst_applicable'  => $isGst,
                    'gst_amount'         => $gstAmount,
                    'payment_method'     => $isDraft ? 'cash' : $log->payment_method,
                ]);
            }

            // Occasionally add a service line
            if (rand(0, 1)) {
                $svcItems = $unit === 'moto' ? $this->motoServices : $this->acServices;
                $svc = $svcItems[array_rand($svcItems)];
                $svcPrice = (float) $svc->sell_price;

                DailySalesLine::create([
                    'daily_sales_log_id' => $log->id,
                    'inventory_item_id'  => $svc->id,
                    'description'        => $svc->name,
                    'qty'                => 1,
                    'unit_price'         => $svcPrice,
                    'line_total'         => $svcPrice,
                    'is_stock_item'      => false,
                    'is_gst_applicable'  => false,
                    'gst_amount'         => 0,
                    'payment_method'     => $isDraft ? 'cash' : $log->payment_method,
                ]);
            }
        }
    }

    // ─── 9. Expenses ──────────────────────────────────────────────

    private function createExpenses(): void
    {
        $admin = $this->users['admin'];
        $businessUnits = ['moto', 'ac', 'shared'];

        $descriptions = [
            'cogs'      => ['Parts restock', 'Gas cylinder purchase', 'Consumables order', 'Bulk parts order'],
            'operating' => ['Monthly rent', 'Electricity bill', 'Fuel for delivery', 'Staff lunch', 'Internet bill', 'Cleaning supplies'],
            'other'     => ['Office chair replacement', 'Parking fee', 'Government permit fee'],
        ];

        for ($i = 0; $i < 20; $i++) {
            $cat = $this->expenseCategories[array_rand($this->expenseCategories)];
            $unit = $businessUnits[array_rand($businessUnits)];
            $daysAgo = rand(1, 60);
            $descs = $descriptions[$cat->type] ?? $descriptions['other'];

            Expense::create([
                'expense_category_id' => $cat->id,
                'vendor_id'           => rand(0, 1) ? $this->vendors[array_rand($this->vendors)]->id : null,
                'account_id'          => $this->accounts[0]->id,
                'business_unit'       => $unit,
                'amount'              => rand(100, 8000),
                'incurred_at'         => now()->subDays($daysAgo),
                'vendor'              => $this->vendors[array_rand($this->vendors)]->name,
                'reference'           => rand(0, 1) ? 'INV-' . rand(1000, 9999) : null,
                'notes'               => $descs[array_rand($descs)],
                'created_by'          => $admin->id,
            ]);
        }
    }

    // ─── 10. Petty Cash ───────────────────────────────────────────

    private function createPettyCash(): void
    {
        $admin    = $this->users['admin'];
        $motoMech = $this->users['moto_mechanic'];
        $acMech   = $this->users['ac_mechanic'];
        $manager  = $this->users['manager'];

        $assignees = [$motoMech, $acMech, $manager];

        // 5 top-ups
        foreach ($assignees as $user) {
            PettyCash::create([
                'user_id'     => $admin->id,
                'assigned_to' => $user->id,
                'type'        => 'topup',
                'amount'      => rand(3, 10) * 1000,
                'category'    => 'Bank withdrawal',
                'purpose'     => 'Petty cash top-up for ' . $user->name,
                'status'      => 'approved',
                'approved_by' => $admin->id,
                'paid_at'     => now()->subDays(rand(20, 50)),
            ]);
        }

        // Add extra top-ups for moto & ac
        foreach ([$motoMech, $acMech] as $user) {
            PettyCash::create([
                'user_id'     => $admin->id,
                'assigned_to' => $user->id,
                'type'        => 'topup',
                'amount'      => rand(2, 5) * 1000,
                'category'    => 'Bank withdrawal',
                'purpose'     => 'Additional float',
                'status'      => 'approved',
                'approved_by' => $admin->id,
                'paid_at'     => now()->subDays(rand(5, 15)),
            ]);
        }

        // 8 expenses — mix of approved & pending
        $purposes = [
            'Fuel for delivery bike', 'Bought screws from hardware', 'Lunch for team',
            'Cable ties and tape', 'Parking fees', 'Customer pickup transport',
            'Small spare part purchase', 'Drinking water for shop',
        ];

        for ($i = 0; $i < 8; $i++) {
            $assignee = $assignees[array_rand($assignees)];
            $isPending = $i >= 6; // last 2 pending

            PettyCash::create([
                'user_id'     => $assignee->id,
                'assigned_to' => $assignee->id,
                'type'        => 'expense',
                'amount'      => rand(30, 500),
                'category'    => ['fuel', 'parts', 'food', 'misc'][array_rand(['fuel', 'parts', 'food', 'misc'])],
                'purpose'     => $purposes[$i],
                'status'      => $isPending ? 'pending' : 'approved',
                'approved_by' => $isPending ? null : $admin->id,
                'paid_at'     => $isPending ? null : now()->subDays(rand(1, 30)),
            ]);
        }
    }

    // ─── 11. Employees + Salaries + Loans ─────────────────────────

    private function createEmployees(): void
    {
        $admin = $this->users['admin'];

        $employees = [
            [
                'employee_number' => 'EMP-001', 'company' => 'Micro Moto Garage', 'name' => 'Ali Rasheed',
                'type' => 'full-time', 'position' => 'Senior Mechanic', 'department' => 'Workshop',
                'nationality' => 'Maldivian', 'basic_salary' => 8000, 'hire_ago_months' => 18,
            ],
            [
                'employee_number' => 'EMP-002', 'company' => 'Micro Moto Garage', 'name' => 'Hussain Shameem',
                'type' => 'full-time', 'position' => 'Junior Mechanic', 'department' => 'Workshop',
                'nationality' => 'Maldivian', 'basic_salary' => 6000, 'hire_ago_months' => 8,
            ],
            [
                'employee_number' => 'EMP-003', 'company' => 'Micro Moto Garage', 'name' => 'Sunil Kumar',
                'type' => 'full-time', 'position' => 'Helper', 'department' => 'Workshop',
                'nationality' => 'Indian', 'basic_salary' => 4500, 'hire_ago_months' => 12,
            ],
            [
                'employee_number' => 'EMP-004', 'company' => 'Micro Cool', 'name' => 'Hassan Waheed',
                'type' => 'full-time', 'position' => 'AC Technician', 'department' => 'Service',
                'nationality' => 'Maldivian', 'basic_salary' => 9000, 'hire_ago_months' => 24,
            ],
            [
                'employee_number' => 'EMP-005', 'company' => 'Micro Cool', 'name' => 'Raju Thapa',
                'type' => 'full-time', 'position' => 'AC Helper', 'department' => 'Service',
                'nationality' => 'Nepali', 'basic_salary' => 4000, 'hire_ago_months' => 6,
            ],
            [
                'employee_number' => 'EMP-006', 'company' => 'Micro Moto Garage', 'name' => 'Mariyam Nisha',
                'type' => 'part-time', 'position' => 'Receptionist', 'department' => 'Admin',
                'nationality' => 'Maldivian', 'basic_salary' => 3500, 'hire_ago_months' => 4,
            ],
        ];

        foreach ($employees as $empData) {
            $hireDate = now()->subMonths($empData['hire_ago_months']);

            $emp = Employee::create([
                'employee_number'  => $empData['employee_number'],
                'company'          => $empData['company'],
                'name'             => $empData['name'],
                'phone'            => '7' . rand(100000, 999999),
                'type'             => $empData['type'],
                'position'         => $empData['position'],
                'department'       => $empData['department'],
                'nationality'      => $empData['nationality'],
                'hire_date'        => $hireDate,
                'status'           => 'active',
                'basic_salary'     => $empData['basic_salary'],
                'address'          => 'Malé, Maldives',
                'date_of_birth'    => Carbon::create(rand(1985, 2000), rand(1, 12), rand(1, 28)),
                'id_number'        => 'A' . rand(100000, 999999),
            ]);

            // Salary records for last 2 months
            for ($m = 1; $m <= 2; $m++) {
                $month = now()->subMonths($m)->month;
                $year  = now()->subMonths($m)->year;

                $basic = $empData['basic_salary'];
                $allowances = $empData['type'] === 'full-time' ? rand(500, 1500) : 0;
                $overtime = rand(0, 3) === 0 ? rand(200, 800) : 0;
                $absentDays = rand(0, 2);
                $dailyRate = $basic / 26;
                $absentDed = round($absentDays * $dailyRate, 2);
                $loanDed = 0; // will be updated if loan exists

                $gross = $basic + $allowances + $overtime;
                $totalDed = $loanDed + $absentDed;
                $net = $gross - $totalDed;

                EmployeeSalary::create([
                    'employee_id'       => $emp->id,
                    'month'             => $month,
                    'year'              => $year,
                    'basic_salary'      => $basic,
                    'allowances'        => $allowances,
                    'bonuses'           => 0,
                    'overtime'          => $overtime,
                    'loan_deduction'    => $loanDed,
                    'absent_days'       => $absentDays,
                    'absent_deduction'  => $absentDed,
                    'working_days'      => 26,
                    'prorated_deduction'=> 0,
                    'other_deductions'  => 0,
                    'gross_salary'      => $gross,
                    'total_deductions'  => $totalDed,
                    'net_salary'        => $net,
                    'status'            => 'paid',
                    'payment_date'      => Carbon::create($year, $month, 28),
                    'payment_method'    => 'transfer',
                ]);
            }

            // Loans for first two employees
            if (in_array($empData['employee_number'], ['EMP-001', 'EMP-004'])) {
                $loanAmount = $empData['employee_number'] === 'EMP-001' ? 5000 : 8000;
                $monthlyDed = $empData['employee_number'] === 'EMP-001' ? 1000 : 1500;
                $remaining = $loanAmount - ($monthlyDed * 2); // 2 months deducted

                EmployeeLoan::create([
                    'employee_id'        => $emp->id,
                    'loan_type'          => 'salary_advance',
                    'amount'             => $loanAmount,
                    'remaining_balance'  => max(0, $remaining),
                    'monthly_deduction'  => $monthlyDed,
                    'loan_date'          => now()->subMonths(3),
                    'start_deduction_date'=> now()->subMonths(2),
                    'status'             => $remaining > 0 ? 'active' : 'completed',
                    'approved_by'        => $admin->id,
                    'approved_date'      => now()->subMonths(3),
                    'reason'             => 'Personal expenses',
                ]);
            }
        }
    }

    // ─── 12. Leads + Interactions ─────────────────────────────────

    private function createLeads(): void
    {
        $admin   = $this->users['admin'];
        $manager = $this->users['manager'];

        $leadData = [
            ['name' => 'Yoosuf Ibrahim',    'status' => 'new',        'interest' => 'moto', 'source' => 'walk-in'],
            ['name' => 'Zakariyya Moosa',   'status' => 'new',        'interest' => 'ac',   'source' => 'phone'],
            ['name' => 'Hamza Umar',        'status' => 'contacted',  'interest' => 'moto', 'source' => 'referral'],
            ['name' => 'Bilal Khalid',      'status' => 'contacted',  'interest' => 'ac',   'source' => 'social_media'],
            ['name' => 'Faisal Abdulla',    'status' => 'interested', 'interest' => 'moto', 'source' => 'walk-in'],
            ['name' => 'Zainab Hafsa',      'status' => 'interested', 'interest' => 'ac',   'source' => 'phone'],
            ['name' => 'Rukhsar Nadiya',    'status' => 'qualified',  'interest' => 'both', 'source' => 'referral'],
            ['name' => 'Safia Reema',       'status' => 'converted',  'interest' => 'moto', 'source' => 'walk-in'],
            ['name' => 'Sofiya Laila',      'status' => 'lost',       'interest' => 'ac',   'source' => 'phone'],
            ['name' => 'Raisa Faiza',       'status' => 'lost',       'interest' => 'moto', 'source' => 'social_media'],
        ];

        $interactionTypes = ['call', 'whatsapp', 'visit', 'email', 'other'];

        foreach ($leadData as $ld) {
            $daysAgo = rand(3, 45);
            $phone = '9' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);

            $lead = Lead::create([
                'name'            => $ld['name'],
                'phone'           => $phone,
                'email'           => rand(0, 1) ? strtolower(str_replace(' ', '.', $ld['name'])) . '@gmail.com' : null,
                'source'          => $ld['source'],
                'status'          => $ld['status'],
                'priority'        => ['low', 'medium', 'high'][array_rand(['low', 'medium', 'high'])],
                'interested_in'   => $ld['interest'],
                'notes'           => 'Inquired about ' . ($ld['interest'] === 'moto' ? 'motorcycle service' : 'AC installation'),
                'follow_up_date'  => in_array($ld['status'], ['new', 'contacted', 'interested', 'qualified']) ? now()->addDays(rand(1, 14)) : null,
                'last_contact_at' => $ld['status'] !== 'new' ? now()->subDays(rand(1, 10)) : null,
                'call_attempts'   => $ld['status'] === 'lost' ? rand(2, 4) : rand(0, 2),
                'created_by'      => $manager->id,
                'assigned_user_id'=> $manager->id,
                'created_at'      => now()->subDays($daysAgo),
                'updated_at'      => now()->subDays(rand(0, $daysAgo)),
            ]);

            // Handle converted lead
            if ($ld['status'] === 'converted') {
                $customer = $this->customers[array_rand($this->customers)];
                $lead->update([
                    'converted_to_customer_id' => $customer->id,
                    'converted_at'             => now()->subDays(rand(1, 5)),
                ]);
            }

            // Handle lost leads
            if ($ld['status'] === 'lost') {
                $reasons = array_keys(Lead::LOST_REASONS);
                $reasonId = $reasons[array_rand($reasons)];
                $lead->update([
                    'lost_reason_id' => $reasonId,
                    'lost_reason'    => Lead::LOST_REASONS[$reasonId],
                    'lost_at'        => now()->subDays(rand(1, 10)),
                    'lost_by'        => $manager->id,
                ]);
            }

            // 1-3 interactions per lead (except brand new)
            if ($ld['status'] !== 'new') {
                $numInteractions = rand(1, 3);
                for ($j = 0; $j < $numInteractions; $j++) {
                    LeadInteraction::create([
                        'lead_id' => $lead->id,
                        'user_id' => $manager->id,
                        'type'    => $interactionTypes[array_rand($interactionTypes)],
                        'notes'   => $this->randomInteractionNote($ld['interest']),
                    ]);
                }
            }
        }
    }

    private function randomInteractionNote(string $interest): string
    {
        $notes = [
            'moto' => [
                'Customer asked about service pricing',
                'Discussed oil change package options',
                'Wants quote for full service',
                'Follow-up call — still interested',
                'Sent WhatsApp with price list',
            ],
            'ac' => [
                'Needs AC installation for 2-bedroom apartment',
                'Asked about cleaning service cost',
                'Wants gas top-up for 3 units',
                'Sent quotation via email',
                'Follow-up — comparing with other shops',
            ],
            'both' => [
                'Interested in both bike service and AC maintenance',
                'Asked for combined package pricing',
                'Follow-up call — deciding on timing',
            ],
        ];

        $pool = $notes[$interest] ?? $notes['both'];
        return $pool[array_rand($pool)];
    }

    // ─── 13. EOD Reconciliations ──────────────────────────────────

    private function createEodReconciliations(): void
    {
        $admin   = $this->users['admin'];
        $manager = $this->users['manager'];

        $records = [
            ['days_ago' => 1, 'unit' => 'moto', 'status' => 'open'],
            ['days_ago' => 1, 'unit' => 'cool', 'status' => 'open'],
            ['days_ago' => 2, 'unit' => 'moto', 'status' => 'closed'],
            ['days_ago' => 2, 'unit' => 'cool', 'status' => 'closed'],
            ['days_ago' => 3, 'unit' => 'moto', 'status' => 'deposited'],
        ];

        foreach ($records as $r) {
            $expectedCash = rand(2000, 8000);
            $expectedTransfer = rand(1000, 5000);
            $isClosed = in_array($r['status'], ['closed', 'deposited']);
            $countedCash = $isClosed ? $expectedCash + rand(-200, 100) : 0;

            EodReconciliation::create([
                'date'               => now()->subDays($r['days_ago'])->toDateString(),
                'business_unit'      => $r['unit'],
                'status'             => $r['status'],
                'expected_cash'      => $expectedCash,
                'expected_transfer'  => $expectedTransfer,
                'note_500'           => $isClosed ? rand(2, 10) : null,
                'note_100'           => $isClosed ? rand(5, 20) : null,
                'note_50'            => $isClosed ? rand(2, 10) : null,
                'note_20'            => $isClosed ? rand(0, 5) : null,
                'note_10'            => $isClosed ? rand(0, 10) : null,
                'coin_2'             => $isClosed ? rand(0, 5) : null,
                'coin_1'             => $isClosed ? rand(0, 5) : null,
                'counted_cash'       => $countedCash,
                'variance'           => $isClosed ? $countedCash - $expectedCash : 0,
                'notes'              => $isClosed ? 'End of day count completed' : null,
                'closed_by'          => $isClosed ? $manager->id : null,
                'closed_at'          => $isClosed ? now()->subDays($r['days_ago'])->setHour(18) : null,
                'deposited_by'       => $r['status'] === 'deposited' ? $admin->id : null,
                'deposited_at'       => $r['status'] === 'deposited' ? now()->subDays($r['days_ago'] - 1)->setHour(10) : null,
                'deposited_account_id' => $r['status'] === 'deposited' ? $this->accounts[0]->id : null,
            ]);
        }
    }
}
