<?php


namespace App\Http\Helper;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\Mixed_;

class Queries
{
    /**
     * @param $nid
     * @param string $uuid
     * @return mixed
     */
    public static function removeFromWatchList($nid, string $uuid)
    {
        return \App\WatchList::query()->where('nid', $nid)->where('uuid', $uuid)->delete();
    }

    /**
     * @param string $uuid
     * @return Builder[]|Collection|Mixed_
     */
    public static function fetchWatchListQuery(string $uuid)
    {
        return \App\WatchList::query()->where('uuid', $uuid)->get('nid');
    }

    /**
     * @param $nid
     * @param string $uuid
     * @return Builder|Model|object|null
     */
    public static function getObj($nid, string $uuid)
    {
        return \App\WatchList::query()->where('nid', $nid)->where('uuid', $uuid)->first();
    }
}
