<?php

namespace App\Http\Controllers;

use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OfficeImageController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Office $office)
    {
        //
        abort_unless(
            auth()->user()->tokenCan('office.' . 'update'),
            Response::HTTP_FORBIDDEN
        );
        $this->authorize('update', $office);

        $request->validate([
            'image' => ['file', 'max:2048', 'mimes:png,jpg']
        ]);
        $path = $request->file('image')->storePublicly('/', ['disk' => 'public']);

        $image = $office->images()->create([
            'path' => $path,
        ]);

        return ImageResource::make($image);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Office $office, Image $image)
    {
        //
        abort_unless(
            auth()->user()->tokenCan('office.' . 'update'),
            Response::HTTP_FORBIDDEN
        );
        $this->authorize('update', $office);
        throw_if(
            $office->images()->count() == 1,
            ValidationException::withMessages(['only_image' => 'Cannot delete the only image'])
        );
        throw_if(
             ($image->resource_type != 'office') || ($image->resource_id != $office->id),
            ValidationException::withMessages(['image' => 'Cannot delete this image because it is not for this office'])
        );
        throw_if(
            $office->featured_image_id == $image->id,
            ValidationException::withMessages(['featured_image' => 'Cannot delete the featured image'])
        );
        Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->json(['message' => 'Image deleted successfully']);

    }
}
