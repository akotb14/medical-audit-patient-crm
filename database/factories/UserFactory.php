<?php

namespace Database\Factories;

use App\Enums\GenderEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use Database\Factories\Concerns\HasEgyptianSampleData;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    use HasEgyptianSampleData;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake('ar_EG');

        return [
            'name' => $this->egyptianName($faker->randomElement(GenderEnum::cases())),
            'username' => $faker->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Assign the receptionist role to the created user.
     */
    public function receptionist(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->assignRole(RoleEnum::RECEPTIONIST->value);
        });
    }
}
