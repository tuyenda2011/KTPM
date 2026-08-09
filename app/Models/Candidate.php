<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $table = 'candidates';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = [
        'user_id', 'job_id', 'position_applied', 'experience', 'education', 'status', 'notes', 'applied_date'
    ];

    protected $casts = [
        'applied_date' => 'date',
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function job()
    {
        return $this->belongsTo(JobPosting::class, 'job_id', 'job_id');
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class, 'user_id', 'user_id');
    }
}
