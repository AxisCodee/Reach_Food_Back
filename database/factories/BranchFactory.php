<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'address_id' => Address::query()->inRandomOrder()->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
