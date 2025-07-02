<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
    ];

    // Relación uno a muchos con Product
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}