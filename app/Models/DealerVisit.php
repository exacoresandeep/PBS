<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealerVisit extends Model
{
    protected $fillable = [
        'dealer_id',
        'aso_id',
        'purpose_of_visit',
        'item_type',
        'remarks',
        'attachments',
        'stock_details',
        'new_order',
        'created_by',
        'created_at',
        "rcb_poster_visible",
        "dealer_signage",
        "pop_poster_available",
        "browser_available",
        "target_scheme_discussion",
        "no_ace_attached",
         'product',
        'products',
        'other_brands',
        'other_brand_details',
    ];
     protected $casts = [
        'attachments' => 'array', // store as JSON
        'stock_details' => 'array', // store as JSON
        'latitude' => 'string',
        'longitude' => 'string',
        'products' => 'array',
        'other_brands' => 'boolean',
        'other_brand_details' => 'array',
    ];
    public $timestamps = false; 

    // Relationships
    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    public function aso()
    {
        return $this->belongsTo(Employee::class, 'aso_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
    public function order()
    {
        return $this->hasOne(Order::class, 'dealer_visit_id')->where('source', 'dealer_visit');
    }
}
