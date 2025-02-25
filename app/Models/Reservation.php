<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'office_id',
        'start_date',
        'end_date',
        'status',
        'price',
    ];

    /**
     * Scope a query to only include
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActiveBetween($query)
    {
        $query->whereStatus(Reservation::STATUS_ACTIVE);
        $query->betweenDate(request('start_date'), request('end_date'));

        return $query;
    }

    /**
     * Scope a query to only include
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $from
     * @param  string  $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDate($query, $from, $to)
    {
        $query->where(function ($query) use ($from, $to) {
            $query->whereBetween('start_date', [$from, $to]);
            $query->orWhereBetween('end_date', [$from, $to]);
            $query->orWhere(function ($query) use ($from, $to) {
                $query->where('start_date', '<', $from);
                $query->where('end_date', '>', $to);

                return $query;
            });

            return $query;
        });

        // dd($query->toSql());
        return $query;
    }

    /**
     * Scope a query to only include
     */
    public function scopeFilter(Builder $query): Builder
    {
        if (Request()->office_id) {
            $query->where('office_id', Request()->office_id);
        }

        if ($status = self::STATUS(Request()->status)) {
            $query->whereStatus($status);
        }

        if (($from = Request()->from_date) && ($to = Request()->to_date)) {
            $query->betweenDate($from, $to);
        }

        return $query;
    }

    const STATUS_ACTIVE = 1;

    const STATUS_CANCELED = 2;

    protected $casts = [
        'price' => 'integer',
        'status' => 'integer',
        'start_date' => 'immutable_date',
        'end_date' => 'immutable_date',

    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public static function STATUS($status): ?int
    {

        if (Str::upper($status) == 'ACTIVE' || request('status') == '1') {
            return self::STATUS_ACTIVE;
        }
        if (Str::upper($status) == 'CANCELED' || request('status') == '2') {
            return self::STATUS_CANCELED;
        }

        return null;
    }
}
