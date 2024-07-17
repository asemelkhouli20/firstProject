<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOfficeRequest;
use App\Http\Requests\UpdateOfficeRequest;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return OfficeResource::collection(
            Office::query()
                ->where('approval_status', Office::APPROVEL_APPROVED)
                ->where('hidden', false)
                ->when(request('host_id'), fn ($builder) => $builder->whereUserId(request('host_id')))
                ->when(request('user_id'), fn (EloquentBuilder $builder) => $builder->whereRelation('reservations', 'user_id', '=', request('user_id')))
                ->when(
                    request('lat') && request('lng'),
                    fn ($builder) => $builder->nearestTo($request->lat, $request->lng),
                    fn ($builder) => $builder->orderBy('id', 'desc')
                )
                ->with(['tags', 'user', 'images'])
                ->withCount(['reservations' => fn ($builder) => $builder->where('status', Reservation::STATUS_ACTIVE)])
                ->paginate(20)
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOfficeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Office $office)
    {
        //
        $office
            ->loadCount(['reservations' => fn ($builder) => $builder->where('status', Reservation::STATUS_ACTIVE)])
            ->load(['tags', 'user', 'images']);
        return OfficeResource::make($office);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Office $office)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOfficeRequest $request, Office $office)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $office)
    {
        //
    }
}
