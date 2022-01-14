<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'user_id', 'title', 'url', 'location', 'city', 'target_amount',
        'current_amount', 'act_date', 'deadline', 'banner_img', 'description',
        'is_completed',
    ];

    public $timestamps = true;

    public function user() {
        return $this->belongsTo('App\Models\User');
    }
}

?>