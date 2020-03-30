<?php

namespace Tests\Unit;

use App\Http\Controllers\WatchListController;
use App\Http\Helper\WatchList;
use App\Http\Helper\WatchListValidators;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class WatchListControllerTest extends TestCase
{
    public $fetchData;
    public function setUp(): void

    {
        parent::setUp();
        Artisan::call('migrate:fresh --seed');

        $this->fetchData =\App\WatchList::query()->first();
    }

    public function testFetchWatchListQuery(): void
    {

        ///has no record
        $testOne = WatchListController::fetchWatchListQuery('2a13af7a-15c2-31b1-abac-54fd64e8b0e4');
        $this->assertEmpty($testOne);

        ///has a record
        $testTwo = WatchListController::fetchWatchListQuery($this->fetchData->uuid);
        $this->assertNotEmpty($testTwo);
        $this->assertJson($testTwo);
        $firstRecord = $testTwo->first()->nid;
        $this->assertEquals($this->fetchData->nid, $firstRecord);

    }




    public function testGetList(): void
    {
        //has no secret
        $test1 = (new WatchListController)->getList(null);
        $this->assertNotEmpty($test1->getContent());
        $this->assertJson($test1->getContent());
        $this->assertEquals(403, $test1->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"body":null,"message":null}', $test1->getContent());


        //has invalid secret
        $test2 = (new WatchListController)->getList('Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2F');
        $this->assertNotEmpty($test2->getContent());
        $this->assertJson($test2->getContent());
        $this->assertEquals(403, $test2->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"body":null,"message":null}', $test2->getContent());



        //uuid notfound in db
        $encrypt = WatchList::encrypt('f0483750-665f-43c9-b2ca-24e7d26f4049', env('DECRYPT_KEY'), env('DECRYPT_IV'));

        $testOne = (new WatchListController)->getList($encrypt);
        $this->assertNotEmpty($testOne->getContent());
        $this->assertJson($testOne->getContent());
        $this->assertEquals(404, $testOne->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"body":null,"message":null}', $testOne->getContent());


        //uuid has value in db
        $encrypt = WatchList::encrypt($this->fetchData->uuid, env('DECRYPT_KEY'), env('DECRYPT_IV'));

        $testOne = (new WatchListController)->getList($encrypt);
        $this->assertNotEmpty($testOne->getContent());
        $this->assertJson($testOne->getContent());
        $this->assertEquals(200, $testOne->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"body":{"list":["'.$this->fetchData->nid.'"]},"message":null}', $testOne->getContent());

    }

}
