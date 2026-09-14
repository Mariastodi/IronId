<?php

namespace App\Models;

use App\Enums\CheckInMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'method',
        'match_distance',
        'checked_in_at',
    ];

    protected function casts(): array
    {
        return [
            'method' => CheckInMethod::class,
            'match_distance' => 'float',
            'checked_in_at' => 'datetime',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
