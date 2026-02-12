<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    use HasFactory;

    protected $table = 'hero_slides';

    protected $fillable = [
        'badge_text',
        'title',
        'description',
        'button_text',
        'button_link',
        'theme_color',
        'bg_gradient_start',
        'bg_gradient_end',
        'icon_top',
        'icon_bottom',
        'is_active',
        'sort_order',
    ];
}

