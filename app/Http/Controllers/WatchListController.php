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
    /**
     * @param null $secret
     * @return JsonResponse
     */
    public function getList($secret = null): JsonResponse
    {
        $this->setForbiddenStatus();

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
            $this->setForbiddenStatus();

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
            $this->setNotFoundStatus();

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
            $this->setOkStatus();
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addWatch(request $request): JsonResponse
    {
        $Validator = WatchListValidators::nidUuidValidator($request);

        $this->ifValidateToAdd($request, $Validator);

        return WatchList::returnDataInJson($this->body, $this->message, $this->statusCode, $this->statusMessage);
    }

    /**
     * @param Request $request
     * @param bool $Validator
     */
    public function ifValidateToAdd(request $request, bool $Validator): void
    {
        if ($Validator) {

            $this->setForbiddenStatus();
            $this->message = __('dict.notLogging');

            $secret = $request->input('u');
            $nid = $request->input('n');

            $uuid = WatchList::decrypt($secret, env(self::DECRYPT_KEY), env(self::DECRYPT_IV));

            $ifNidAndUuidExist = $this->getObj($nid, $uuid);

            $this->ifUserIsLoggedInToAdd($uuid, $ifNidAndUuidExist, $nid);

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
    public function ifUserIsLoggedInToAdd(string $uuid, $ifNidAndUuidExist, $nid): void
    {
        if (WatchListValidators::uuidValidator($uuid) && $uuid !== env('ANONYMOUS')) {
            $this->setForbiddenStatus();
            $this->message = __('dict.nidAndUuidExist');
            $this->ifNidAndUuidExistToAdd($ifNidAndUuidExist, $uuid, $nid);
        }
    }

    /**
     * @param $ifNidAndUuidExist
     * @param string $uuid
     * @param $nid
     */
    public function ifNidAndUuidExistToAdd($ifNidAndUuidExist, string $uuid, $nid): void
    {
        if (!$ifNidAndUuidExist) {
            $this->setForbiddenStatus();
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeWatch(request $request): JsonResponse
    {
        $Validator = WatchListValidators::nidUuidValidator($request);

        $this->ifValidateToRemove($request, $Validator);

        return WatchList::returnDataInJson($this->body, $this->message, $this->statusCode, $this->statusMessage);
    }

    /**
     * @param $nid
     * @param string $uuid
     * @return mixed
     */
    public function removeFromWatchList($nid, string $uuid)
    {
        return \App\WatchList::query()->where('nid', $nid)->where('uuid', $uuid)->delete();
    }

    /**
     * @param $ifNidAndUuidExist
     * @param $nid
     * @param string $uuid
     */
    public function ifNidAndUuidExistToRemove($ifNidAndUuidExist, $nid, string $uuid): void
    {
        if ($ifNidAndUuidExist) {
            $this->setOkStatus();
            $this->message = __('dict.deleted');
            $this->removeFromWatchList($nid, $uuid);
        }
    }

    /**
     * @param string $uuid
     * @param $ifNidAndUuidExist
     * @param $nid
     */
    public function ifUserIsLoggedInToRemove(string $uuid, $ifNidAndUuidExist, $nid): void
    {
        if (WatchListValidators::uuidValidator($uuid) && $uuid !== env('ANONYMOUS')) {
            $this->setNotFoundStatus();
            $this->message = __('dict.nidAndUuidNotExist');
            $this->ifNidAndUuidExistToRemove($ifNidAndUuidExist, $nid, $uuid);
        }
    }

    /**
     * @param Request $request
     * @param bool $Validator
     */
    public function ifValidateToRemove(request $request, bool $Validator): void
    {
        if ($Validator) {

            $this->setForbiddenStatus();
            $this->message = __('dict.notLogging');

            $secret = $request->input('u');
            $nid = $request->input('n');

            $uuid = WatchList::decrypt($secret, env(self::DECRYPT_KEY), env(self::DECRYPT_IV));

            $ifNidAndUuidExist = $this->getObj($nid, $uuid);

            $this->ifUserIsLoggedInToRemove($uuid, $ifNidAndUuidExist, $nid);

        }
    }

    /**
     * @param string $statusMessage
     */
    public function setStatusMessage(string $statusMessage): void
    {
        $this->statusMessage = $statusMessage;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * set Forbidden Status.
     */
    protected function setForbiddenStatus(): void
    {
        $this->setStatusCode(403);
        $this->setStatusMessage('Forbidden');
    }

    /**
     * set Not Found Status.
     */
    protected function setNotFoundStatus(): void
    {
        $this->setStatusCode(404);
        $this->setStatusMessage('Not Found');
    }

    /**
     * set Ok Status.
     */
    protected function setOkStatus(): void
    {
        $this->setStatusCode(200);
        $this->setStatusMessage('OK');
    }


}
