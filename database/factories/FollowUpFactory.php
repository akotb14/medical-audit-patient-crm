<?php

namespace Database\Factories;

use App\Models\FollowUp;
use App\Models\User;
use App\Models\Visit;
use Database\Factories\Concerns\HasEgyptianSampleData;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<FollowUp>
 */
class FollowUpFactory extends Factory
{
    use HasEgyptianSampleData;

    /**
     * @var list<string>
     */
    private const REASONS = [
        'متابعة ضغط الدم بعد تعديل الجرعة',
        'إعادة تقييم نسبة السكر التراكمي بعد ثلاثة أشهر',
        'مراجعة نتيجة الأشعة وتقييم الحاجة إلى علاج طبيعي',
        'متابعة التئام الجرح وخلع الغرز',
        'استكمال جرعات المضاد الحيوي والاطمئنان على الحالة',
        'متابعة وظائف الكلى بعد تغيير العلاج',
        'تقييم استجابة المريض لعلاج الحساسية الصدرية',
        'مراجعة تحاليل الغدة الدرقية وضبط الجرعة',
        'متابعة حالة الطفل بعد التطعيم',
        'إعادة فحص السمع بعد علاج التهاب الأذن',
        'متابعة انتظام السكر الصائم لمدة أسبوعين',
        'استشارة تغذية لضبط الوزن ومتابعة الحمية',
    ];

    /**
     * Every note has to describe a follow-up that actually took place, because
     * only completed follow-ups carry one.
     *
     * Kept generic on purpose: a note has to read naturally next to any reason.
     *
     * @var list<string>
     */
    private const OUTCOME_NOTES = [
        'تمت المتابعة، الحالة مستقرة والالتزام بالخطة العلاجية جيد.',
        'تحسن ملحوظ في الأعراض، تم الاستمرار على نفس الجرعة وإعادة التقييم بعد شهر.',
        'الأعراض تحت السيطرة، مع التوصية بفحص دوري كل ثلاثة أشهر.',
        'تم طلب تحاليل جديدة وسيتم مناقشة النتائج في الموعد القادم.',
        'الحالة مستقرة، لا حاجة لتغيير العلاج مع ضرورة المتابعة الدورية.',
        'استمرار الشكوى، تم تحويل المريض إلى تخصص أدق لاستكمال التشخيص.',
        'استجابة جيدة للعلاج، تم تقليل الجرعة مع متابعة شهرية.',
        'تمت المتابعة هاتفيًا، الأعراض بسيطة ولا تستدعي الحضور للعيادة.',
        'تمت إحالة المريض إلى العلاج الطبيعي لاستكمال خطة التأهيل.',
        'تم تعديل الخطة العلاجية بعد مراجعة الأعراض، مع تحديد موعد متابعة جديد.',
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
            'created_at' => fn (array $attributes) => $this->scheduledMoment(
                Carbon::parse(Visit::query()->whereKey($attributes['visit_id'])->value('date')),
            ),
            'status' => fn (array $attributes) => $this->pickStatus(Carbon::parse($attributes['created_at'])),
            'due_date' => fn (array $attributes) => $this->dueDate(
                Carbon::parse($attributes['created_at']),
                $attributes['status'],
            ),
            'completed_at' => fn (array $attributes) => $attributes['status'] === 'done'
                ? $this->completionMoment(Carbon::parse($attributes['due_date']))
                : null,
            'outcome_note' => fn (array $attributes) => $attributes['status'] === 'done'
                ? $faker->randomElement(self::OUTCOME_NOTES)
                : null,
            'reason' => $faker->randomElement(self::REASONS),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'updated_at' => fn (array $attributes) => $attributes['completed_at'] ?? $attributes['created_at'],
        ];
    }

    /**
     * Anchor the follow-up to a visit that already took place.
     */
    public function forVisit(Visit $visit): static
    {
        return $this->state(fn (): array => [
            'visit_id' => $visit->getKey(),
            'patient_id' => $visit->patient_id,
            'created_at' => $this->scheduledMoment(Carbon::parse($visit->date)),
        ]);
    }

    /**
     * Scheduling moment: the follow-up is booked when the visit is closed.
     */
    private function scheduledMoment(Carbon $visitDate): Carbon
    {
        return $this->momentBetween(
            $visitDate,
            now()->min($visitDate->copy()->addHours(8)),
        );
    }

    /**
     * A follow-up booked less than two weeks ago cannot be closed yet.
     */
    private function pickStatus(Carbon $scheduledAt): string
    {
        if ($scheduledAt->greaterThan(now()->subDays(14))) {
            return 'scheduled';
        }

        return fake('ar_EG')->randomElement([
            'done', 'done', 'done', 'done', 'missed', 'missed', 'cancelled', 'scheduled',
        ]);
    }

    /**
     * Closed follow-ups are anchored in the past, open ones always in the future.
     *
     * The upper bound of an open follow-up is widened when the visit is older than
     * the 45 days the follow-up should have been due in, because the edit form
     * only accepts an upcoming due date for a scheduled follow-up.
     */
    private function dueDate(Carbon $scheduledAt, string $status): Carbon
    {
        $closed = in_array($status, ['done', 'missed', 'cancelled'], true);

        return $closed
            ? $this->momentBetween($scheduledAt->copy()->addDay(), now()->subDay())
            : $this->momentBetween(
                now()->addDay(),
                $scheduledAt->copy()->addDays(45)->max(now()->addDays(2)),
            );
    }

    /**
     * Completion moment: shortly after the due date and never in the future.
     */
    private function completionMoment(Carbon $dueDate): Carbon
    {
        return $this->momentBetween(
            $dueDate,
            $dueDate->copy()->addHours(48)->min(now()),
        );
    }
}
