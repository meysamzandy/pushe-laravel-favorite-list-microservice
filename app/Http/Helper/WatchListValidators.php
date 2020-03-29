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
            ['n' => 'required|numeric|min:1', 'u' => 'required|min:64']
        );
        return !$validator->fails();
    }

    /**
     * @param $uuid
     * @return bool
     */
    public static function uuidValidator($uuid): bool
    {
        $validator = Validator::make($uuid,
            ['uuid' => 'uuid']
        );
        return !$validator->fails();
    }

    /**
     * @param Request $request
     * @return bool
     */
    public static function secretValidator(request $request): bool
    {
        $validator = Validator::make($request->all(),
            ['u' => 'required|min:64']
        );
        return !$validator->fails();
    }

}
