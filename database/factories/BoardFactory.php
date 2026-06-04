<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoardFactory extends Factory
{
    protected $model = Board::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'color' => $this->faker->hexColor(),
            'workspace_id' => Workspace::factory(),
        ];
    }
}
