<?php

namespace Database\Factories;

use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invitation>
 */
class InvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'template_id' => \App\Models\Template::factory(),
            'subdomain' => fake()->unique()->slug(),
            'custom_domain' => null,
            'status' => 'draft',
            'published_at' => null,
            'expires_at' => now()->addYear(),
            'view_count' => 0,
        ];
    }

    /**
     * Indicate that the invitation is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
