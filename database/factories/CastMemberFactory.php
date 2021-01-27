<?php

namespace Database\Factories;

use App\Models\CastMember;
use Illuminate\Database\Eloquent\Factories\Factory;

class CastMemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CastMember::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $types = [CastMember::TYPE_DIRECTOR, CastMember::TYPE_ACTOR];
        return [
            'name' => $this->faker->colorName,
            'type' => $types[array_rand($types)],
        ];
    }
}
