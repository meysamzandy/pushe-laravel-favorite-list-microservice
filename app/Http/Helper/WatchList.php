<?php


namespace App\Http\Helper;

use Illuminate\Http\JsonResponse;


class WatchList
{
    public const SHA_256 = 'sha256';
    public const CAST_5_CFB = 'cast5-cfb';

    /**
     * @param $string
     * @param $secret_key
     * @param $secret_iv
     * @return string
     */
    public static function encrypt($string, $secret_key, $secret_iv): string
    {
        $encrypt_method = self::CAST_5_CFB;
        $password = hash(self::SHA_256, $secret_key);
        $iv = substr(hash(self::SHA_256, $secret_iv), 0, 8);
        return base64_encode(openssl_encrypt($string, $encrypt_method, $password, 0,$iv));
    }

    /**
     * @param $string
     * @param $secret_key
     * @param $secret_iv
     * @return string
     */
    public static function decrypt($string, $secret_key, $secret_iv): string
    {
        $encrypt_method = self::CAST_5_CFB;
        $password = hash(self::SHA_256, $secret_key);
        $iv = substr(hash(self::SHA_256, $secret_iv), 0, 8);
        return openssl_decrypt(base64_decode($string), $encrypt_method, $password, 0, $iv);
    }


    /**
     * @param $body
     * @param $message
     * @param $statusCode
     * @param $statusMessage
     * @return JsonResponse
     */
    public static function returnDataInJson($body, $message, $statusCode, $statusMessage): JsonResponse
    {
        $data = [
            'body' => $body,
            'message' => $message,
        ];
        return (new JsonResponse($data))->setStatusCode($statusCode, $statusMessage);
    }

}
