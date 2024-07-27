<?php

use App\Models\Office;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('upload_image_and_store', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    Storage::fake('public');
    $office = Office::factory()->for($user)->create();
    $response = $this->post(
        '/api/offices/' . $office->id . '/images',
        [
            'image' => UploadedFile::fake()->image('image.jpg')
        ]
    );
    $response->assertCreated();
    Storage::disk('public')->assertExists(
        $response->json('data.path')
    );
});
