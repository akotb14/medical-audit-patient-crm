<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\User;
use Database\Factories\Concerns\HasEgyptianSampleData;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    use HasEgyptianSampleData;

    /**
     * The title, the description and the resolution of an incident are kept
     * together so a complaint never resolves a problem it does not describe.
     *
     * @var list<array{title: string, description: string, resolution: string}>
     */
    private const INCIDENTS = [
        [
            'title' => 'تأخير في موعد الكشف رغم الحجز المسبق',
            'description' => 'حضر المريض في الموعد المحدد وانتظر أكثر من ساعة دون أي توضيح من الإدارة، مما أثر على حالته الصحية.',
            'resolution' => 'تم التواصل مع المريض وتقديم اعتذار رسمي، وجرى تعديل نظام حجز المواعيد للحد من التكدس.',
        ],
        [
            'title' => 'سوء معاملة من موظف الاستقبال',
            'description' => 'اشتكى المريض من أسلوب التعامل غير اللائق من أحد موظفي الاستقبال أثناء إجراءات التسجيل.',
            'resolution' => 'تم إصدار تعليمات لموظفي الاستقبال بمراجعة أسلوب التعامل، وحصل الموظف على تدريب إضافي.',
        ],
        [
            'title' => 'عدم وضوح شرح الطبيب للحالة',
            'description' => 'أفاد المريض بأن الطبيب لم يشرح التشخيص ولم يجيب على استفساراته حول مدة العلاج والآثار الجانبية.',
            'resolution' => 'تمت إعادة شرح الحالة للمريض في موعد مجاني وتسليمه تقريرًا طبيًا مفصلًا.',
        ],
        [
            'title' => 'خطأ في وصف العلاج',
            'description' => 'لاحظ المريض أن الجرعة الموصوفة تختلف عن الجرعة التي كان يتناولها سابقًا دون توضيح السبب.',
            'resolution' => 'تمت مراجعة الوصفة مع الطبيب المعالج وتصحيحها، وتكفلت العيادة بفرق التكلفة.',
        ],
        [
            'title' => 'عدم الالتزام بمواعيد المتابعة',
            'description' => 'لم يتم التواصل مع المريض لتأكيد موعد المتابعة، وعند الحضور قيل له إن الموعد ملغى.',
            'resolution' => 'تم إعادة جدولة الموعد وتعويض المريض بجلسة متابعة بدون مقابل.',
        ],
        [
            'title' => 'مشكلة في الفاتورة والحساب',
            'description' => 'تم تحصيل مبلغ إضافي غير مذكور في قائمة الأسعار المعلنة داخل العيادة.',
            'resolution' => 'تم رد المبلغ الإضافي للمريض وتحديث قائمة الأسعار المعلنة داخل العيادة.',
        ],
        [
            'title' => 'نقص في نظافة غرفة الكشف',
            'description' => 'ذكر المريض أن غرفة الكشف غير نظيفة والأدوات غير معقمة بشكل كافٍ.',
            'resolution' => 'تم التنسيق مع مسؤول النظافة لتكثيف التعقيم وجدولته على مدار اليوم.',
        ],
        [
            'title' => 'عدم توفر الأدوية الموصوفة',
            'description' => 'الصيدلية التابعة للعيادة لا يتوفر بها الدواء الموصوف واضطر المريض للبحث عنه خارجها.',
            'resolution' => 'تم توفير الدواء من مخزن العيادة وتوصيله إلى المريض.',
        ],
        [
            'title' => 'صعوبة في الوصول إلى نتائج التحاليل',
            'description' => 'لم يستلم المريض نتائج التحاليل في الوقت المتفق عليه ولم يجد من يوضح له سبب التأخير.',
            'resolution' => 'تم تصحيح إجراءات تسليم نتائج التحاليل وإخطار المريض بها هاتفيًا.',
        ],
        [
            'title' => 'عدم الرد على مكالمات المتابعة',
            'description' => 'لم يتلق المريض أي رد على مكالماته المتكررة لمتابعة حالته بعد الزيارة.',
            'resolution' => 'تم تسجيل رقم هاتف بديل للمريض ومتابعته من خلال فريق خدمة المرضى.',
        ],
    ];

    /**
     * Complaints raised from a survey keep the patient's own words as their
     * description, so they get a neutral title and a generic resolution.
     *
     * @var list<string>
     */
    private const SURVEY_TITLES = [
        'تقييم منخفض في استبيان رضا المرضى',
        'شكوى مسجلة عبر استبيان ما بعد الزيارة',
        'ملاحظات سلبية في استبيان رضا المرضى',
    ];

    /**
     * @var list<string>
     */
    private const SURVEY_RESOLUTIONS = [
        'تم التواصل مع المريض والاستماع لتفاصيل شكواه، وتم اتخاذ الإجراءات التصحيحية اللازمة.',
        'تم تصعيد الشكوى إلى مدير العيادة وتم إخطار المريض بالنتيجة النهائية.',
        'تم تقديم اعتذار رسمي للمريض وتعويضه بموعد متابعة مجاني.',
        'تمت معالجة سبب الشكوى وإخطار المريض بذلك هاتفيًا.',
        'تم تحويل الشكوى إلى القسم المختص وجارٍ استكمال الإجراءات.',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake('ar_EG');
        $incident = $faker->randomElement(self::INCIDENTS);

        return [
            'patient_id' => Patient::factory(),
            'survey_id' => null,
            'source' => 'manual',
            'title' => $incident['title'],
            'description' => $incident['description'],
            'created_at' => fn () => $faker->dateTimeBetween('-120 days', '-1 day'),
            'status' => $faker->randomElement([
                'new', 'assigned', 'investigating', 'resolved', 'resolved', 'closed', 'closed',
            ]),
            'assigned_to' => fn (array $attributes) => $attributes['status'] === 'new' ? null : User::factory(),
            'resolved_at' => fn (array $attributes) => in_array($attributes['status'], ['resolved', 'closed'], true)
                ? $this->resolutionMoment(Carbon::parse($attributes['created_at']))
                : null,
            'resolution_note' => fn (array $attributes) => in_array($attributes['status'], ['resolved', 'closed'], true)
                ? ($attributes['source'] === 'survey'
                    ? $faker->randomElement(self::SURVEY_RESOLUTIONS)
                    : $incident['resolution'])
                : null,
            'closed_at' => fn (array $attributes) => $attributes['status'] === 'closed'
                ? $this->closingMoment(Carbon::parse($attributes['resolved_at']))
                : null,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'updated_at' => fn (array $attributes) => $attributes['closed_at']
                ?? $attributes['resolved_at']
                ?? $attributes['created_at'],
        ];
    }

    /**
     * Raise the complaint from a patient survey.
     */
    public function fromSurvey(Survey $survey): static
    {
        return $this->state(fn (): array => [
            'patient_id' => $survey->patient_id,
            'survey_id' => $survey->getKey(),
            'source' => 'survey',
            'title' => fake('ar_EG')->randomElement(self::SURVEY_TITLES),
            'description' => $survey->comment
                ?? 'لم يترك المريض تعليقًا نصيًا، والتقييم المنخفض هو سبب تسجيل الشكوى.',
            'created_at' => $this->filedMoment(Carbon::parse($survey->created_at)),
        ]);
    }

    /**
     * Indicate that the complaint is still waiting for triage.
     */
    public function unassigned(): static
    {
        return $this->state(fn (): array => ['status' => 'new']);
    }

    /**
     * Indicate that the complaint was resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (): array => ['status' => 'resolved']);
    }

    /**
     * Indicate that the complaint was closed.
     */
    public function closed(): static
    {
        return $this->state(fn (): array => ['status' => 'closed']);
    }

    /**
     * Filing moment: the complaint is registered after the survey was filled.
     */
    private function filedMoment(Carbon $surveyCreatedAt): Carbon
    {
        return $this->momentBetween(
            $surveyCreatedAt->copy()->addHour(),
            $surveyCreatedAt->copy()->addDays(3)->min(now()),
        );
    }

    /**
     * Resolution moment: it always follows the filing of the complaint.
     */
    private function resolutionMoment(Carbon $createdAt): Carbon
    {
        return $this->momentBetween(
            $createdAt->copy()->addHour(),
            $createdAt->copy()->addDays(7)->min(now()),
        );
    }

    /**
     * Closing moment: it always follows the resolution.
     */
    private function closingMoment(Carbon $resolvedAt): Carbon
    {
        return $this->momentBetween(
            $resolvedAt->copy()->addHour(),
            $resolvedAt->copy()->addDays(3)->min(now()),
        );
    }
}
