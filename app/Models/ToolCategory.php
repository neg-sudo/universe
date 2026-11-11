<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToolCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'icon', 'sort_order'];

    public function tools(): HasMany
    {
        return $this->hasMany(Tool::class);
    }

    public function publishedTools(): HasMany
    {
        return $this->tools()->where('status', 'published');
    }
}
