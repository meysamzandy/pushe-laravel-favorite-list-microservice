<?php

namespace Tests\Feature;

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

        $this->fetchData = \App\WatchList::query()->first();
    }

    public function testGetList(): void
    {
        // check if no url has no secret
        $url = self::WATCH;
        $response = $this->get($url);
        $response->assertStatus(404);

        // secret is not valid
        $url = self::WATCH . '/MTYwUFQzbFNtMTZFQnpQL2RyZkpOeHNNTVZKYzZoaisrNVVIdmFuclRZcEJDKzN';
        $response = $this->get($url);
        $response->assertStatus(403);

        // uuid not found in db
        $url = self::WATCH . '/MTYwUFQzbFNtMTZFQnpQL2RyZkpOeHNNTVZKYzZoaisrNVVIdmFuclRZcEJDKzNC';
        $response = $this->get($url);
        $response->assertStatus(404);

        // uuid not found in db
        $encrypt = WatchList::encrypt($this->fetchData->uuid, env('DECRYPT_KEY'), env('DECRYPT_IV'));
        $url = self::WATCH . '/' . $encrypt;
        $response = $this->get($url);
        $response->assertStatus(200);

    }

    public function testAddWatch(): void
    {
        // check if no data
        $data = [
        ];
        $url = self::WATCH . '/add';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(400);

        // route is invalid
        $data = [
            'n' => 222222,
            'u' => ''
        ];
        $url = self::WATCH . '/adds';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(405);

        $this->assertDatabaseMissing('watch_lists', [
            'nid' => 222222
        ]);



        // check if nid is invalid
        $data = [
            'n' => '0e0',
            'u' => 'Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo'
        ];
        $url = self::WATCH . '/add';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(400);

        $this->assertDatabaseMissing('watch_lists', [
            'nid' => '0e0'
        ]);


        // check if no uuid
        $data = [
            'n' => 111111,
            'u' => ''
        ];
        $url = self::WATCH . '/add';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(400);

        $this->assertDatabaseMissing('watch_lists', [
            'nid' => 111111
        ]);




        //if user is anonymous
        $data = [
            'n' => 111111,
            'u' => 'MTYwUFQzbFNtMTZFQnpQL2RyZkpOeHNNTVZKYzZoaisrNVVIdmFuclRZcEJDKzNC'
        ];
        $url = self::WATCH . '/add';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(403);

        $this->assertDatabaseMissing('watch_lists', [
            'nid' => 111111,
        ]);


        //add
        $data = [
            'n' => 111111,
            'u' => 'Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo'
        ];
        $url = self::WATCH . '/add';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(201);

        $this->assertDatabaseHas('watch_lists', [
            'uuid' => '2d3c9de4-3831-4988-8afb-710fda2e740c',
            'nid' => 111111,
        ]);


        // check if there is  already a record
        $data = [
            'n' => 111111,
            'u' => 'Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo'
        ];
        $url = self::WATCH . '/add';
        $response = $this->post($url, $data, ['Content-Type', 'application/json']);
        $response->assertStatus(403);

        $this->assertDatabaseHas('watch_lists', [
            'uuid' => '2d3c9de4-3831-4988-8afb-710fda2e740c',
            'nid' => 111111,

        ]);

        // too many request > 30
        for ($i=0; $i<30;$i++) {

            $data = [
                'n' => 1 + $i,
                'u' => 'Zy9rSUZITUJ5MW91MUVBeGI3SWZIK3NlRVB4dm56ZUZSNUFHb2FDalpRcUJ0c2Fo'
            ];
            $url = self::WATCH . '/add';
            $response = $this->post($url, $data, ['Content-Type', 'application/json']);

        }
        $response->assertStatus(403);

        $this->assertDatabaseHas('watch_lists', [
            'uuid' => '2d3c9de4-3831-4988-8afb-710fda2e740c',
            'nid' => 29,
        ]);
        $this->assertDatabaseMissing('watch_lists', [
            'nid' => 30
        ]);


    }
}