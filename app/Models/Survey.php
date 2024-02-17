<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Survey extends Model
{
    use HasFactory;

    const TYPE_TEXT = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_SELECT = 'select';
    const TYPE_RADIO = 'radio';
    const TYPE_CHECKBOX = 'checkbox';

    protected $fillable = [
        'evaluator_id',
        'image',
        'title',
        'slug',
        'status',
        'description',
        'expire_date'
    ];

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function question()
    {
        return $this->hasMany(SurveyQuestion::class, 'survey_id');
    }

    public function answer()
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    public function evaluee()
    {
        return $this->belongsToMany(User::class, 'p_survey_evaluee', 'survey_id', 'evaluee_id')->withTimestamps();
    }
}
