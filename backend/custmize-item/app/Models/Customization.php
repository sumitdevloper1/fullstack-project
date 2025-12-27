<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customization extends Model
{
    protected $fillable = [
        'printing_color_mark_json',
        'printing_color_print_json',
        'engraving',
        'is_specification',
        'add_accessories_data',
        'remove_accessories_data',
        'unique_code',
        'standard_code_id'
    ];
       

    public function specifications()
    {
        return $this->hasMany(Specification::class);
    }
    public function standardCode()
    {
        return $this->belongsTo(StandardCode::class, 'standard_code_id');
    }

}
