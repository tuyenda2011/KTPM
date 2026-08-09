<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    protected $table = 'job_postings';
    protected $primaryKey = 'job_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($job) {
            if (!$job->job_id) {
                $maxNumber = (int) static::query()
                    ->where('job_id', 'like', 'JOB\_%')
                    ->selectRaw("COALESCE(MAX(CAST(SUBSTRING(job_id, 5) AS UNSIGNED)), 0) as max_job_number")
                    ->value('max_job_number');

                do {
                    $maxNumber++;
                    $candidateId = 'JOB_' . str_pad((string) $maxNumber, 4, '0', STR_PAD_LEFT);
                } while (static::where('job_id', $candidateId)->exists());

                $job->job_id = $candidateId;
            }
        });
    }

    protected $fillable = [
        'job_id', 'recruitment_period_id',  'title', 'department', 'location', 'salary_min', 'salary_max', 'quantity',
        'description', 'requirements', 'deadline', 'status', 'is_deleted'
    ];

    protected $casts = [
        'deadline' => 'date',
        'is_deleted' => 'boolean',
    ];

    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'job_id', 'job_id');
    }

    public function recruitmentPeriod()
    {
        return $this->belongsTo(RecruitmentPeriod::class, 'recruitment_period_id', 'period_id');
    }

    /**
     * Check if the job posting deadline has passed
     */
    public function isDeadlinePassed(): bool
    {
        return $this->deadline && $this->deadline->isPast();
    }

    public function isDeleted(): bool
    {
        return (bool) $this->is_deleted;
    }
}
