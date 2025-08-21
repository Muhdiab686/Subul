<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */


    public function run(): void
    {

        $this->call([
            AdminUserSeeder::class,
            ManagerUserSeeder::class,
            WarehousemanSeeder::class,
            FixedCostSeeder::class,
        ]);
        // دول
        \App\Models\Country::firstOrCreate(
            ['code' => '+90'],
            ['name' => 'Turkey', 'is_enabled' => 1]
        );
        \App\Models\Country::firstOrCreate(
            ['code' => '+971'],
            ['name' => 'United Arab Emirates', 'is_enabled' => 1]
        );
        // موردين
        \App\Models\Supplier::factory(5)->create();
        // مستخدمين
        \App\Models\User::factory(10)->create();
        // موظفي توصيل
        \App\Models\DeliveryStaff::factory(5)->create();
        // // شحنات
        \App\Models\Shipment::factory(40)->create();
        // // طرود
        // \App\Models\Parcel::factory(30)->create();
        // // عناصر الطرود
        // \App\Models\ParcelItem::factory(50)->create();
        // // إنشاء شكاوى
        \App\Models\Complaint::factory(20)->create();

        // // إنشاء إدارة طرود
         \App\Models\ParcelManage::factory(count: 20)->create();
        // // محافظ
        // \App\Models\User::all()->each(function($user){
        // });
        // // ردود الشكاوى
         \App\Models\ComplaintResponse::factory(30)->create();
        // // استدعاء Seeders أخرى موجودة


    }
}
