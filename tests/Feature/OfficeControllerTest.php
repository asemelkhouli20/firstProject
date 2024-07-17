<?php

use App\Models\Office;

test('test_offices_with_more_near_office_get_rout_api', function () {
    $officeVista = Office::factory()->create([
        'lat' => '42.85462222240959',
        'lng' => '-106.4458201607401',
        'title' => 'Vista West (neard)',
    ]);
    $officeAir = Office::factory()->create([
        'lat' => '42.902509841803486',
        'lng' => '-106.4583482169854',
        'title' => 'Casper/Natrona County International Airport (far)',
    ]);
    $officeGreen = Office::factory()->create([
        'lat' => '42.95168938189494',
        'lng' => '-106.46148919716981',
        'title' => 'Green Acres Corn Maze (more far)',
    ]);
    //Yonder Marketing
    $response = $this->get('/api/offices?lat=42.82495894464695&lng=-106.37005835529582');
    $response->assertOk();
    $this->assertEquals($officeVista->title, $response->json('data')[0]['title']);
    $this->assertEquals($officeAir->title, $response->json('data')[1]['title']);
    $this->assertEquals($officeGreen->title, $response->json('data')[2]['title']);
});
