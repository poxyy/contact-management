<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'street' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'province' => $this->faker->state,
            'country' => $this->faker->country,
            'postal_code' => $this->faker->postcode,
        ];
    }
}
