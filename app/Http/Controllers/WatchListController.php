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


    public function ifSecretIsValid(Request $request,$nid = null): void
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
            $this->ifUuidIsValid($uuid,$nid);


        }
    }

    /**
     * @param string $uuid
     * @param null $nid
     */
    public function ifUuidIsValid(string $uuid,$nid = null): void
    {
        if (WatchListValidators::uuidValidator($uuid)) {
            $this->setNotFoundStatus();

            $watchList = Queries::fetchWatchListQuery($uuid);

            if (!$nid) {
                $this->ifWatchListHasItem($watchList);
            }else{
                $this->isFavorite($uuid,$nid);
            }

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
     * @param $uuid
     * @param $nid
     */
    public function isFavorite($uuid,$nid): void
    {
        $isFavorite = \App\WatchList::query()->where(['uuid'=>$uuid ,'nid' => $nid])->exists();
            $this->body['isFavorite'] = $isFavorite;
            $this->setOkStatus();

    }

    /**
     * @param $watchList
     */
    public function ifWatchListHasItem($watchList): void
    {
        if (count($watchList) !== 0) {
            $data = null;
            foreach ($watchList as $value) {
                $obj = $this->prepareData($value->nid);
                if (!$obj) {
                    continue;
                }
                $data [] = $obj;
            }
            $this->body['list'] = $data;
            $this->setOkStatus();
        }
    }

    public function prepareData(int $nid): array
    {
        $data = [];
       $dataQueries = Queries::getMovieDataByNid($nid);
       if ($dataQueries) {
           foreach ($dataQueries as $item => $value) {
               if ($item === 'id') {
                   $data['nid'] = (int) $value;
                   continue;
               }
               if ($item === 'alias') {
                   $data['alias'] = $value;
                   continue;
               }
               if ($item === 'title') {
                   $data['title'] = $value;
                   continue;
               }
               if ($item === 'uri') {
                   $data['uri'] = str_replace('public://', '/sites/default/files/', $value);
                   continue;
               }
               if ($item === 'serial_type') {
                   $data['serial_type'] = $value ? (int) $value : null ;
                   continue;
               }
               if ($item === 'field_serial_part_numbers_value') {
                   $data['serial_part_number'] = $value ? (int) $value : null;
                   continue;
               }
               if ($item === 'field_serial_season_number_value') {
                   $data['serial_season_number'] = $value ? (int) $value : null;
                   continue;
               }
               if ($item === 'mother') {
                   $data['mother'] = $value ? (int) $value : null;
                   continue;
               }
               $data[$item] = $value ? (int) $value : null;
           }
       }


        return $data;
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
     * @param int $nid
     * @return JsonResponse
     */
    public function addWatch(request $request, int $nid): JsonResponse
    {

        $this->ifValidateToAdd($request, $nid);

        return WatchList::returnDataInJson($this->body, $this->message, $this->statusCode, $this->statusMessage);
    }

    /**
     * @param Request $request
     * @param int $nid
     */
    public function ifValidateToAdd(request $request, int $nid): void
    {
        $uuid = null;
        $tokenData = $this->getPayloadFromJwt($request->bearerToken());
        if ($nid && $tokenData) {

            $this->setForbiddenStatus();
            $this->setMessage(__('dict.notLogging'));

            if (isset($tokenData['auid'])) {
                $uuid = $tokenData['auid'];
            }
            if (isset($tokenData['body']['auid'])) {
                $uuid = $tokenData['body']['auid'];
            }

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
     * @param int $nid
     * @return JsonResponse
     */
    public function removeWatch(request $request, int $nid): JsonResponse
    {
        $this->ifValidateToRemove($request, $nid);

        return WatchList::returnDataInJson($this->body, $this->message, $this->statusCode, $this->statusMessage);
    }

    /**
     * @param Request $request
     * @param int $nid
     */
    public function ifValidateToRemove(request $request, int $nid): void
    {
        $uuid = null;
        $tokenData = $this->getPayloadFromJwt($request->bearerToken());
        if ($nid && $tokenData) {

            $this->setForbiddenStatus();
            $this->setMessage(__('dict.notLogging'));

            if (isset($tokenData['auid'])) {
                $uuid = $tokenData['auid'];
            }
            if (isset($tokenData['body']['auid'])) {
                $uuid = $tokenData['body']['auid'];
            }

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

    public function check($nid,Request $request)
    {

        $this->setForbiddenStatus();

        $this->ifSecretIsValid($request,(int) $nid);

        return WatchList::returnDataInJson($this->body, $this->message, $this->statusCode, $this->statusMessage);

    }

}
