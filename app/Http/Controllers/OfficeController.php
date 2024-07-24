<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOfficeRequest;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OfficeController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return OfficeResource::collection(
            Office::query()
                ->where('approval_status', Office::APPROVEL_APPROVED)
                ->where('hidden', false)
                ->when(request('user_id'), fn ($builder) => $builder->whereUserId(request('user_id')))
                ->when(request('visitor_id'), fn (EloquentBuilder $builder) => $builder->whereRelation('reservations', 'user_id', '=', request('visitor_id')))
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
    public function create(Request $request): JsonResource
    {
        //
        $attributes = OfficeController::vidateOffice($request, 'create');
        $attributes['approval_status'] = Office::APPROVEL_PENDING;
        DB::beginTransaction();
        $office = Office::Create(
            Arr::except($attributes, ['tags'])
        );
        if (isset($attributes['tags'])) {
            $office->tags()->sync($attributes['tags']);
        }
        DB::commit();

        return OfficeResource::make($office->load(['user', 'tags', 'images']));
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
    public function update(Request $request, Office $office)
    {
        //
        $this->authorize('update', $office);
        $attributes = OfficeController::vidateOffice($request, 'update');
        DB::beginTransaction();
        $office->update(Arr::except($attributes, ['tags']));
        if(isset($attributes['tags'])){
            $office->tags()->sync($attributes['tags']);
        }

        DB::commit();

        return OfficeResource::make($office->load(['user', 'tags', 'images']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $office)
    {
        //
    }

    function vidateOffice(Request $request, string $metthod)
    {
        abort_unless(
            auth()->user()->tokenCan('office.' . $metthod),
            Response::HTTP_FORBIDDEN
        );
        $sometimes = Rule::when(($metthod == 'update'), 'sometimes') ;
        $attributes = $request->validate([
                'title'         => [$sometimes, 'required', 'string' ],
                'description'   => [$sometimes, 'required', 'string' ],
                'lat'           => [$sometimes, 'required', 'numeric'],
                'lng'           => [$sometimes, 'required', 'numeric'],
                'address_line1' => [$sometimes, 'required', 'string' ],
                'price_per_day' => [$sometimes, 'required', 'integer', 'min:100'],

                'hidden' => ['bool'],
                'monthly_discount' => ['integer', 'min:0'],

                'tags' => ['array'],
                'tags.*' => ['integer', Rule::exists('tags', 'id')],
            ]
        );
        $attributes['user_id'] = auth()->id();
        return $attributes;
    }
}
