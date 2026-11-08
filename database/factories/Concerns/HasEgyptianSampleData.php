<?php

namespace Database\Factories\Concerns;

use App\Enums\GenderEnum;
use Illuminate\Support\Carbon;

/**
 * Helpers shared by the application factories to build realistic Egyptian data.
 */
trait HasEgyptianSampleData
{
    /**
     * Build an Egyptian full name, optionally with a second given name.
     */
    protected function egyptianName(GenderEnum $gender, bool $includeSecondName = false): string
    {
        $names = [$this->egyptianFirstName($gender)];

        if ($includeSecondName) {
            $names[] = $this->egyptianFirstName($gender);
        }

        $names[] = fake('ar_EG')->lastName();

        return implode(' ', $names);
    }

    /**
     * Build an Egyptian mobile number (01 + carrier digit + 8 digits).
     */
    protected function egyptianMobile(bool $unique = true): string
    {
        $faker = fake('ar_EG');

        $digits = $unique
            ? $faker->unique()->numerify('########')
            : $faker->numerify('########');

        return '+201'.$faker->randomElement(['0', '1', '2', '5']).$digits;
    }

    /**
     * Pick a random moment inside the window, falling back to its end when the window is empty.
     */
    protected function momentBetween(Carbon $earliest, Carbon $latest): Carbon
    {
        if ($earliest->greaterThan($latest)) {
            return $latest->copy();
        }

        return Carbon::parse(fake('ar_EG')->dateTimeBetween($earliest, $latest));
    }

    private function egyptianFirstName(GenderEnum $gender): string
    {
        return $gender === GenderEnum::MALE
            ? fake('ar_EG')->firstNameMale()
            : fake('ar_EG')->firstNameFemale();
    }
}
