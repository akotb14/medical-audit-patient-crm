<?php

namespace Database\Factories;

use App\Models\Survey;
use App\Models\User;
use App\Models\Visit;
use Database\Factories\Concerns\HasEgyptianSampleData;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Survey>
 */
class SurveyFactory extends Factory
{
    use HasEgyptianSampleData;

    /**
     * Patient feedback grouped by satisfaction level.
     *
     * @var array<string, list<string>>
     */
    private const COMMENTS = [
        'positive' => [
            'خدمة ممتازة والطبيب شرح الحالة بالتفصيل وطمأنني على صحتي.',
            'الاستقبال كان محترمًا والانتظار لم يطل، شكرًا للفريق بالكامل.',
            'العيادة نظيفة والمواعيد منظمة، تجربة مريحة وسأكرر الزيارة.',
            'الدكتور متعاون وأجاب على كل استفساراتي بصبر.',
            'العلاج كان فعالًا والأعراض اختفت بعد أسبوع من المتابعة.',
        ],
        'neutral' => [
            'الخدمة مقبولة لكن الانتظار كان أطول من الموعد المحدد.',
            'الطبيب جيد لكن احتجت إلى إعادة الزيارة أكثر من مرة.',
            'التعامل جيد بشكل عام، وأنصح بتحسين تنظيم المواعيد.',
            'التشخيص كان مناسبًا لكن أسعار الكشف مرتفعة قليلًا.',
        ],
        'negative' => [
            'انتظرت أكثر من ساعة رغم وجود موعد مسبق ولم يعتذر أحد.',
            'لم يشرح الطبيب الحالة بوضوح ولم أحصل على تقرير مكتوب.',
            'تم إعطائي دواء غير مناسب واضطررت لزيارة طبيب آخر.',
            'معاملة غير لائقة من موظف الاستقبال أثناء التسجيل.',
            'العيادة مزدحمة بشكل مبالغ فيه ولا توجد خصوصية للمريض.',
        ],
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
            'visit_id' => Visit::factory()->completed(),
            'patient_id' => fn (array $attributes) => Visit::query()
                ->whereKey($attributes['visit_id'])
                ->value('patient_id'),
            'rating' => $faker->randomElement([5, 5, 5, 4, 4, 4, 4, 3, 3, 2, 1]),
            'comment' => fn (array $attributes) => $faker->boolean(80)
                ? $faker->randomElement(self::commentsFor((int) $attributes['rating']))
                : null,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'created_at' => fn () => $faker->dateTimeBetween('-90 days', 'now'),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }

    /**
     * Anchor the survey to the visit it evaluates.
     */
    public function forVisit(Visit $visit): static
    {
        return $this->state(fn (): array => [
            'visit_id' => $visit->getKey(),
            'patient_id' => $visit->patient_id,
            'created_at' => $this->filledMoment(Carbon::parse($visit->date)),
        ]);
    }

    /**
     * Indicate the rating given by the patient.
     */
    public function rating(int $rating): static
    {
        return $this->state(fn (): array => ['rating' => $rating]);
    }

    /**
     * Feedback statements matching the given rating.
     *
     * @return list<string>
     */
    private static function commentsFor(int $rating): array
    {
        return match (true) {
            $rating >= 4 => self::COMMENTS['positive'],
            $rating === 3 => self::COMMENTS['neutral'],
            default => self::COMMENTS['negative'],
        };
    }

    /**
     * Feedback moment: the patient fills the survey shortly after the visit.
     */
    private function filledMoment(Carbon $visitDate): Carbon
    {
        return $this->momentBetween($visitDate->copy()->addHour(), now());
    }
}
