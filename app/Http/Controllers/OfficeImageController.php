<?php

namespace App\Http\Controllers;

use App\Http\Resources\ImageResource;
use App\Models\Office;
use Illuminate\Http\Request;

class OfficeImageController extends Controller
{
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
    public function store(Request $request,Office $office)
    {
        //
        $request->validate([
            'image' => ['file', 'max:5000', 'mimes:png,jpg']
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
    public function destroy(string $id)
    {
        //
    }
}
