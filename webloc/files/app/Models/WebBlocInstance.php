<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebBlocInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'webbloc_id',
        'version',
        'settings',
        'status',
        'installation_data',
        'last_updated_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'installation_data' => 'array',
        'last_updated_at' => 'datetime',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function webBloc(): BelongsTo
    {
        return $this->belongsTo(WebBloc::class);
    }

    public function getWebBlocTypeAttribute(): string
    {
        return $this->webBloc->type ?? 'unknown';
    }

    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOutdated(): bool
    {
        return $this->version !== $this->webBloc->version;
    }

    public function getIntegrationCode(): string
    {
        $attributes = [];

        // Add common settings as attributes
        foreach ($this->settings as $key => $value) {
            if (in_array($key, ['theme', 'limit', 'sort', 'mode'])) {
                $attributes[$key] = $value;
            }
        }

        $attributeString = '';
        if (!empty($attributes)) {
            $attributeString = " w2030b_tags='" . json_encode($attributes) . "'";
        }

        return '<div w2030b="' . $this->webBloc->type . '"' . $attributeString . '>Loading...</div>';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForWebsite($query, $websiteId)
    {
        return $query->where('website_id', $websiteId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->whereHas('webBloc', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }
}
