<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Priority extends Model
{
    protected $fillable = ['name', 'sla_hours', 'color_code', 'sort_order'];

    protected function casts(): array
    {
        return [
            'sla_hours'  => 'float',
            'sort_order' => 'integer',
        ];
    }

    // ── Relationships ──────────────────────────────────────────

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    // ── Scopes ─────────────────────────────────────────────────

    /** Default ordering: most urgent first */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    // ── Helpers ────────────────────────────────────────────────

    /** Returns Tailwind badge classes for this priority */
    public function badgeClasses(): string
    {
        return match (strtolower($this->name)) {
            'urgent' => 'bg-red-100 text-red-700',
            'high'   => 'bg-orange-100 text-orange-700',
            'medium' => 'bg-yellow-50 text-yellow-600',
            default  => 'bg-blue-50 text-blue-600',   // low
        };
    }
}
