<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'original_name',
        'stored_path',
        'mime_type',
        'size',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Accessors ──────────────────────────────────────────────

    /** Human-readable file size */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes < 1024)       return "{$bytes} B";
        if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    /** Icon class based on mime type */
    public function getIconAttribute(): string
    {
        if (!$this->mime_type) return 'document';
        if (str_starts_with($this->mime_type, 'image/')) return 'photo';
        if ($this->mime_type === 'application/pdf') return 'document-text';
        if (str_contains($this->mime_type, 'spreadsheet') || str_contains($this->mime_type, 'excel')) return 'table-cells';
        return 'document';
    }
}
