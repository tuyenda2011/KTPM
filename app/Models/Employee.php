<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = [
        'user_id', 'employee_code', 'position', 'identity_card', 'marital_status', 'hometown',
        'current_address', 'start_date', 'department_id', 'ethnicity', 'religion',
        'nationality', 'education_level', 'previous_experience', 'avatar_path', 'cv_path', 'contract_path', 'notes', 'status',
        'degree', 'school_name', 'certificates', 'language_certificates'
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }
}
