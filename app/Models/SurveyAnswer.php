<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'answer',
        'comments',
        'rating',
        'evaluee_id',
        'question_id',
    ];

    public function evaluee()
    {
        return $this->belongsTo(User::class, 'evaluee_id');
    }

    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class,'question_id');
    }


}
