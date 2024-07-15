<?php

namespace App\Models;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;

use PhpParser\Node\Expr\Cast;

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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, "resource");
    }

}