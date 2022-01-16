<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $fillable = [
        'user_id', 'campaign_id', 'name', 'email', 'phone', 'comment', 'amount', 
        'is_anonim', 'is_paid', 'paid_at', 'evidence', 'payment_method',
    ];

    public $timestamps = true;

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function campaign() {
        return $this->belongsTo('App\Models\Campaign');
    }
}

?>