<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isGuru();
    }

    public function view(User $user, Quiz $quiz): bool
    {
        return $user->isGuru() && $quiz->creator_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isGuru();
    }

    public function update(User $user, Quiz $quiz): bool
    {
        return $user->isGuru() && $quiz->creator_id === $user->id;
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $user->isGuru() && $quiz->creator_id === $user->id;
    }
}
