<?php

namespace App\Models;

use Database\Factories\QuizFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Quiz extends Model
{
    /** @use HasFactory<QuizFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'subject',
        'status',
        'start_at',
        'end_at',
        'duration_minutes',
    ];

    protected $appends = ['subject_image_url'];

    protected static function booted(): void
    {
        static::deleting(function (Quiz $quiz): void {
            $quiz->questions()->each(function (Question $question): void {
                if ($question->image_path) {
                    Storage::disk('public')->delete($question->image_path);
                }
            });
        });
    }

    protected function subjectImageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => Subject::imageUrlFor($this->subject));
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
