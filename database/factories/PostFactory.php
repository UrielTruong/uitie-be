<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'category_id'    => Category::factory(),
            'parent_post_id' => null,
            'content'        => $this->faker->paragraph(),
            'visibility'     => Post::VISIBILITY_PUBLIC,
            'status'         => Post::STATUS_PENDING,
            'reject_reason'  => null,
            'is_edited'      => false,
        ];
    }

    // ----------------------------------------------------------------
    // Visibility states
    // ----------------------------------------------------------------

    public function public(): static
    {
        return $this->state(['visibility' => Post::VISIBILITY_PUBLIC]);
    }

    public function private(): static
    {
        return $this->state(['visibility' => Post::VISIBILITY_PRIVATE]);
    }

    // ----------------------------------------------------------------
    // Status states
    // ----------------------------------------------------------------

    public function pending(): static
    {
        return $this->state([
            'status'        => Post::STATUS_PENDING,
            'reject_reason' => null,
        ]);
    }

    public function accepted(): static
    {
        return $this->state([
            'status'        => Post::STATUS_ACCEPTED,
            'reject_reason' => null,
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status'        => Post::STATUS_REJECTED,
            'reject_reason' => $this->faker->sentence(),
        ]);
    }

    // ----------------------------------------------------------------
    // Other states
    // ----------------------------------------------------------------

    public function edited(): static
    {
        return $this->state(['is_edited' => true]);
    }

    public function reply(Post $parent): static
    {
        return $this->state(['parent_post_id' => $parent->id]);
    }
}
