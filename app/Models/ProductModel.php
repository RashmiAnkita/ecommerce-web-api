<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    protected $fillable = [
        'product_name',
        'product_description',
        'product_category_id',
        'product_price',
        'product_quantity',
        'product_images',
        'product_manufacturer',
    ];
}
