<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class SurveyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'image',
        'question',
        'description',
        'data',
        'survey_id'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected $appends = ['imagen'];

    public function imagen(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image ? Storage::disk('s3')->url($this->image) : null
        );
    }

    protected function defaultPathFolder(): Attribute
    {
        return Attribute::make(
            get: fn () => "questions/id_" . $this->id,
        );
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class, 'survey_id');
    }

    public function answer() {
        return $this->hasMany(SurveyAnswer::class, 'question_id');
    }
}
