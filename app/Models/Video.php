<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Video extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $table = 'ta_video';

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->slug = bin2hex(random_bytes(5));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function timeStamp()
    {
        $this->timeStamp = array(
            "created_at" => is_null($this->created_at) ? null : getTimeStampsAttribute($this->created_at),
            "updated_at" => is_null($this->updated_at) ? null : getTimeStampsAttribute($this->updated_at)
        );
        // return $this->timeStamp;
    }
}
