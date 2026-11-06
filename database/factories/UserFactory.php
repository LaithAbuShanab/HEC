<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public const TEST_MFA_SECRET = 'JBSWY3DPEHPK3PXP';

    protected static ?string $password;

    public function withoutMfa(): static
    {
        return $this->state(['app_authentication_secret' => null]);
    }

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'app_authentication_secret' => self::TEST_MFA_SECRET,
        ];
    }
}
