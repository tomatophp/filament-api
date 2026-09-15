<?php

namespace TomatoPHP\FilamentApi\Tests\Policies;

use TomatoPHP\FilamentApi\Tests\Models\Post;
use TomatoPHP\FilamentApi\Tests\Models\User;

/**
 * Only the author may delete a post; every other ability is left to the default (allowed).
 */
class PostPolicy
{
    public function delete(User $user, Post $post): bool
    {
        return $post->user_id === $user->id;
    }
}
