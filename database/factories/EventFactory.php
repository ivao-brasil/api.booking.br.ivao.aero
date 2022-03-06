<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $start  = time() + rand(1 * 24 * 60 * 60, 14 * 24 * 60 * 60);
        $end    = $start + rand(3 * 60 * 60, 5 * 60 * 60);

        return [
            'division' => 'BR',
            'dateStart' => $start,
            'dateEnd' => $end,
            'eventName' => $this->faker->sentence(3),
            'privateSlots' => $this->faker->numberBetween(0, 1),
            'status' => $this->faker->randomElement(['created', 'scheduled', 'finished']),
            'createdBy' => 1,
            'description' => $this->faker->text(300),
            'banner' => 'https://wallpapercave.com/wp/wp4728116.jpg',
            'atcBooking' => $this->faker->url(),
            'atcBriefing' => $this->faker->url(),
            'pilotBriefing' => $this->faker->url(),
            'public' => $this->faker->numberBetween(0, 1)
        ];
    }
}
