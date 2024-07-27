<?php

namespace App\Models;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Office extends Model
{
    use HasFactory, SoftDeletes;
    const APPROVEL_PENDING = 1;
    const APPROVEL_APPROVED = 2;
    const APPROVEL_REJECTED = 3;

    protected $casts = [
        "lat" => "decimal:8",
        "lng" => "decimal:8",
        "approval_status" => "integer",
        "price_per_day" => "integer",
        "monthly_discount" => "integer",
        "hidden" => "bool",
    ];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'lat',
        'lng',
        'address_line1',
        'price_per_day',
        'monthly_discount',
        'approval_status',
        'hidden',
        'featured_image_id',
        'tags',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,);
    }
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'featured_image_id');
    }
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'offices_tags');
    }
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, "resource");
    }
    public function scopeNearestTo(Builder $builder,$lat,$lng): Builder  {
        return $builder
            ->select()
            ->orderByRaw(
                'SQRT(POW(69.1 * (lat - ?), 2) + POW(69.1 * (? - lng) * COS(lat / 57.3), 2))',
                [$lat, $lng]
            );

    }

}
