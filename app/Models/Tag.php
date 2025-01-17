<?php

namespace App\Models;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
