<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthKey extends Model
{
    protected $fillable = ['key','type','expires_at','last_used_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
