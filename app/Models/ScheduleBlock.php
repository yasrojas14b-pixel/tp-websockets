<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ScheduleBlockFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['starts_at', 'ends_at', 'reason'])]
class ScheduleBlock extends Model
{
    /** @use HasFactory<ScheduleBlockFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<ScheduleBlock>  $query
     */
    public function scopeOverlapping(Builder $query, CarbonInterface $startsAt, CarbonInterface $endsAt): void
    {
        $query->where('starts_at', '<', $endsAt)->where('ends_at', '>', $startsAt);
    }
}
