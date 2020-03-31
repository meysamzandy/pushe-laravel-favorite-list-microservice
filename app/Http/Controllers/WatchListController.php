<?php

namespace App\Http\Controllers;

use App\Http\Helper\WatchList;
use App\Http\Helper\WatchListValidators;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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
            $this->ifUuidIsValid( $secretValidator, $uuid);


        }
    }

    /**
     * @param bool $secretValidator
     * @param string $uuid
     */
    public function ifUuidIsValid(bool $secretValidator, string $uuid): void
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

    public function addWatch(request $request): JsonResponse
    {
        $Validator = WatchListValidators::nidUuidValidator($request);

        $this->ifValidate($request, $Validator);

        return WatchList::returnDataInJson($this->body, $this->message, $this->statusCode, $this->statusMessage);
    }

    /**
     * @param Request $request
     * @param bool $Validator
     */
    public function ifValidate(request $request, bool $Validator): void
    {
        if ($Validator) {

            $this->statusCode = 403;
            $this->statusMessage = 'Forbidden';
            $this->message = __('dict.notLogging');

            $secret = $request->input('u');
            $nid = $request->input('n');

            $uuid = WatchList::decrypt($secret, env(self::DECRYPT_KEY), env(self::DECRYPT_IV));

            $ifNidAndUuidExist = $this->getObj($nid, $uuid);

            $this->ifUserIsLoggedIn($uuid, $ifNidAndUuidExist, $nid);

        }
    }

    /**
     * @param $nid
     * @param string $uuid
     * @return Builder|Model|object|null
     */
    public function getObj($nid, string $uuid)
    {
        return \App\WatchList::query()->where('nid', $nid)->where('uuid', $uuid)->first();
    }

    /**
     * @param string $uuid
     * @param $ifNidAndUuidExist
     * @param $nid
     */
    public function ifUserIsLoggedIn(string $uuid, $ifNidAndUuidExist, $nid): void
    {
        if (WatchListValidators::uuidValidator($uuid) && $uuid !== env('ANONYMOUS')) {
            $this->statusCode = 403;
            $this->statusMessage = 'Forbidden';
            $this->message = __('dict.nidAndUuidExist');
            $this->ifNidAndUuidExist($ifNidAndUuidExist, $uuid, $nid);
        }
    }

    /**
     * @param $ifNidAndUuidExist
     * @param string $uuid
     * @param $nid
     */
    public function ifNidAndUuidExist($ifNidAndUuidExist, string $uuid, $nid): void
    {
        if (!$ifNidAndUuidExist) {
            $this->statusCode = 403;
            $this->statusMessage = 'Forbidden';
            $this->message = __('dict.toManyRequest');
            $watchList = self::fetchWatchListQuery($uuid);
            $this->addToWatchlistQuery($watchList, $nid, $uuid);
        }
    }

    /**
     * @param $watchList
     * @param $nid
     * @param string $uuid
     */
    public function addToWatchlistQuery($watchList, $nid, string $uuid): void
    {
        if (count($watchList) < 30) {
            $this->statusCode = 201;
            $this->statusMessage = 'Created';
            $this->message = __('dict.created');
            $reActions = new \App\WatchList();
            $reActions->nid = $nid;
            $reActions->uuid = $uuid;
            $reActions->save();

        }
    }

    public function removeWatch(request $request)
    {

    }


}
