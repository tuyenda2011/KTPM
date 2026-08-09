<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'password',
        'role',
        'birth_date',
        'gender',
        'phone',
        'address',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    // Relationship to Employee (if user is an employee)
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id', 'user_id');
    }

    // Relationship to Candidate (if user is a candidate)
    public function candidate()
    {
        return $this->hasOne(Candidate::class, 'user_id', 'user_id');
    }

    // Get avatar path from storage folder
    public function getAvatarPathAttribute()
    {
        $employeeFolderPath = "employees/{$this->user_id}";
        
        // Check if folder exists in public disk
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($employeeFolderPath)) {
            return null;
        }
        
        // Get all files in the folder
        $files = \Illuminate\Support\Facades\Storage::disk('public')->files($employeeFolderPath);
        
        // Filter image files
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $imageExtensions)) {
                // Return relative path
                return $file;
            }
        }
        
        return null;
    }
}
