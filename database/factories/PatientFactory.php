<?php

namespace Database\Factories;

use App\Enums\BloodGroupEnum;
use App\Enums\GenderEnum;
use App\Models\Patient;
use App\Models\User;
use Database\Factories\Concerns\HasEgyptianSampleData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    use HasEgyptianSampleData;

    /**
     * Egyptian governorates mapped to their most common districts.
     *
     * @var array<string, list<string>>
     */
    private const GOVERNORATES = [
        'القاهرة' => ['مدينة نصر', 'المعادي', 'مصر الجديدة', 'شبرا', 'حلوان', 'الزمالك', 'التجمع الخامس', 'عين شمس'],
        'الجيزة' => ['الدقي', 'المهندسين', 'فيصل', 'الهرم', 'السادس من أكتوبر', 'الشيخ زايد', 'إمبابة'],
        'الإسكندرية' => ['سموحة', 'المنتزه', 'العجمي', 'سيدي جابر', 'المحرم بك', 'المنشية'],
        'الدقهلية' => ['المنصورة', 'طلخا', 'ميت غمر', 'دكرنس', 'بلقاس'],
        'الشرقية' => ['الزقازيق', 'بلبيس', 'منيا القمح', 'أبو حماد', 'العاشر من رمضان'],
        'المنوفية' => ['شبين الكوم', 'منوف', 'قويسنا', 'أشمون', 'الباجور'],
        'البحيرة' => ['دمنهور', 'كفر الدوار', 'إيتاي البارود', 'رشيد'],
        'أسيوط' => ['أسيوط', 'أبنوب', 'منفلوط', 'ديروط'],
        'سوهاج' => ['سوهاج', 'طهطا', 'جرجا', 'أخميم'],
        'أسوان' => ['أسوان', 'كوم أمبو', 'إدفو', 'درو'],
    ];

    /**
     * Common Egyptian street names.
     *
     * @var list<string>
     */
    private const STREETS = [
        'شارع النصر', 'شارع الجمهورية', 'شارع الحرية', 'شارع صلاح سالم', 'شارع الثورة',
        'شارع المحطة', 'شارع الجيش', 'شارع بورسعيد', 'شارع أحمد عرابي', 'شارع سعد زغلول',
        'شارع مصطفى النحاس', 'شارع عباس العقاد', 'شارع مكرم عبيد', 'شارع الطيران',
        'شارع فيصل', 'شارع الهرم', 'كورنيش النيل', 'شارع شبرا',
    ];

    /**
     * @var list<string>
     */
    private const ALLERGIES = [
        'حساسية من البنسلين',
        'حساسية من الأسبرين',
        'حساسية من مضادات الالتهاب غير الستيرويدية',
        'حساسية موسمية من الأتربة وحبوب اللقاح',
        'حساسية من المكسرات',
        'حساسية من اللاكتوز',
        'حساسية جلدية من بعض أنواع الصابون',
        'حساسية من مادة اليود المستخدمة في الأشعة',
    ];

    /**
     * @var list<string>
     */
    private const CHRONIC_DISEASES = [
        'ارتفاع ضغط الدم',
        'السكري من النوع الثاني',
        'الربو الشعبي',
        'قصور الغدة الدرقية',
        'فيروس الكبد الوبائي سي',
        'قصور الشريان التاجي',
        'الفشل الكلوي المزمن',
        'ارتفاع نسبة الكوليسترول في الدم',
        'الروماتويد',
        'الصرع',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake('ar_EG');

        $gender = $faker->randomElement(GenderEnum::cases());

        return [
            'gender' => $gender->value,
            'fullname' => $this->egyptianName($gender, includeSecondName: true),
            'phone' => $this->egyptianMobile(),
            'mrn' => 'MRN-'.$faker->unique()->regexify('[A-Z0-9]{8}'),
            'address' => $this->egyptianAddress(),
            'birthdate' => $faker->dateTimeBetween('-80 years', '-1 years')->format('Y-m-d'),
            'blood_group' => $faker->randomElement(BloodGroupEnum::cases())->value,
            'allergies' => $faker->boolean(60) ? $faker->randomElement(self::ALLERGIES) : null,
            'chronic_diseases' => $faker->boolean(70) ? $faker->randomElement(self::CHRONIC_DISEASES) : null,
            'emergency_name' => $this->egyptianName($faker->randomElement(GenderEnum::cases())),
            'emergency_phone' => $this->egyptianMobile(unique: false),
            'status' => $faker->randomElement(['active', 'active', 'active', 'active', 'inactive']),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'created_at' => fn () => $faker->dateTimeBetween('-2 years', '-1 month'),
            'updated_at' => fn (array $attributes) => $faker->dateTimeBetween($attributes['created_at'], 'now'),
        ];
    }

    /**
     * Indicate that the patient file is closed.
     */
    public function inactive(): static
    {
        return $this->state(fn (): array => ['status' => 'inactive']);
    }

    /**
     * Indicate that the patient gender is male.
     */
    public function male(): static
    {
        return $this->state(fn (): array => [
            'gender' => GenderEnum::MALE->value,
            'fullname' => $this->egyptianName(GenderEnum::MALE, includeSecondName: true),
        ]);
    }

    /**
     * Indicate that the patient gender is female.
     */
    public function female(): static
    {
        return $this->state(fn (): array => [
            'gender' => GenderEnum::FEMALE->value,
            'fullname' => $this->egyptianName(GenderEnum::FEMALE, includeSecondName: true),
        ]);
    }

    /**
     * Build an address such as "شارع النصر، مدينة نصر، القاهرة".
     */
    private function egyptianAddress(): string
    {
        $faker = fake('ar_EG');

        $governorate = $faker->randomElement(array_keys(self::GOVERNORATES));
        $district = $faker->randomElement(self::GOVERNORATES[$governorate]);
        $street = $faker->randomElement(self::STREETS);

        return $street.'، '.$district.'، '.$governorate;
    }
}
