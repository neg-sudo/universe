<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['term', 'result_count', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
