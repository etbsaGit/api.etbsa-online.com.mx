<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
       'comments',
       'score',
       'questions',
       'correct',
       'incorrect',
       'unanswered',
       'evaluee_id',
       'survey_id',
    ];

    public function evaluee()
    {
        return $this->belongsTo(User::class, 'evaluee_id');
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class, 'survey_id');
    }
}
