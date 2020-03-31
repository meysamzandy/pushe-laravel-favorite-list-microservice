<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed nid
 * @property string uuid
 */
class WatchList extends Model
{
    protected $fillable = ['nid','uuid'];
}
