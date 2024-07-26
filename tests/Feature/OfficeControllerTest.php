<?php

use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Tag;
use App\Notifications\OfficePendingApproval;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;

test('test_offices_with_paggination_get_rout_api', function () {
    $response = $this->get('/api/offices');
    $response->assertOk();
    $this->assertNotNull($response->json('meta'));
    $this->assertNotNull($response->json('data'));
});

test('test_offices_does_not_countent_hidden_or_APPROVEL_APPROVED_get_rout_api', function () {
    Office::factory(3)->create();
    Office::factory()->create(['approval_status' => Office::APPROVEL_PENDING]);
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

    $response = $this->get('/api/offices/' . $office->id);
    $response->assertOk();
    $this->assertEquals($office->id, $response->json('data')['id']);
    $this->assertNotNull($response->json('data')['user']);
    $this->assertEquals(1, $response->json('data')['reservations_count']);
    $this->assertNotNull($response->json('data')['tags'][0]);
    $this->assertNotNull($response->json('data')['images'][0]);
});


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

test('test_offices_create_rout_api', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $tags = Tag::factory(2)->create();
    Notification::fake();
    $admin = User::factory()->create(['name' => 'Admin']);

    $response = $this->postJson('/api/offices', Office::factory()->raw([
        'tags' => $tags->pluck('id')->toArray(),
    ]));
    $response->assertCreated()
        ->assertJsonPath('data.approval_status', Office::APPROVEL_PENDING)
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonCount(2, 'data.tags');

    Notification::assertSentTo($admin, OfficePendingApproval::class);

});

test('test_offices_create_not_allow_to_create_rout_api', function () {
    $user = User::factory()->createQuietly();
    $token = $user->createToken('test', []);

    $response = $this->postJson(
        '/api/offices',
        [],
        [
            'Authorization' => 'Bearer ' . $token->plainTextToken
        ]
    );
    $response->assertStatus(Response::HTTP_FORBIDDEN);
});
//

test('test_offices_update_rout_api', function () {
    $user = User::factory()->create();
    $tags = Tag::factory(2)->create();
    $office = Office::factory()->for($user)->create(['approval_status' => Office::APPROVEL_APPROVED]);

    $office->tags()->attach($tags);
    $this->actingAs($user);

    $anotheTag = Tag::factory()->create();
    $response = $this->putJson('/api/offices/' . $office->id, [
        'title' => 'updated',
        'tags' => [$tags[0]->id, $anotheTag->id]
    ]);
    // dd($response->json());
    $response->assertOk()
        ->assertJsonPath('data.title', 'updated')
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.approval_status', Office::APPROVEL_APPROVED)
        ->assertJsonCount(2, 'data.tags')
        ->assertJsonPath('data.tags.0.id', $tags[0]->id)
        ->assertJsonPath('data.tags.1.id', $anotheTag->id);
    $this->assertDatabaseHas('offices', ['title' => 'updated']);
});

test('test_offices_update_not_allow_for_his_not_office_user_rout_api', function () {
    $user = User::factory()->create();
    $office = Office::factory()->for(User::factory()->create())->create();

    $this->actingAs($user);

    $response = $this->putJson('/api/offices/' . $office->id, [
        'title' => 'updated'
    ]);
    $response->assertStatus(Response::HTTP_FORBIDDEN);
});



test('test_offices_update_change_approval_status_when_need_it_rout_api', function () {
    $user = User::factory()->create();
    $office = Office::factory()->for($user)->create(['approval_status' => Office::APPROVEL_APPROVED]);
    $this->actingAs($user);
    Notification::fake();
    $admin = User::factory()->create(['name' => 'Admin']);

    $response = $this->putJson('/api/offices/' . $office->id, [
        'title' => 'updated',
        'lat' => 434
    ]);
    $response->assertOk()
        ->assertJsonPath('data.title', 'updated')
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.approval_status', Office::APPROVEL_PENDING);
    $this->assertDatabaseHas('offices', ['title' => 'updated']);
    Notification::assertSentTo($admin, OfficePendingApproval::class);
});


test('test_offices_delete_rout_api', function () {
    $user = User::factory()->create();
    $office = Office::factory()->for($user)->create(['approval_status' => Office::APPROVEL_APPROVED]);
    $this->actingAs($user);

    $response = $this->deleteJson('/api/offices/' . $office->id);
    $response->assertOk();
    $this->assertSoftDeleted($office);
});

test('test_offices_cannot_delete_when_is_reservation_rout_api', function () {
    $user = User::factory()->create();
    $office = Office::factory()->for($user)->create(['approval_status' => Office::APPROVEL_APPROVED]);
    Reservation::factory()->for($office)->create();
    $this->actingAs($user);

    $response = $this->deleteJson('/api/offices/' . $office->id);
    $response->assertUnprocessable();
    $this->assertNotSoftDeleted($office);
});
