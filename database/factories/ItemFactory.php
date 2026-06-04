<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'status' => $this->faker->randomElement(['todo', 'progress', 'ongoing', 'blocked', 'done']),
            'priority' => $this->faker->randomElement(['basse', 'moyenne', 'haute', 'critique']),
            'deadline' => $this->faker->optional()->date(),
            'description' => $this->faker->optional()->paragraph(),
            'deliverable' => $this->faker->optional()->sentence(),
            'obstacles' => $this->faker->optional()->sentence(),
            'order' => 0,
            'group_id' => Group::factory(),
        ];
    }
}
