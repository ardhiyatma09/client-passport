<?php

namespace App;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function hasExpired()
    {
        return Carbon::now()->gte($this->updated_at->addSeconds($this->expired_in));
    }
}
