<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Laptop Gaming ASUS ROG',
                'description' => 'Laptop gaming dengan performa tinggi, RAM 16GB, SSD 512GB',
                'price' => 15000000.00,
                'stock' => 10,
                'category' => 'Electronics',
                'is_active' => true,
            ],
            [
                'name' => 'Smartphone Samsung Galaxy S23',
                'description' => 'Smartphone flagship dengan kamera 50MP dan layar 6.1 inch',
                'price' => 12000000.00,
                'stock' => 25,
                'category' => 'Electronics',
                'is_active' => true,
            ],
            [
                'name' => 'Mechanical Keyboard Logitech',
                'description' => 'Keyboard mechanical dengan RGB lighting dan switch tactile',
                'price' => 1500000.00,
                'stock' => 50,
                'category' => 'Accessories',
                'is_active' => true,
            ],
            [
                'name' => 'Monitor 4K LG 27 inch',
                'description' => 'Monitor 4K dengan panel IPS dan HDR10 support',
                'price' => 4500000.00,
                'stock' => 15,
                'category' => 'Electronics',
                'is_active' => true,
            ],
            [
                'name' => 'Baju Kaos Polo',
                'description' => 'Kaos polo premium dengan bahan cotton 100%',
                'price' => 150000.00,
                'stock' => 100,
                'category' => 'Fashion',
                'is_active' => true,
            ],
            [
                'name' => 'Sepatu Running Nike',
                'description' => 'Sepatu running dengan teknologi Air Max untuk kenyamanan maksimal',
                'price' => 2500000.00,
                'stock' => 30,
                'category' => 'Fashion',
                'is_active' => true,
            ],
            [
                'name' => 'Headphone Sony WH-1000XM4',
                'description' => 'Headphone wireless dengan noise cancellation terbaik di kelasnya',
                'price' => 4000000.00,
                'stock' => 20,
                'category' => 'Electronics',
                'is_active' => true,
            ],
            [
                'name' => 'Power Bank Xiaomi 20000mAh',
                'description' => 'Power bank dengan kapasitas besar dan fast charging support',
                'price' => 300000.00,
                'stock' => 75,
                'category' => 'Accessories',
                'is_active' => true,
            ],
            [
                'name' => 'Kamera Canon EOS R5',
                'description' => 'Kamera mirrorless full-frame dengan resolusi 45MP dan video 8K',
                'price' => 50000000.00,
                'stock' => 5,
                'category' => 'Electronics',
                'is_active' => true,
            ],
            [
                'name' => 'Jam Tangan Apple Watch Series 9',
                'description' => 'Smartwatch dengan GPS, health monitoring, dan Always-On display',
                'price' => 6000000.00,
                'stock' => 40,
                'category' => 'Accessories',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
