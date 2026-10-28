<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Clinic>
 */
class ClinicFactory extends Factory
{
    /**
     * Arabic clinic names mapped to a short description.
     *
     * @var array<string, string>
     */
    public const CATALOG = [
        'عيادة الباطنة' => 'متابعة أمراض الباطنة المزمنة والحالات العامة للبالغين.',
        'عيادة القلب والأوعية الدموية' => 'تشخيص ومتابعة أمراض القلب وضغط الدم وقياس رسم القلب والإيكو.',
        'عيادة الأطفال' => 'متابعة نمو الأطفال والتطعيمات الإجبارية وعلاج أمراض الطفولة الشائعة.',
        'عيادة النساء والتوليد' => 'متابعة الحمل والولادة وصحة المرأة ومتابعة أمراض النساء.',
        'عيادة العظام' => 'علاج الكسور وإصابات الملاعب وخشونة المفاصل وآلام العمود الفقري.',
        'عيادة الجلدية' => 'علاج الأمراض الجلدية وجلسات الليزر والتجميل غير الجراحي.',
        'عيادة الأنف والأذن والحنجرة' => 'علاج أمراض الأنف والجيوب الأنفية والأذن والحنجرة وتقييم حاسة السمع.',
        'عيادة العيون' => 'قياس النظر ومتابعة أمراض الشبكية والمياه البيضاء وضغط العين.',
        'عيادة الأسنان' => 'حشو وخلع الأسنان وتقويمها وزراعة الأسنان وجراحة الفم.',
        'عيادة المسالك البولية' => 'علاج حصوات الكلى والمسالك ومتابعة أمراض البروستاتا.',
        'عيادة الجهاز الهضمي والكبد' => 'مناظير الجهاز الهضمي ومتابعة التهاب الكبد وأمراض القولون.',
        'عيادة الصدر والجهاز التنفسي' => 'علاج الربو الشعبي والحساسية الصدرية والتهابات الجهاز التنفسي.',
        'عيادة الغدد الصماء والسكر' => 'متابعة مرضى السكري واضطرابات الغدة الدرقية والسمنة.',
        'عيادة المخ والأعصاب' => 'تشخيص الصداع النصفي والصرع وآلام الأعصاب وأمراض المخ.',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake('ar_EG');

        $name = $faker->unique()->randomElement(array_keys(self::CATALOG));

        return [
            'name' => $name,
            'description' => self::CATALOG[$name],
            'status' => $faker->randomElement([
                'active', 'active', 'active', 'active', 'active', 'maintenance', 'inactive',
            ]),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'created_at' => fn () => $faker->dateTimeBetween('-2 years', '-2 months'),
            'updated_at' => fn (array $attributes) => $faker->dateTimeBetween($attributes['created_at'], 'now'),
        ];
    }

    /**
     * Indicate that the clinic is under maintenance.
     */
    public function underMaintenance(): static
    {
        return $this->state(fn (): array => ['status' => 'maintenance']);
    }

    /**
     * Indicate that the clinic is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (): array => ['status' => 'inactive']);
    }
}
