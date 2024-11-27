<?php

namespace Database\Factories;

use App\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionFactory extends Factory
{
    protected $model = Division::class;

    public function definition(): array
    {
        $id = strtoupper($this->faker->lexify('??'));
        $active = $this->faker->randomElement(['0', '1']);
    	return [
            'id' => $id,
            'active' => $active
    	];
    }
}
