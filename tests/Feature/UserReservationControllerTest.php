<?php

use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;

test('is_list_user_reserviation_belong_to_user_only', function () {
    $user = User::factory()->create();
    Reservation::factory(2)->for($user)->create();
    Reservation::factory(3)->create();
    $this->actingSanctumAs($user);
    $response = $this->getJson('/api/reservations/');
    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

test('is_list_user_reserviation_belong_to_office_only', function () {
    $user = User::factory()->create();
    $office = Office::factory()->for($user)->create();
    Reservation::factory(2)->for($user)->for($office)->create();
    Reservation::factory(3)->for($user)->create();
    $this->actingSanctumAs($user);
    //with number
    $response = $this->getJson('/api/reservations?'.http_build_query(['office_id' => $office->id]));
    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

test('is_list_user_reserviation_state_that_faillter_only', function () {
    $user = User::factory()->create();
    Reservation::factory(2)->for($user)->create(['status' => Reservation::STATUS_ACTIVE]);
    Reservation::factory(3)->for($user)->create(['status' => Reservation::STATUS_CANCELED]);
    $this->actingSanctumAs($user);
    //captlize
    $response = $this->getJson('/api/reservations?'.http_build_query(['status' => 'ACTIVE']));
    $response->assertOk();
    $response->assertJsonCount(2, 'data');
    foreach ($response->json('data') as $item) {
        $this->assertEquals(Reservation::STATUS_ACTIVE, $item['status']);
    }
    //small
    $response = $this->getJson('/api/reservations?'.http_build_query(['status' => 'active']));
    $response->assertOk();
    $response->assertJsonCount(2, 'data');
    foreach ($response->json('data') as $item) {
        $this->assertEquals(Reservation::STATUS_ACTIVE, $item['status']);
    }
    //other type
    $response = $this->getJson('/api/reservations?'.http_build_query(['status' => 'CANCELED']));
    $response->assertOk();
    $response->assertJsonCount(3, 'data');
    foreach ($response->json('data') as $item) {
        $this->assertEquals(Reservation::STATUS_CANCELED, $item['status']);
    }
    //Wrong return all
    $response = $this->getJson('/api/reservations?'.http_build_query(['status' => 'ff']));
    $response->assertOk();
    $response->assertJsonCount(5, 'data');
    //with number
    $response = $this->getJson('/api/reservations?'.http_build_query(['status' => Reservation::STATUS_CANCELED]));
    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});
test('filters_reservations_by_date_range', function () {
    $user = User::factory()->create();
    $from_date = '2021-03-03';
    $to_date = '2021-04-04';

    // Create reservations with different dates
    Reservation::factory()->for($user)->createMany([
        [
            'start_date' => '2021-03-01',
            'end_date' => '2021-03-15',
        ],
        [
            'start_date' => '2021-03-25',
            'end_date' => '2021-03-15',
        ],
        [
            'start_date' => '2021-03-25',
            'end_date' => '2021-03-29',
        ],
        [
            'start_date' => '2021-03-01',
            'end_date' => '2021-04-15',
        ],
        [
            'start_date' => '2021-05-04',
            'end_date' => '2021-05-12',
        ],
    ]);

    //for other user
    Reservation::factory()->create([
        'start_date' => '2021-03-03',
        'end_date' => '2021-03-03',
    ]);
    // Authenticate as the created user
    $this->actingAs($user);

    // Test for both from_date and to_date
    $response = $this->getJson('/api/reservations?'.http_build_query([
        'from_date' => $from_date,
        'to_date' => $to_date,
    ]));

    $response->assertOk()
        ->assertJsonCount(4, 'data');
    // Test for invalid dates (no matching records)
    $response = $this->getJson('/api/reservations?'.http_build_query([
        'from_date' => now()->addDays(15)->format('Y-m-d'),
        'to_date' => now()->addDays(20)->format('Y-m-d'),
    ]));
    $response->assertOk()
        ->assertJsonCount(0, 'data');
});
