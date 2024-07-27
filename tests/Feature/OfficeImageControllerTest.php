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
        "/api/offices/{$office->id}/images",
        ['image' => UploadedFile::fake()->image('image.jpg')]
    );

    $response->assertCreated();
    Storage::disk('public')->assertExists(
        $response->json('data.path')
    );
});

test('delete_image', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    Storage::fake('public');
    $file = UploadedFile::fake()->image('uploaded_image.jpg');
    $filePath = $file->store('images', 'public');

    $office = Office::factory()->for($user)->create();
    $image = $office->images()->create(['path' => $filePath]);

    $office->images()->create(['path' => 'image2.jpg']);

    $response = $this->deleteJson("/api/offices/{$office->id}/images/{$image->id}");

    $response->assertOk();
    $this->assertModelMissing($image);
    Storage::disk('public')->assertMissing($filePath);
});

test('it_doesnot_delete_the_only_image', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $office = Office::factory()->for($user)->create();
    $image = $office->images()->create(['path' => 'image.jpg']);

    $response = $this->deleteJson("/api/offices/{$office->id}/images/{$image->id}");

    $response->assertUnprocessable();
    $this->assertModelExists($image);
    $response->assertJsonValidationErrors(['only_image']);
});

test('it_doesnot_delete_the_feature_image', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $office = Office::factory()->for($user)->create();
    $image = $office->images()->create(['path' => 'image.jpg']);


    $office->update(['featured_image_id' => $image->id]);
    $office->images()->create(['path' => 'image2.jpg']);

    $response = $this->deleteJson("/api/offices/{$office->id}/images/{$image->id}");

    $response->assertUnprocessable();
    $this->assertModelExists($image);
    $response->assertJsonValidationErrors(['featured_image']);
});

test('it_doesnot_delete_image_for_another_office', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $office = Office::factory()->for($user)->create();
    $office2 = Office::factory()->for($user)->create();
    $image = $office->images()->create(['path' => 'image.jpg']);


    $office->images()->create(['path' => 'image2.jpg']);

    $response = $this->deleteJson("/api/offices/{$office2->id}/images/{$image->id}");

    $response->assertUnprocessable();
    $this->assertModelExists($image);
    $response->assertJsonValidationErrors(['image']);
});
