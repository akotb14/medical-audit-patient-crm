<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Database\Factories\Concerns\HasEgyptianSampleData;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    use HasEgyptianSampleData;

    /**
     * @var list<string>
     */
    private const REASONS = [
        'يعاني المريض من ارتفاع ضغط الدم ويحتاج إلى متابعة دورية',
        'شكوى من آلام أسفل الظهر منذ أسبوع مع صعوبة في الحركة',
        'متابعة نتائج التحاليل وضبط جرعة دواء السكري',
        'ارتفاع في درجة الحرارة مصحوب بكحة مستمرة منذ ثلاثة أيام',
        'ألم شديد في الصدر عند المجهود مع ضيق في التنفس',
        'مراجعة بعد تركيب الدعامة القلبية وتقييم الحالة العامة',
        'التهاب في الحلق مع صعوبة في البلع وتضخم في اللوزتين',
        'متابعة الحمل والاطمئنان على نبض الجنين',
        'حساسية جلدية منتشرة بالذراعين مع حكة مستمرة',
        'كسر في عظمة الرسغ نتيجة السقوط ويحتاج إلى تركيب جبيرة',
        'صداع نصفي متكرر لا يستجيب للمسكنات العادية',
        'ألم في المفاصل مع تورم في الركبة اليمنى',
        'متابعة ما بعد عملية استئصال المرارة',
        'التهاب في الأذن الوسطى مع ضعف في السمع',
        'التهاب في المسالك البولية مع حرقة عند التبول',
        'قياس النظر ومراجعة الطبيب لضعف الرؤية الليلية',
        'ألم في الأسنان الأمامية مع تورم في اللثة',
        'متابعة حالة قصور الغدة الدرقية وتعديل الجرعة',
        'شكوى من إمساك مزمن وانتفاخ متكرر بالبطن',
        'فحص دوري شامل قبل السفر مع طلب تقرير طبي',
        'إصابة رياضية في أربطة الكاحل خلال مباراة كرة القدم',
        'التهاب في الجيوب الأنفية مع صداع في مقدمة الرأس',
        'متابعة ما بعد الجبس وتقييم التئام العظم',
        'حالة قلق وتوتر مصحوبة بأرق منذ شهرين',
        'تساقط شديد في الشعر مع ضعف عام في الجسم',
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
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'visit_status' => 'created',
            'date' => fn () => $faker->dateTimeBetween('+1 day', '+30 days'),
            'reason' => $faker->randomElement(self::REASONS),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'created_at' => fn (array $attributes) => $this->bookingMoment(Carbon::parse($attributes['date'])),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }

    /**
     * Indicate that the visit already took place.
     */
    public function completed(): static
    {
        return $this->state(fn (): array => [
            'visit_status' => 'completed',
            'date' => fake('ar_EG')->dateTimeBetween('-180 days', '-2 days'),
            'updated_at' => fn (array $attributes) => $this->closingMoment(
                Carbon::parse($attributes['date']),
                Carbon::parse($attributes['created_at']),
            ),
        ]);
    }

    /**
     * Indicate that the visit was cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'visit_status' => 'cancelled',
            'date' => fake('ar_EG')->dateTimeBetween('-60 days', '+10 days'),
        ]);
    }

    /**
     * Booking moment: always after the previous visit slot opened and never in the future.
     */
    private function bookingMoment(Carbon $visitDate): Carbon
    {
        return $this->momentBetween(
            $visitDate->copy()->subDays(14),
            now()->min($visitDate->copy()->subDay()),
        );
    }

    /**
     * Closing moment: the visit is closed shortly after it takes place.
     */
    private function closingMoment(Carbon $visitDate, Carbon $bookingMoment): Carbon
    {
        return $this->momentBetween(
            $bookingMoment,
            now()->min($visitDate->copy()->addHours(8)),
        );
    }
}
