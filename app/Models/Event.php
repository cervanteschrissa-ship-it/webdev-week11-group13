<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guarded = [];

    protected $casts = [
        'details'   => 'array',
        'starts_at' => 'datetime',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'event_participant')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /* ---------- Local scopes ---------- */

    // Events that have not started yet.
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now());
    }

    // Events at one venue (takes a parameter).
    public function scopeAtVenue(Builder $query, int $venueId): Builder
    {
        return $query->where('venue_id', $venueId);
    }

    // JSONB filter, arrow form: details->>'dress_code' = ?
    public function scopeDressCode(Builder $query, string $code): Builder
    {
        return $query->where('details->dress_code', $code);
    }

    // JSONB filter, containment on the whole column: details @> ?
    public function scopeDetailsContain(Builder $query, array $details): Builder
    {
        return $query->whereJsonContains('details', $details);
    }

    // Full-text search on description.
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->whereFullText('description', $term);
    }
}
