<?php

namespace Database\Factories;

use App\Enums\GenderEnum;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Database\Factories\Concerns\HasEgyptianSampleData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    use HasEgyptianSampleData;

    /**
     * Working days, ordered as they appear in the Egyptian week.
     *
     * @var list<string>
     */
    private const WEEK_DAYS = [
        'السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة',
    ];

    /**
     * Arabic medical specialties matching each clinic name of {@see ClinicFactory::CATALOG}.
     *
     * @var array<string, list<string>>
     */
    private const SPECIALTIES_BY_CLINIC = [
        'عيادة الباطنة' => ['الباطنة العامة', 'الجهاز الهضمي والكبد'],
        'عيادة القلب والأوعية الدموية' => ['أمراض القلب والأوعية الدموية', 'القسطرة القلبية'],
        'عيادة الأطفال' => ['طب الأطفال', 'حديثي الولادة'],
        'عيادة النساء والتوليد' => ['النساء والتوليد', 'العقم وأطفال الأنابيب'],
        'عيادة العظام' => ['جراحة العظام', 'المفاصل والعمود الفقري'],
        'عيادة الجلدية' => ['الأمراض الجلدية', 'التجميل والليزر'],
        'عيادة الأنف والأذن والحنجرة' => ['الأنف والأذن والحنجرة', 'السمع والتوازن'],
        'عيادة العيون' => ['طب وجراحة العيون', 'تصحيح الإبصار'],
        'عيادة الأسنان' => ['تقويم الأسنان', 'جراحة الفم والأسنان'],
        'عيادة المسالك البولية' => ['المسالك البولية', 'الذكورة والعقم'],
        'عيادة الجهاز الهضمي والكبد' => ['الجهاز الهضمي والكبد', 'مناظير الجهاز الهضمي'],
        'عيادة الصدر والجهاز التنفسي' => ['أمراض الصدر والحساسية', 'الجهاز التنفسي'],
        'عيادة الغدد الصماء والسكر' => ['الغدد الصماء والسكر', 'التغذية العلاجية'],
        'عيادة المخ والأعصاب' => ['المخ والأعصاب', 'الطب النفسي'],
    ];

    /**
     * Specialties used when a doctor is not tied to one of the known clinics.
     *
     * @var list<string>
     */
    private const DEFAULT_SPECIALTIES = [
        'الباطنة العامة', 'طب الأسرة', 'الطوارئ والحوادث', 'التخدير والعناية المركزة', 'الروماتيزم والمناعة',
    ];

    /**
     * Free-text notes attached to a doctor profile.
     *
     * @var list<string>
     */
    private const NOTES = [
        'يفضل الحضور قبل الموعد بنصف ساعة لإنهاء إجراءات التسجيل.',
        'لا يستقبل حالات الطوارئ خارج مواعيد العيادة.',
        'متاح للاستشارات الهاتفية للمرضى المتابعين فقط.',
        'يقبل التحويل من الأطباء الخارجيين بشرط وجود تقرير طبي.',
        'يحتاج المريض إلى إحضار التحاليل السابقة في أول زيارة.',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake('ar_EG');

        return [
            'fullname' => 'د. '.$this->egyptianName($faker->randomElement(GenderEnum::cases())),
            'phone' => $this->egyptianMobile(),
            'email' => $faker->unique()->bothify('dr.????####@clinizone.com'),
            'specialty' => $faker->randomElement(self::allSpecialties()),
            'clinic_id' => Clinic::factory(),
            'schedule_days' => $this->scheduleDays(),
            'notes' => $faker->boolean(50) ? $faker->randomElement(self::NOTES) : null,
            'status' => $faker->randomElement([
                'active', 'active', 'active', 'active', 'holiday', 'inactive',
            ]),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'created_at' => fn () => $faker->dateTimeBetween('-2 years', '-2 months'),
            'updated_at' => fn (array $attributes) => $faker->dateTimeBetween($attributes['created_at'], 'now'),
        ];
    }

    /**
     * Specialties that make sense for the given clinic name.
     *
     * @return list<string>
     */
    public static function specialtiesFor(?string $clinicName): array
    {
        return self::SPECIALTIES_BY_CLINIC[$clinicName] ?? self::DEFAULT_SPECIALTIES;
    }

    /**
     * Indicate that the doctor currently has no clinic.
     */
    public function withoutClinic(): static
    {
        return $this->state(fn (): array => ['clinic_id' => null]);
    }

    /**
     * Indicate that the doctor is on holiday.
     */
    public function onHoliday(): static
    {
        return $this->state(fn (): array => ['status' => 'holiday']);
    }

    /**
     * Indicate that the doctor is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (): array => ['status' => 'inactive']);
    }

    /**
     * Build an Arabic weekly schedule such as "السبت والأربعاء من 4:00 م حتى 9:00 م".
     */
    private function scheduleDays(): string
    {
        $faker = fake('ar_EG');

        $picked = (array) $faker->randomElements(self::WEEK_DAYS, $faker->numberBetween(2, 4));
        $days = array_values(array_intersect(self::WEEK_DAYS, $picked));

        $from = $faker->randomElement(['10:00 ص', '11:00 ص', '2:00 م', '4:00 م', '5:00 م', '6:00 م']);
        $to = $faker->randomElement(['8:00 م', '9:00 م', '10:00 م', '11:00 م']);

        return implode(' و', $days).' من '.$from.' حتى '.$to;
    }

    /**
     * @return list<string>
     */
    private static function allSpecialties(): array
    {
        return array_values(array_unique(array_merge(
            self::DEFAULT_SPECIALTIES,
            ...array_values(self::SPECIALTIES_BY_CLINIC),
        )));
    }
}
