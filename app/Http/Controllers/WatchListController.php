<?php

namespace App\Http\Controllers;

use App\Http\Helper\WatchList;
use App\Http\Helper\WatchListValidators;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Mixed_;

class WatchListController extends Controller
{
    public const DECRYPT_KEY = 'DECRYPT_KEY';
    public const DECRYPT_IV = 'DECRYPT_IV';
    private
        $body = NULL,
        $message = NULL,
        $statusCode = 400,
        $statusMessage = 'Bad Request';

    public function getList($secret = null): JsonResponse
    {
        $this->statusCode = 403;
        $this->statusMessage = 'Forbidden';

        $secretValidator = WatchListValidators::secretValidator($secret);

        $this->ifSecretIsValid($secret, $secretValidator);

        return WatchList::returnDataInJson($this->body, $this->message, $this->statusCode, $this->statusMessage);

    }

    /**
     * @param $secret
     * @param bool $secretValidator
     */
    public function ifSecretIsValid($secret, bool $secretValidator): void
    {
        if ($secretValidator) {
            $this->statusCode = 403;
            $this->statusMessage = 'Forbidden';

            $uuid = WatchList::decrypt($secret, env(self::DECRYPT_KEY), env(self::DECRYPT_IV));
            $this->ifUuidIsValid($secret, $secretValidator, $uuid);


        }
    }

    /**
     * @param $secret
     * @param bool $secretValidator
     * @param string $uuid
     */
    public function ifUuidIsValid($secret, bool $secretValidator, string $uuid): void
    {
        if ($secretValidator && WatchListValidators::uuidValidator($uuid)) {
            $this->statusCode = 404;
            $this->statusMessage = 'Not Found';

            $watchList = self::fetchWatchListQuery($uuid);

            $this->ifWatchListHasItem($watchList);

        }
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
     * @param $watchList
     */
    public function ifWatchListHasItem($watchList): void
    {
        if (count($watchList) !== 0) {
            $data = null;
            foreach ($watchList as $value) {
                $data [] = $value->nid;
            }
            $this->body['list'] = $data;
            $this->statusCode = 200;
            $this->statusMessage = 'OK';
        }
    }

    public function addWatch(request $request)
    {

    }

    public function removeWatch(request $request)
    {

    }


}
