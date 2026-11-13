<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Jobs & markets models — spec S11, S12.
 */
class JobPosting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employer_id', 'title', 'description', 'type', 'location',
        'qualifications', 'compensation', 'application_deadline',
        'published', 'moderation_status',
    ];

    protected function casts(): array
    {
        return ['application_deadline' => 'date', 'published' => 'boolean'];
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'job_id');
    }
}

class Application extends Model
{
    protected $fillable = ['job_id', 'applicant_id', 'cover_message', 'portfolio_path', 'status'];

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class, 'job_id');
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

class Market extends Model
{
    protected $fillable = ['name', 'locality', 'operating_days', 'contact', 'contributed_by', 'moderation_status'];

    public function contributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contributed_by');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(MarketPrice::class);
    }
}

class MarketPrice extends Model
{
    protected $fillable = ['market_id', 'commodity', 'unit', 'price', 'contributed_by', 'verification_status', 'price_date'];

    protected function casts(): array
    {
        return ['price_date' => 'date'];
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }
}
