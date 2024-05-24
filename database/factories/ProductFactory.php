<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Category::class;
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'branch_id' => Branch::query()->inRandomOrder()->value('id') ?? Branch::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

    }
}
