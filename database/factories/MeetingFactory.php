<?php

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'date' => $this->faker->date(),
            'attendees' => [],
            'bilan' => [],
            'recommendations' => [],
            'actions' => [],
            'workspace_id' => null,
            'user_id' => User::factory(),
        ];
    }
}
