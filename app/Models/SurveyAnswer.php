<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'comments',
        'rating',
        'evaluee_id',
        'survey_id',
        'start_date',
        'end_date'
    ];

    public function evaluee()
    {
        return $this->belongsTo(User::class, 'evaluee_id');
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function surveyQuestionAnswer() {
        return $this->hasMany(SurveyQuestionAnswer::class, 'survey_answer_id');
    }

}
