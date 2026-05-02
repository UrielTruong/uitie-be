<?php

namespace Database\Factories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Default: TARGET_POST — an toàn, không conflict
        $reporterId = fake()->randomElement([5, 6, 7]);

        return [
            'reporter_id'      => $reporterId,
            'reported_user_id' => null,
            'reported_post_id' => fake()->randomElement(range(6, 18)),
            'reason'           => fake()->sentence(),
            'status'           => Report::STATUS_PENDING,
            'target_type'      => Report::TARGET_POST,
            'resolved_at'      => null,
            'created_at'       => fake()->dateTime(),
            'updated_at'       => fake()->dateTime(),
        ];
    }

    public function forUser(): static
    {
        return $this->state(function () {
            $reportedUserId = fake()->randomElement([5, 6, 7]);
            $reporterId = fake()->randomElement(
                array_values(array_diff([5, 6, 7], [$reportedUserId]))
            );

            return [
                'reporter_id'      => $reporterId,
                'reported_user_id' => $reportedUserId,
                'reported_post_id' => null, // bắt buộc null
                'target_type'      => Report::TARGET_USER,
            ];
        });
    }

    public function resolved(): static
    {
        return $this->state(fn() => [
            'status'      => Report::STATUS_RESOLVED,
            'resolved_at' => fake()->dateTime(),
        ]);
    }
}
