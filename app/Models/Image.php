<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $tags
 * @property string $thumb_file
 * @property string $image_file
 */
class Image extends Model
{
    protected $table = "images";

    protected $hidden = ["id"];
}
