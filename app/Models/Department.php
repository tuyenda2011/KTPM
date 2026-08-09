<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'department_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($department) {
            if (!$department->department_id) {
                $lastDept = static::orderBy('department_id', 'desc')->first();
                $lastNumber = 0;
                if ($lastDept && preg_match('/DEPT_(\d+)/', $lastDept->department_id, $matches)) {
                    $lastNumber = intval($matches[1]);
                }
                $department->department_id = 'DEPT_' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    protected $fillable = [
        'department_id',
        'name',
        'description',
        'manager_user_id',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id', 'department_id');
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_user_id', 'user_id');
    }
}