<?php

namespace App\Models;

use Database\Factories\SubjectFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Subject extends Model
{
    /** @use HasFactory<SubjectFactory> */
    use HasFactory;

    protected $fillable = ['name', 'image_path'];

    protected $appends = ['image_url'];

    protected static function booted(): void
    {
        static::deleted(function (Subject $subject): void {
            if ($subject->image_path) {
                Storage::disk('public')->delete($subject->image_path);
            }
        });
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->image_path ? Storage::url($this->image_path) : null);
    }

    public static function canonicalName(string $name): string
    {
        return static::query()
            ->whereRaw('lower(name) = lower(?)', [$name])
            ->value('name') ?? $name;
    }

    public static function imageUrlFor(string $name): ?string
    {
        $path = static::query()
            ->whereRaw('lower(name) = lower(?)', [$name])
            ->value('image_path');

        return $path ? Storage::url($path) : null;
    }
}
