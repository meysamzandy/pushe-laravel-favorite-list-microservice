<?php

namespace Tests\Feature;

use App\Http\Controllers\WatchListController;
use App\Http\Helper\WatchList;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class WatchListControllerTest extends TestCase
{
    public const WATCH = '_api/_v1/watch';
    public $fetchData;
    public function setUp(): void

    {
        parent::setUp();
        Artisan::call('migrate:fresh --seed');

        $this->fetchData =\App\WatchList::query()->first();
    }

    public function testGetList(): void
    {
        // check if no url has no secret
        $url = self::WATCH;
        $response = $this->get($url);
        $response->assertStatus(404);

        // secret is not valid
        $url = self::WATCH.'/MTYwUFQzbFNtMTZFQnpQL2RyZkpOeHNNTVZKYzZoaisrNVVIdmFuclRZcEJDKzN';
        $response = $this->get($url);
        $response->assertStatus(403);

        // uuid not found in db
        $url = self::WATCH.'/MTYwUFQzbFNtMTZFQnpQL2RyZkpOeHNNTVZKYzZoaisrNVVIdmFuclRZcEJDKzNC';
        $response = $this->get($url);
        $response->assertStatus(404);

        // uuid not found in db
        $encrypt = WatchList::encrypt($this->fetchData->uuid, env('DECRYPT_KEY'), env('DECRYPT_IV'));
        $url = self::WATCH.'/'.$encrypt;
        $response = $this->get($url);
        $response->assertStatus(200);

    }
}
