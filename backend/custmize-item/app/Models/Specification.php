<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specification extends Model
{
    protected $fillable = [
        'file',
        'note',
        'customization_id',
        'capacity',
        'neck_size',
        'item_name',
        'item_description',
        'remarks',
        'vendor_name',
        'pack_size',
        'moq'
    ];

    public function customization()
    {
        return $this->belongsTo(Customization::class);
    }
}
