<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Office;
use App\Models\Reservation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class UserReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        //
        abort_unless(
            auth()->user()->tokenCan('reservation.'.'show'),
            Response::HTTP_FORBIDDEN
        );
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = Reservation::filter();
        $query->where('user_id', auth()->id());
        $query->with(['office']);
        $reservation = $query->paginate(20);

        // Return the reservations
        return ReservationResource::collection($reservation);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_unless(
            auth()->user()->tokenCan('reservation.'.'create'),
            Response::HTTP_FORBIDDEN
        );
        $request = request()->validate([
            'office_id' => ['required', 'integer'],
            'start_date' => ['required', 'date:Y-m-d', 'after:'.now()->addDay()->toDateString()],
            'end_date' => ['required', 'date:Y-m-d', 'after:start_date'],
        ]);
        /** @var \App\Models\Office $office */
        $office = Office::findOr($request('office_id'), ['office_id'], fn () => throw ValidationException::withMessages(['office_id' => 'Invailed Office ID']));
        throw_if(
            $office->user_id == auth()->id(),
            ValidationException::withMessages(['office' => 'you cannot make reservation on your own office'])
        );
        throw_if(
            $office->reservations()->activeBetween()->excists(),
            ValidationException::withMessages(['office' => 'you cannot make reservation on this time'])
        );

        Cache::lock('reservations_office_'.$office->id, 10)->block(3, function () {});

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReservationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        //
    }
}
