<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategoriesSeederTable extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('product_categories')->insert([
            ['category_name' => 'Electronics', 'category_status' => 'A'],
            ['category_name' => 'Clothing', 'category_status' => 'A'],
            ['category_name' => 'Home & Furniture', 'category_status' => 'A'],
            ['category_name' => 'Toys', 'category_status' => 'A'],
            ['category_name' => 'Grocery', 'category_status' => 'A'], 
        ]);
    }
}
