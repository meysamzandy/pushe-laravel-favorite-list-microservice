<?php

namespace Tests\Unit;

use App\Http\Controllers\WatchListController;
use App\Http\Helper\WatchList;
use App\Http\Helper\WatchListValidators;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Illuminate\Http\Request;
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


    public function testAddWatch(): void
    {
        // check if there is no data in body
        $request = new Request([], $_POST, [], [], [], []);
        $request->headers->set('Content-Type', 'application/json');

        $WatchListController = new WatchListController();
        $addWatch = $WatchListController->addWatch($request);

        $this->assertJson($addWatch->getContent());
        $this->assertEquals($addWatch->getStatusCode(), 400);

        // check if there is data in body but no value
        $data = '{"n":"","u":""}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');

        $WatchListController = new WatchListController();
        $addWatch = $WatchListController->addWatch($request);

        $this->assertJson($addWatch->getContent());
        $this->assertEquals($addWatch->getStatusCode(), 400);


        // check if there is data in body but nid is not valid

        $data = '{"n":"0e0","u":"Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo"}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');

        $WatchListController = new WatchListController();
        $addWatch = $WatchListController->addWatch($request);

        $this->assertJson($addWatch->getContent());
        $this->assertEquals($addWatch->getStatusCode(), 400);


        // check if if user is anonymous
        $data = '{"n":"757575","u":"OEluZnpRVWp4U1FyRE1Sb1IvYnA1RkVqYnc4SmNyRWp6WENYVk5MNzRycjdQRkVD"}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');

        $WatchListController = new WatchListController();
        $addWatch = $WatchListController->addWatch($request);

        $this->assertJson($addWatch->getContent());
        $this->assertEquals($addWatch->getStatusCode(), 403);



        // check if if uuid is invalid
        $data = '{"n":"757575","u":"Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNtFHb2FDalpRcUJ0c2Fo"}';

        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');

        $WatchListController = new WatchListController();
        $addWatch = $WatchListController->addWatch($request);

        $this->assertJson($addWatch->getContent());
        $this->assertEquals($addWatch->getStatusCode(), 403);



        // check if if uuid is invalid
        $data = '{"n":"757575","u":"Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNtFHb2FDalpRcUJ0c2F"}';
        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');

        $WatchListController = new WatchListController();
        $addWatch = $WatchListController->addWatch($request);

        $this->assertJson($addWatch->getContent());
        $this->assertEquals($addWatch->getStatusCode(), 400);



        // check if there is data in body and valid
        $data = '{"n":"757575","u":"Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo"}';


        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');

        $WatchListController = new WatchListController();
        $addWatch = $WatchListController->addWatch($request);

        $this->assertJson($addWatch->getContent());
        $this->assertEquals($addWatch->getStatusCode(), 201);



        // check if there is  already a record
        $data = '{"n":"757575","u":"Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo"}';

        $request = new Request([], $_POST, [], [], [], [], $data);
        $request->headers->set('Content-Type', 'application/json');

        $WatchListController = new WatchListController();
        $addWatch = $WatchListController->addWatch($request);

        $this->assertJson($addWatch->getContent());
        $this->assertEquals($addWatch->getStatusCode(), 403);



    }

}
