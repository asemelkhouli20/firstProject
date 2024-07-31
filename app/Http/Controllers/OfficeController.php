<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOfficeRequest;
use App\Http\Resources\OfficeResource;
use App\Http\Validators\OfficeValidator;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\OfficePendingApproval;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OfficeController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $query = Office::query();
        $userId = request('user_id');
        $isUserRequestHisOffice = $userId && ($userId == auth('sanctum')->id());
        if (! $isUserRequestHisOffice) {
            $query->where('approval_status', Office::APPROVEL_APPROVED);
            $query->where('hidden', false);
        }
        if ($userId) {
            $query->whereUserId($userId);
        }
        if ($visitorID = request('visitor_id')) {
            $query->whereRelation('reservations', 'user_id', '=', $visitorID);
        }
        if ($lat = request('lat') && $lng = request('lng')) {
            $query->nearestTo($lat, $lng);
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
        abort_unless(
            auth()->user()->tokenCan('office.create'),
            Response::HTTP_FORBIDDEN
        );
        $attributes = OfficeValidator::validate($request);
        $attributes['approval_status'] = Office::APPROVEL_PENDING;
        DB::beginTransaction();
        $office = Office::Create(
            Arr::except($attributes, ['tags'])
        );
        if (isset($attributes['tags'])) {
            $office->tags()->sync($attributes['tags']);
        }
        DB::commit();
        $this->notfiyAdmin($office);

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
        abort_unless(
            auth()->user()->tokenCan('office.update'),
            Response::HTTP_FORBIDDEN
        );
        $this->authorize('update', $office);
        $attributes = OfficeValidator::validate($request, $office->id);
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
            $this->notfiyAdmin($office);
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
            auth()->user()->tokenCan('office.'.'delete'),
            Response::HTTP_FORBIDDEN
        );
        $this->authorize('delete', $office);

        throw_if(
            $office->reservations()->where('status', Reservation::STATUS_ACTIVE)->exists(),
            ValidationException::withMessages(['office' => 'This office cannot be deleted because there are existing reservations associated with it.'])
        );
        $office->images()->each(function ($image) {
            Storage::disk('public')->delete($image->path);

            $image->delete();
        });
        $office->delete();
    }

    public function notfiyAdmin(Office $office)
    {
        $admin = User::firstwhere('is_admin', true);
        if ($admin) {
            Notification::send($admin, new OfficePendingApproval($office));
        }
    }
}
