<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Board;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'color' => '#0091CD',
            'order' => 0,
            'board_id' => Board::factory(),
        ];
    }
}
