<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'date',
        'api_requests',
        'unique_users',
        'webbloc_interactions',
        'errors',
        'bandwidth_used',
        'response_time_avg',
        'metadata',
    ];

    protected $casts = [
        'date' => 'date',
        'metadata' => 'array',
        'response_time_avg' => 'float',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function getFormattedBandwidthAttribute(): string
    {
        return $this->formatBytes($this->bandwidth_used);
    }

    public function getErrorRateAttribute(): float
    {
        if ($this->api_requests === 0) {
            return 0;
        }

        return round(($this->errors / $this->api_requests) * 100, 2);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeForWebsite($query, $websiteId)
    {
        return $query->where('website_id', $websiteId);
    }

    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}