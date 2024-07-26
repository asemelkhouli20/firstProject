<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOfficeRequest;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\OfficePendingApproval;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Notification;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OfficeController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query =  Office::query();
        $isUserRequestHisOffice = request('user_id') && auth()->user() && (request('user_id') == auth()->id());
        if (!$isUserRequestHisOffice) {
            $query->where('approval_status', Office::APPROVEL_APPROVED)->where('hidden', false);
        }
        if (request('user_id')) {
            $query->whereUserId(request('user_id'));
        }
        if (request('visitor_id')) {
            $query->whereRelation('reservations', 'user_id', '=', request('visitor_id'));
        }
        if (request('lat') && request('lng')) {
            $query->nearestTo($request->lat, $request->lng);
        } else {
            $query->orderBy('id', 'desc');
        }
        $query->with(['tags', 'user', 'images']);
        $query->withCount(['reservations' => fn ($builder) => $builder->where('status', Reservation::STATUS_ACTIVE)]);

        $office = $query->paginate(20);
        return OfficeResource::collection($office);
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
        $admin = User::firstwhere('is_admin', true);
        if ($admin) {
            Notification::send($admin, new OfficePendingApproval($office));
        }
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
        $office->fill(Arr::except($attributes, ['tags']));

        $requerReview = $office->isDirty(['lat', 'lng', 'address_line1', 'price_per_day', 'monthly_discount']);
        if ($requerReview) {
            $office->fill(['approval_status' => Office::APPROVEL_PENDING]);
        }
        DB::beginTransaction();
        $office->save();
        if (isset($attributes['tags'])) {
            $office->tags()->sync($attributes['tags']);
        }
        DB::commit();
        if ($requerReview) {
            $admin = User::firstwhere('is_admin', true);
            if ($admin) {
                Notification::send($admin, new OfficePendingApproval($office));
            }
        }

        return OfficeResource::make($office->load(['user', 'tags', 'images']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Office $office)
    {
        //
        abort_unless(
            auth()->user()->tokenCan('office.' . 'delete'),
            Response::HTTP_FORBIDDEN
        );
        $this->authorize('delete', $office);

        throw_if(
            $office->reservations()->where('status', Reservation::STATUS_ACTIVE)->exists(),
            ValidationException::withMessages(['office' => 'This office cannot be deleted because there are existing reservations associated with it.'])
        );
        $office->delete();
    }

    function vidateOffice(Request $request, string $metthod)
    {
        abort_unless(
            auth()->user()->tokenCan('office.' . $metthod),
            Response::HTTP_FORBIDDEN
        );
        $sometimes = Rule::when(($metthod == 'update'), 'sometimes');
        $attributes = $request->validate(
            [
                'title'         => [$sometimes, 'required', 'string'],
                'description'   => [$sometimes, 'required', 'string'],
                'lat'           => [$sometimes, 'required', 'numeric'],
                'lng'           => [$sometimes, 'required', 'numeric'],
                'address_line1' => [$sometimes, 'required', 'string'],
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
