<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 1;
    const STATUS_CANCELED = 2;

    protected $casts = [
        "price" => "integer",
        "status" => "integer",
        "start_date" => "immutable_date",
        "end_date" => "immutable_date",

    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}