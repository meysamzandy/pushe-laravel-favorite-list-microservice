<?php

namespace Tests\Http\Helper;


use App\Http\Helper\WatchList;
use App\Http\Helper\WatchListValidators;
use App\Http\Controllers\WatchListController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class WatchListTest extends TestCase
{

	public function testReturnDataInJson(): void
    {
        $returnDataInJson = WatchList::returnDataInJson('test text', 'test message', 200, 'OK');
        $this->assertJson($returnDataInJson->getContent());
        $this->assertEquals($returnDataInJson->getStatusCode(), 200);

	}

	public function testDecrypt(): void
    {
        $decrypt = WatchList::decrypt('OEluZnpRVWp4U1FyRE1Sb1IvYnA1RkVqYnc4SmNyRWp6WENYVk5MNzRycjdQRkVD', 'xxxxx', 'yyyyy');
        $this->assertEquals('f0483750-665f-43c9-b2ca-24e7d26f4049', $decrypt);
        $uuid = WatchListValidators::uuidValidator($decrypt);
        $this->assertTrue($uuid);

        $decrypt = WatchList::decrypt('OEluZnpRVWp4U1FyRE1Sb1IvYnA1RkVqYnc4SmNyRWp6WENYVk5MNzRycjdQRkV', 'xxxxx', 'yyyyy');
        $this->assertNotEquals('f0483750-665f-43c9-b2ca-24e7d26f4049', $decrypt);
        $uuid = WatchListValidators::uuidValidator($decrypt);
        $this->assertFalse($uuid);

        $decrypt = WatchList::decrypt('OEluZnpRVWp4U1FyRE1Sb1IvYnA1RkVqYnc4SmNyRWp6WENYVk5MNzRycjdQRkVD', 'xxx', 'yyyyy');
        $this->assertNotEquals('f0483750-665f-43c9-b2ca-24e7d26f4049', $decrypt);
        $uuid = WatchListValidators::uuidValidator($decrypt);
        $this->assertFalse($uuid);

        $decrypt = WatchList::decrypt('OEluZnpRVWp4U1FyRE1Sb1IvYnA1RkVqYnc4SmNyRWp6WENYVk5MNzRycjdQRkVD', 'xxxxx', 'yyy');
        $this->assertNotEquals('f0483750-665f-43c9-b2ca-24e7d26f4049', $decrypt);
        $uuid = WatchListValidators::uuidValidator( $decrypt);
        $this->assertFalse($uuid);

        $decrypt = WatchList::decrypt('f0483750-665f-43c9-b2ca-24e7d26f4049', 'xxxxx', 'yyyyy');
        $this->assertNotEquals('f0483750-665f-43c9-b2ca-24e7d26f4049', $decrypt);
        $uuid = WatchListValidators::uuidValidator($decrypt);
        $this->assertFalse($uuid);

	}

	public function testEncrypt(): void
    {
        $decrypt = WatchList::encrypt('f0483750-665f-43c9-b2ca-24e7d26f4049', 'xxxxx', 'yyyyy');
        $this->assertIsString($decrypt);
        $this->assertEquals(64, strlen($decrypt));

        $decrypt = WatchList::decrypt($decrypt, 'xxxxx', 'yyyyy');
        $this->assertEquals('f0483750-665f-43c9-b2ca-24e7d26f4049', $decrypt);
        $uuid = WatchListValidators::uuidValidator($decrypt);
        $this->asserttrue($uuid);

	}
}
