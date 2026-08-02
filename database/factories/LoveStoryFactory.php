<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\LoveStory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoveStory>
 */
class LoveStoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'title' => fake()->words(3, true),
            'date_label' => fake()->monthName().' '.fake()->year(),
            'description' => fake()->paragraph(),
            'sort_order' => 0,
        ];
    }
}
