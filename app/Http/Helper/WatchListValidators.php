<?php


namespace App\Http\Helper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class WatchListValidators
{
    /**
     * @param Request $request
     * @return bool
     */
    public static function nidUuidValidator(request $request): bool
    {
        $validator = Validator::make($request->all(),
            ['n' => 'required|numeric|min:1']
        );
        return !$validator->fails();
    }

    /**
     * @param $uuid
     * @return bool
     */
    public static function uuidValidator($uuid): bool
    {
        $validator = Validator::make(['uuid' =>$uuid],
            ['uuid' => 'uuid']
        );
        return !$validator->fails();
    }

    /**
     * @param $secret
     * @return bool
     */
    public static function secretValidator($secret): bool
    {
        $validator = Validator::make(['u' => $secret],
            ['u' => 'required|min:64']
        );
        return !$validator->fails();
    }

}
