<?php

/*
 * This file is part of ibrand/laravel-miniprogram-poster.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wonbin\Miniprogram\Share;

use Illuminate\Database\Eloquent\Model;

class Poster extends Model
{
    public $table = 'posters';

    public $guarded = ['id'];

    public function posterable()
    {
        return $this->morphTo();
    }

    public function getContentAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setContentAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['content'] = json_encode($value);
        }
    }
}
