<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'quantity',
        'description',
        'image_url',
        'image_public_id', // Added for tracking and deleting Cloudinary assets
    ];
}