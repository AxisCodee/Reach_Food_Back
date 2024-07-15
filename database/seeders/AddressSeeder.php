<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\City;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure you have some cities created before creating addresses
//        $cities = City::all();
//
//        if ($cities->isEmpty()) {
//            $this->command->info('No cities found. Please seed cities first.');
//            return;
//        }

        $cities = [
            'دمشق' => ['المالكي', 'المزة', 'كفرسوسة', 'ركن الدين', 'باب توما'],
            'حلب' => ['السليمانية', 'الجميلية', 'حلب الجديدة', 'الحمدانية', 'الأعظمية'],
            'حمص' => ['الحمرا', 'الإنشاءات', 'باب السباع', 'حي الزهراء', 'حي النزهة'],
            'اللاذقية' => ['الرمل الشمالي', 'الصليبة', 'الأزهري', 'الشاطئ الأزرق', 'القرداحة'],
            'طرطوس' => ['بانياس', 'الدريكيش', 'صافيتا', 'الشيخ بدر', 'حمام واصل'],
            'حماة' => ['حي الأربعين', 'حي الحاضر', 'حي النصر', 'حي القصور', 'حي البرناوي'],
            'درعا' => ['نوى', 'جاسم', 'الصنمين', 'طفس', 'درعا البلد'],
            'الرقة' => ['تل أبيض', 'عين عيسى', 'الكرامة', 'معدان', 'سلوك'],
            'دير الزور' => ['الميادين', 'البوكمال', 'الكسرة', 'البصيرة', 'موحسن'],
            'إدلب' => ['معرة النعمان', 'جسر الشغور', 'سراقب', 'أريحا', 'خان شيخون'],
            'الحسكة' => ['القامشلي', 'رأس العين', 'المالكية', 'عامودا', 'الدرباسية'],
        ];
        $damas=['المالكي', 'المزة', 'كفرسوسة', 'ركن الدين', 'باب توما'];
        $tarto=['بانياس', 'الدريكيش', 'صافيتا', 'الشيخ بدر', 'حمام واصل'];
        $allepo= ['السليمانية', 'الجميلية', 'حلب الجديدة', 'الحمدانية', 'الأعظمية'];
        $hama=['حي الأربعين', 'حي الحاضر', 'حي النصر', 'حي القصور', 'حي البرناوي'];
        $dar=['نوى', 'جاسم', 'الصنمين', 'طفس', 'درعا البلد'];
        $der=['تل أبيض', 'عين عيسى', 'الكرامة', 'معدان', 'سلوك'];
        $idil=['الميادين', 'البوكمال', 'الكسرة', 'البصيرة', 'موحسن'];
        $hask=['القامشلي', 'رأس العين', 'المالكية', 'عامودا', 'الدرباسية'];
        $homs=['الحمرا', 'الإنشاءات', 'باب السباع', 'حي الزهراء', 'حي النزهة'];
        $refdama=['جرمانا','ببيلا','كراج الست'];
        $index=[
            1 => $damas,
            2=>$refdama,
            3=>$tarto,
            4=>$allepo,
            5=>$homs,
            6=>$hama
        ];

//        foreach ($cities as $cityName => $addresses) {
//            $city = City::create(['name' => $cityName]);
//            foreach ($addresses as $addressName) {
//                Address::create([
//                    'city_id' => $city->id,
//                    'name' => $addressName
//                ]);
//            }
//        }
//        $addresses = [
//            ['city_id' => 1, 'area' => 'منطقة 1'],
//            ['city_id' => 2, 'area' => 'منطقة 2'],
//            ['city_id' => 3, 'area' => 'منطقة 3'],
//            ['city_id' => 4, 'area' => 'منطقة 4'],
//            ['city_id' => 1, 'area' => 'منطقة 5'],
//            // Add more addresses as needed
//        ];

        $cities = City::all();
        foreach ($cities as $city) {
            $addresses = $index[$city->id];
            foreach ($addresses as $address) {
                Address::create([
                    'city_id' => $city->id,
                    'area' => $address
                ]);
            }
        }
    }
}
