<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Notification Model
 * 
 * Menyimpan notifikasi untuk admin.
 */
class Notification extends Model
{
    /**
     * Disable updated_at karena notifikasi tidak di-update
     */
    public $timestamps = false;

    /**
     * Atribut yang boleh diisi secara mass assignment
     */
    protected $fillable = [
        'type',
        'message',
        'triggered_by',
        'triggered_ip',
        'is_read',
        'created_at',
    ];

    /**
     * Casting atribut ke tipe data tertentu
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Relasi ke User yang memicu notifikasi
     */
    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope untuk notifikasi yang belum dibaca
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope untuk filter berdasarkan tipe
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
