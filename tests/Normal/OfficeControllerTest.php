<?php

use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Tag;


test('test_offices_with_paggination_get_rout_api', function () {
    $response = $this->get('/api/offices');
    $response->assertOk();
    $this->assertNotNull($response->json('meta'));
    $this->assertNotNull($response->json('data'));
});

test('test_offices_does_not_countent_hidden_or_APPROVEL_APPROVED_get_rout_api', function () {
    Office::factory(3)->create();
    Office::factory()->create(['approval_status'=> Office::APPROVEL_PENDING]);
    Office::factory()->create(['approval_status' => Office::APPROVEL_REJECTED]);
    Office::factory()->create(['hidden' => true]);
    $response = $this->get('/api/offices');
    foreach ($response->json('data') as $office) {
        $this->assertEquals(Office::APPROVEL_APPROVED, $office['approval_status']);
        $this->assertEquals(false, $office['hidden']);
    }
});

test('test_offices_with_fillter_by_userID_get_rout_api', function () {
    $houst = User::factory()->create();
    $office = Office::factory()->for($houst)->create();
    $response = $this->get('/api/offices?user_id=' . $houst->id);
    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $this->assertEquals($office->id, $response->json('data')[0]['id']);
});

test('test_offices_with_fillter_by_userID_for_reservation_get_rout_api', function () {
    $office = Office::factory()->create();
    $user = User::factory()->create();
    Reservation::factory()->for($office)->for($user)->create();
    Reservation::factory()->for(Office::factory())->for(User::factory())->create();

    $response = $this->get('/api/offices?visitor_id=' . $user->id);
    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $this->assertEquals($office->id, $response->json('data')[0]['id']);
    $this->assertEquals(1, $response->json('data')[0]['reservations_count']);
});
test('test_offices_with_tag_and_user_img_get_rout_api', function () {
    $office = Office::factory()->create();
    $tag = Tag::factory()->create();
    $office->tags()->attach($tag);
    $office->images()->create(['path' => 'image.png']);
    $response = $this->get('/api/offices');
    $response->assertOk();
    $this->assertNotNull($response->json('data')[0]['user']);
    $this->assertNotNull($response->json('data')[0]['images'][0]);
    $this->assertNotNull($response->json('data')[0]['tags'][0]);
});

test('test_offices_show_rout_api', function () {
    $office = Office::factory()->create();
    $user = User::factory()->create();
    Reservation::factory()->for($office)->for($user)->create();
    Reservation::factory()->for(Office::factory())->for(User::factory())->create();
    $tag = Tag::factory()->create();
    $office->tags()->attach($tag);
    $office->images()->create(['path' => 'image.png']);

    $response = $this->get('/api/offices/'. $office->id);
    $response->assertOk();
    $this->assertEquals($office->id, $response->json('data')['id']);
    $this->assertNotNull($response->json('data')['user']);
    $this->assertEquals(1,$response->json('data')['reservations_count']);
    $this->assertNotNull($response->json('data')['tags'][0]);
    $this->assertNotNull($response->json('data')['images'][0]);

});

