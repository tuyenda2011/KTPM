<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    protected $table = 'interviews';
    protected $primaryKey = 'interview_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($interview) {
            if (!$interview->interview_id) {
                $lastInterview = static::orderBy('interview_id', 'desc')->first();
                $lastNumber = 0;
                if ($lastInterview && preg_match('/IV_(\d+)/', $lastInterview->interview_id, $matches)) {
                    $lastNumber = intval($matches[1]);
                }
                $interview->interview_id = 'IV_' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    protected $fillable = [
        'interview_id', 'user_id', 'scheduled_at', 'result', 'notes'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    // Relationship to Candidate (kế thừa từ Candidate)
    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'user_id', 'user_id');
    }

    // Resolve job through candidate.user_id -> candidate.job_id -> job_postings.job_id
    public function job()
    {
        return $this->hasOneThrough(
            JobPosting::class,
            Candidate::class,
            'user_id',
            'job_id',
            'user_id',
            'job_id'
        );
    }

    // Optional interviewer relation when interviews table contains employee_id
    public function interviewer()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'user_id');
    }
}
