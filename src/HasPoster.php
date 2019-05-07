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

trait HasPoster
{
    public function posters()
    {
        return $this->morphMany(Poster::class, 'posterable');
    }
}
