<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'model',
        'year',
        'color',
        'plate_number',
        'category_id',
        'seats',
        'transmission',
        'fuel_type',
        'price_per_day',
        'mileage',
        'description',
        'is_available',
    ];

    protected $casts = [
        'year' => 'integer',
        'seats' => 'integer',
        'price_per_day' => 'decimal:2',
        'mileage' => 'integer',
        'is_available' => 'boolean',
    ];

    protected $appends = ['primary_image_url'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function carImages(): HasMany
    {
        return $this->hasMany(CarImage::class);
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        $primaryImage = $this->carImages()->where('is_primary', true)->first();

        return $primaryImage?->image_url;
    }

    public function scopeAvailableForPeriod(
        Builder $query,
        string|Carbon $startDate,
        string|Carbon $endDate
    ): Builder {
        $start = $startDate instanceof Carbon ? $startDate->toDateString() : $startDate;
        $end = $endDate instanceof Carbon ? $endDate->toDateString() : $endDate;

        return $query->whereDoesntHave('bookings', function (Builder $bookingQuery) use ($start, $end) {
            $bookingQuery
                ->active()
                ->whereDate('start_date', '<=', $end)
                ->whereDate('end_date', '>=', $start);
        });
    }

    public function isAvailableForPeriod(string|Carbon $startDate, string|Carbon $endDate): bool
    {
        $start = $startDate instanceof Carbon ? $startDate->toDateString() : $startDate;
        $end = $endDate instanceof Carbon ? $endDate->toDateString() : $endDate;

        return ! $this->bookings()
            ->active()
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->exists();
    }
}
