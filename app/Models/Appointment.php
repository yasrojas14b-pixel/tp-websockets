<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Carbon\CarbonInterface;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['client_id', 'service_id', 'starts_at', 'ends_at', 'status', 'total_price', 'source', 'client_notes', 'admin_notes'])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory, HasUlids;

    /**
     * The public code is the only identifier shown to clients, so
     * appointment ids cannot be guessed from the confirmation URL.
     *
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['public_code'];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => AppointmentStatus::class,
            'total_price' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return BelongsToMany<Addon, $this>
     */
    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class)->withPivot(['price', 'duration_minutes']);
    }

    /**
     * Appointments that still hold their time slot (everything but cancelled).
     *
     * @param  Builder<Appointment>  $query
     */
    public function scopeOccupying(Builder $query): void
    {
        $query->whereIn('status', AppointmentStatus::occupying());
    }

    /**
     * Two ranges [a, b) and [c, d) overlap when a < d and c < b.
     * Touching ranges (one ends exactly when the other starts) do not overlap.
     *
     * @param  Builder<Appointment>  $query
     */
    public function scopeOverlapping(Builder $query, CarbonInterface $startsAt, CarbonInterface $endsAt): void
    {
        $query->where('starts_at', '<', $endsAt)->where('ends_at', '>', $startsAt);
    }
}
