<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'matricula',
        'cpf',
        'email',
        'phone',
        'birth_date',
        'avatar_path',
        'face_descriptor',
        'plan_id',
        'plan_started_at',
        'plan_expires_at',
        'active',
    ];

    protected $hidden = [
        'face_descriptor',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'plan_started_at' => 'date',
            'plan_expires_at' => 'date',
            'face_descriptor' => 'array',
            'active' => 'boolean',
        ];
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function checkIns()
    {
        return $this->hasMany(CheckIn::class);
    }

    public function hasFaceEnrolled(): bool
    {
        return ! empty($this->face_descriptor);
    }
}
