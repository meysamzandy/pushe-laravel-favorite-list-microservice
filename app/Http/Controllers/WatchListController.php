<?php

namespace App\Http\Controllers;

use App\Http\Helper\Queries;
use App\Http\Helper\WatchList;
use App\Http\Helper\WatchListValidators;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nowakowskir\JWT\Base64Url;

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
     * @param Request $request
     * @return JsonResponse
     */
    public function getList(Request $request): JsonResponse
    {
        $this->setForbiddenStatus();


        $this->ifSecretIsValid($request);

        return WatchList::returnDataInJson($this->body, $this->message, $this->statusCode, $this->statusMessage);

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
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @param string $statusMessage
     */
    public function setStatusMessage(string $statusMessage): void
    {
        $this->statusMessage = $statusMessage;
    }


    public function ifSecretIsValid(Request $request): void
    {
        $uuid = null ;
        $tokenData = $this->getPayloadFromJwt($request->bearerToken());

        if ($tokenData) {
            $this->setForbiddenStatus();

            if (isset($tokenData['auid'])) {
                $uuid = $tokenData['auid'];
            }
            if (isset($tokenData['body']['auid'])) {
                $uuid = $tokenData['body']['auid'];
            }
            $this->ifUuidIsValid($uuid);


        }
    }

    /**
     * @param string $uuid
     */
    public function ifUuidIsValid(string $uuid): void
    {
        if (WatchListValidators::uuidValidator($uuid)) {
            $this->setNotFoundStatus();

            $watchList = Queries::fetchWatchListQuery($uuid);

            $this->ifWatchListHasItem($watchList);

        }
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
     * @param $watchList
     */
    public function ifWatchListHasItem($watchList): void
    {
        if (count($watchList) !== 0) {
            $data = null;
            foreach ($watchList as $value) {
                $data [] = Queries::getMovieDataByNid($value->nid);
            }
            $this->body['list'] = $data;
            $this->setOkStatus();
        }
    }

    /**
     * set Ok Status.
     */
    protected function setOkStatus(): void
    {
        $this->setStatusCode(200);
        $this->setStatusMessage('OK');
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
        $uuid = null;
        $tokenData = $this->getPayloadFromJwt($request->bearerToken());
        if ($Validator && $tokenData) {

            $this->setForbiddenStatus();
            $this->setMessage(__('dict.notLogging'));

            if (isset($tokenData['auid'])) {
                $uuid = $tokenData['auid'];
            }
            if (isset($tokenData['body']['auid'])) {
                $uuid = $tokenData['body']['auid'];
            }

            $nid = $request->input('n');

            $ifNidAndUuidExist = Queries::getObj($nid, $uuid);

            $this->ifUserIsLoggedInToAdd($uuid, $ifNidAndUuidExist, $nid);

        }
    }

    /**
     * @param null $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
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
            $this->setMessage(__('dict.nidAndUuidExist'));
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
            $this->setMessage(__('dict.toManyRequest'));
            $watchList = Queries::fetchWatchListQuery($uuid);
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
            $this->setCreatedStatus();
            $this->setMessage(__('dict.created'));
            $reActions = new \App\WatchList();
            $reActions->nid = $nid;
            $reActions->uuid = $uuid;
            $reActions->save();

        }
    }

    /**
     * set Created Status.
     */
    protected function setCreatedStatus(): void
    {
        $this->setStatusCode(201);
        $this->setStatusMessage('Created');
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
     * @param Request $request
     * @param bool $Validator
     */
    public function ifValidateToRemove(request $request, bool $Validator): void
    {
        $uuid = null;
        $tokenData = $this->getPayloadFromJwt($request->bearerToken());
        if ($Validator && $tokenData) {

            $this->setForbiddenStatus();
            $this->setMessage(__('dict.notLogging'));

            if (isset($tokenData['auid'])) {
                $uuid = $tokenData['auid'];
            }
            if (isset($tokenData['body']['auid'])) {
                $uuid = $tokenData['body']['auid'];
            }

            $nid = $request->input('n');

            $ifNidAndUuidExist = Queries::getObj($nid, $uuid);

            $this->ifUserIsLoggedInToRemove($uuid, $ifNidAndUuidExist, $nid);

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
            $this->setMessage(__('dict.nidAndUuidNotExist'));
            $this->ifNidAndUuidExistToRemove($ifNidAndUuidExist, $nid, $uuid);
        }
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
            $this->setMessage(__('dict.deleted'));
            Queries::removeFromWatchList($nid, $uuid);
        }
    }

    public function getPayloadFromJwt($token)
    {
        if (!$token) {
            return null ;
        }
        $token = str_replace('Bearer ', '', $token);
        list($header, $payload, $signature) = explode('.', $token);
        return json_decode(Base64Url::decode($payload), true);
    }

}
