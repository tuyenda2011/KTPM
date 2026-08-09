<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentPeriod extends Model
{
    protected $table = 'recruitment_periods';
    protected $primaryKey = 'period_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($period) {
            if (!$period->period_id) {
                $maxNumber = (int) static::query()
                    ->where('period_id', 'like', 'RP\_%')
                    ->selectRaw("COALESCE(MAX(CAST(SUBSTRING(period_id, 4) AS UNSIGNED)), 0) as max_period_number")
                    ->value('max_period_number');

                do {
                    $maxNumber++;
                    $candidateId = 'RP_' . str_pad((string) $maxNumber, 3, '0', STR_PAD_LEFT);
                } while (static::where('period_id', $candidateId)->exists());

                $period->period_id = $candidateId;
            }
        });
    }

    protected $fillable = [
        'period_id',
        'name',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function jobPostings()
    {
        return $this->hasMany(JobPosting::class, 'recruitment_period_id', 'period_id');
    }
}
