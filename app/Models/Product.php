<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'tblProductData';
    protected $primaryKey = 'intProductDataId';
    public $timestamps = false;

    protected $fillable = [
        'strProductCode',
        'strProductName',
        'strProductDesc',
        'intStock',
        'decCostGBP',
        'dtmAdded',
        'dtmDiscontinued',
    ];

     protected $casts = [
        'intStock' => 'integer',
        'decCostGBP' => 'decimal:2',
        'dtmAdded' => 'datetime',
        'dtmDiscontinued' => 'datetime',
    ];
}
