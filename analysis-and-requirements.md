# Analysis & Requirements
## Medical Audit + Patient CRM Platform (Laravel)

---

## 1. نظرة عامة (Overview)

**اسم المشروع:** Medical Audit & Patient CRM Platform

**الفكرة:** منصة ويب تساعد المنشآت الطبية على:
1. **تقييم جودة شغلها** مقابل معايير ولوائح محددة (Medical Audit).
2. **فهم احتياجات المرضى ومتابعتهم وقياس رضاهم** (Medical CRM).

**المرجع للمجال:** نشاط شركة Clinizone، وهي شركة HealthTech بتقدم أدوات دعم القرار الإكلينيكي، ورؤيتها ربط الـ Medical Audit بالـ Medical CRM لتحسين نتايج المرضى ورضاهم وولائهم.

**الهدف من المشروع:** مشروع احترافي بمستوى شغل الشركات (Architecture نظيفة، Transactions، Audit log، Tests، Docker، CI) يوضح فهم المجال الطبي والـ Backend.

---

## 2. فهم الـ Business

### 2.1 Medical Audit
تقييم منشأة طبية للتأكد من التزامها باللوائح والمعايير. يغطي أربع محاور رئيسية:

| المحور | أمثلة على المعايير |
|---|---|
| Clinical Care (الرعاية الإكلينيكية) | توثيق الحالة، اتباع البروتوكولات العلاجية |
| Medication Management (إدارة الأدوية) | تخزين الأدوية، صرف الدواء، تسجيل الجرعات |
| Patient Safety (سلامة المريض) | مكافحة العدوى، تعريف المريض، منع الحوادث |
| Quality Management (إدارة الجودة) | تسجيل الشكاوى، مؤشرات الأداء، التدريب |

**الأهداف:**
- تحديد نقاط التحسين
- قياس كفاءة الخدمة المقدمة
- تحسين نتايج المرضى

### 2.2 Medical CRM
إدارة العلاقة مع المرضى: ملفاتهم، المتابعات، استبيانات الرضا، الشكاوى.

### 2.3 العلاقة بينهم
- الـ Audit يضمن أن الرعاية بمعايير صحيحة.
- الـ CRM يضمن فهم المريض والاستجابة لاحتياجاته.
- الاتنين مع بعض = **جودة + رضا + ولاء**.

### 2.4 المشاكل اللي النظام بيحلها
- التدقيق بيتم على ورق أو Excel، فصعب المقارنة والمتابعة.
- مفيش حساب موحد لنسبة الامتثال.
- المخالفات بتتسجل ومفيش متابعة لتصحيحها.
- شكاوى المرضى وملاحظاتهم متفرقة وبلا تحليل.
- مفيش رؤية للإدارة بالاتجاه العام للجودة عبر الوقت.

### 2.5 خدمات Clinizone وربطها بالمنصة

الخدمات المعلنة على موقع الشركة، وكيف تنعكس على المشروع:

| خدمة Clinizone | الموديول المقابل في المنصة |
|---|---|
| Medical & Dental Audit | Audit Templates / Sessions / Compliance Score |
| Medical & Dental CRM | Patient CRM + استبيانات الرضا |
| نظام إدارة الشكاوى والاستفسارات | Complaints & Inquiries |
| Revenue Cycle Management (مطالبات التأمين، المرفوضات، إعادة الإرسال) | Claims & Rejections |
| Healthcare KPIs (وقت الانتظار، الرضا، معدل المتابعة...) | KPIs Module |
| Infection Control audits (نظافة الإيدين، PPE، التعقيم...) | Audit Templates جاهزة (Seed) |
| Contact Center | يُؤجَّل للمرحلة التانية (Inquiries كبداية) |
| Digital Transformation (HIS, CRM, Dashboards) | المنصة نفسها + API وتقارير |

> الخدمات مأخوذة من الموقع الرسمي، ومعلومات الأنظمة الداخلية عندهم غير معروفة، فالنطاق هنا تقدير للمشروع التعليمي وليس نسخة من منتجاتهم.

---

## 3. المستخدمين (Actors)

| الدور | الوصف | الصلاحيات الأساسية |
|---|---|---|
| **Admin** | مدير النظام | كل شيء، إدارة المستخدمين والـ Roles |
| **Quality Manager** | مسؤول الجودة | إنشاء Templates، اعتماد Audits، متابعة Corrective Actions، التقارير |
| **Auditor** | المدقق | تنفيذ Audits، تسجيل الإجابات والأدلة |
| **Facility Staff** | موظف المنشأة | تنفيذ Corrective Actions المسندة له، إدارة المرضى والمتابعات |
| **Claims Officer** | مسؤول المطالبات | إدارة المطالبات التأمينية والمرفوضات وإعادة الإرسال |
| **Contact Center Agent** | موظف خدمة العملاء | تسجيل الشكاوى والاستفسارات ومتابعتها |

---

## 4. نطاق المشروع (Scope)

### 4.1 داخل النطاق (MVP)
1. Auth والصلاحيات
2. Facilities (المنشآت والأقسام)
3. Audit Templates (مع Versioning)
4. Audit Sessions والإجابات والأدلة
5. حساب Compliance Score
6. Corrective Actions
7. Compliance Dashboard
8. Patient CRM الأساسي (مرضى، متابعات، استبيانات رضا)
9. Complaints & Inquiries (إدارة الشكاوى والاستفسارات بـ SLA)
10. Claims & Rejections (تتبع المطالبات التأمينية وأسباب الرفض)
11. KPIs Module (مؤشرات أداء قابلة للتعريف لكل منشأة)
12. التقارير والتصدير (PDF / Excel) وAPI موثق

### 4.2 خارج النطاق (المرحلة التانية)
- ربط مع أنظمة خارجية (HIS / EMR)
- تطبيق موبايل
- إشعارات SMS / WhatsApp
- تحليلات متقدمة بالـ AI
- تعدد اللغات الكامل
- نظام الفواتير والعقود

---

## 5. المتطلبات الوظيفية (Functional Requirements)

### FR-1: Auth والصلاحيات
- FR-1.1 تسجيل الدخول والخروج.
- FR-1.2 إدارة المستخدمين (إنشاء، تعديل، تعطيل).
- FR-1.3 Roles وPermissions (Admin / Quality Manager / Auditor / Staff).
- FR-1.4 المستخدم يشوف بس المنشآت المسموح له بيها.

### FR-2: Facilities
- FR-2.1 إدارة المنشآت (اسم، نوع: عيادة / مستشفى / معمل، مدينة، بيانات تواصل).
- FR-2.2 إدارة الأقسام داخل كل منشأة.
- FR-2.3 ربط المستخدمين بالمنشآت.

### FR-3: Audit Templates
- FR-3.1 إنشاء Template يتكون من: Domains (محاور) ← Criteria (معايير) ← أسئلة.
- FR-3.2 كل Criterion له **وزن (Weight)**.
- FR-3.3 **Versioning:** بعد استخدام الـ Template في Audit، أي تعديل ينتج نسخة جديدة.
- FR-3.4 تفعيل / إيقاف الـ Template.
- FR-3.5 نسخ Template موجود (Duplicate).
- FR-3.6 Seed لـ Templates جاهزة (مثل Infection Control: Hand Hygiene، PPE، التعقيم، نظافة البيئة، غرف العمليات).

### FR-4: Audit Sessions
- FR-4.1 إنشاء Audit لمنشأة (أو قسم) بناءً على Template ونسخة محددة.
- FR-4.2 إسناد الـ Audit لمدقق وتحديد تاريخ.
- FR-4.3 تسجيل إجابة لكل معيار: `Compliant / Partial / Non-compliant / N/A`.
- FR-4.4 إرفاق **أدلة** (ملفات / صور) وملاحظات لكل إجابة.
- FR-4.5 حفظ كمسودة (Draft) واستكمال لاحقًا.
- FR-4.6 **الاعتماد (Finalize):** بعده الـ Audit يتقفل ولا يمكن تعديل إجاباته.
- FR-4.7 تصدير تقرير الـ Audit (PDF).

### FR-5: Compliance Score
- FR-5.1 حساب النسبة تلقائيًا لكل محور ولـ Audit ككل.
- FR-5.2 استبعاد إجابات N/A من الحساب.
- FR-5.3 تصنيف النتيجة (مثلاً: ممتاز ≥ 90% / مقبول 75–89% / ضعيف < 75%) بحدود قابلة للتعديل.

### FR-6: Corrective Actions
- FR-6.1 فتح Corrective Action **تلقائيًا** لأي إجابة Non-compliant.
- FR-6.2 كل Action له: وصف، مسؤول، Deadline، أولوية، حالة (`Open / In progress / Done / Verified`).
- FR-6.3 تعليقات ومرفقات على الـ Action.
- FR-6.4 تمييز الـ Actions المتأخرة (Overdue).
- FR-6.5 التحقق (Verification) من التنفيذ بواسطة Quality Manager.

### FR-7: Compliance Dashboard
- FR-7.1 نسبة الامتثال لكل محور.
- FR-7.2 اتجاه الامتثال عبر الوقت (Trend) لكل منشأة.
- FR-7.3 عدد الـ Corrective Actions المفتوحة والمتأخرة.
- FR-7.4 مقارنة بين المنشآت أو الأقسام.
- FR-7.5 فلترة بالفترة الزمنية والمنشأة والمحور.

### FR-8: Patient CRM
- FR-8.1 إدارة ملفات المرضى (بيانات أساسية، منشأة).
- FR-8.2 تسجيل الزيارات والمتابعات (Follow-ups) مع تاريخ المتابعة القادمة.
- FR-8.3 استبيانات رضا (Surveys): أسئلة بتقييم رقمي وتعليق حر.
- FR-8.4 تسجيل الشكاوى بحالات (`New / Investigating / Resolved / Closed`).
- FR-8.5 مؤشرات رضا لكل منشأة (متوسط التقييم، عدد الشكاوى).

### FR-9: الـ Audit Log
- FR-9.1 تسجيل أي تعديل على: Templates، Audits، Corrective Actions، بيانات المرضى.
- FR-9.2 تسجيل من فتح ملف مريض ومتى.
- FR-9.3 عرض السجل للـ Admin فقط.

### FR-10: Complaints & Inquiries
- FR-10.1 تسجيل شكوى أو استفسار (القناة: هاتف / واتساب / إيميل / حضوري، التصنيف، المنشأة، المريض اختياري).
- FR-10.2 الحالات: `New / Assigned / Investigating / Resolved / Closed`.
- FR-10.3 إسناد لمسؤول، مع SLA (مهلة الرد والحل حسب الأولوية) وتمييز المتأخر.
- FR-10.4 سجل تواصل وتعليقات ومرفقات.
- FR-10.5 ربط الشكوى المتكررة بـ Corrective Action أو Audit.
- FR-10.6 مؤشرات: زمن الحل، التصنيفات الأكثر تكرارًا.

### FR-11: Claims & Rejections
- FR-11.1 تسجيل مطالبة تأمينية (مريض، شركة تأمين، الخدمات، القيمة، التاريخ).
- FR-11.2 الحالات: `Draft / Reviewed / Submitted / Approved / Rejected / Resubmitted`.
- FR-11.3 مراجعة طبية وفنية ومالية قبل الإرسال (Checklist مراجعة).
- FR-11.4 تسجيل سبب الرفض (من قائمة تصنيفات) وإعادة الإرسال بعد التصحيح.
- FR-11.5 تحليل المرفوضات: نسبة الرفض، الأسباب الأكثر تكرارًا، حسب شركة التأمين والمنشأة.
- FR-11.6 تقرير تسوية (Reconciliation) بين المطالب به والمحصّل.

### FR-12: KPIs Module
- FR-12.1 تعريف KPI (الاسم، الصيغة، الوحدة، الهدف، التكرار).
- FR-12.2 إدخال قيم يدويًا أو احتسابها من بيانات النظام (مثل نسبة الرضا، معدل المتابعة).
- FR-12.3 مقارنة القيمة الفعلية بالهدف وتلوين الحالة (أخضر / أصفر / أحمر).
- FR-12.4 عرض KPIs في الـ Dashboard مع الاتجاه عبر الوقت.
- FR-12.5 أمثلة جاهزة: وقت انتظار المريض، معدل الإلغاء، رضا المرضى، معدل المتابعة.

### FR-13: التقارير والتكامل
- FR-13.1 تصدير تقارير Audit والمطالبات بصيغة PDF.
- FR-13.2 تصدير واستيراد Excel (المطالبات، KPIs، المرضى).
- FR-13.3 REST API موثق للموديولات الأساسية بمصادقة (Sanctum).

---

## 6. قواعد العمل (Business Rules)

| # | القاعدة |
|---|---|
| BR-1 | الـ Template المستخدم في Audit لا يُعدّل مباشرة، وأي تعديل ينشئ نسخة جديدة. |
| BR-2 | الـ Audit المعتمد (Finalized) لا يمكن تعديل إجاباته أو حذفه. |
| BR-3 | **Compliance Score** = Σ (الوزن × نتيجة الإجابة) ÷ Σ (أوزان المعايير غير N/A) × 100. |
| BR-4 | قيم النتيجة: Compliant = 1، Partial = 0.5، Non-compliant = 0. |
| BR-5 | أي إجابة Non-compliant تفتح Corrective Action تلقائيًا عند اعتماد الـ Audit. |
| BR-6 | الـ Action يصير Overdue لو عدّى الـ Deadline ولم يكتمل. |
| BR-7 | لا يمكن اعتماد Audit فيه معايير بدون إجابة. |
| BR-8 | الـ Auditor لا يعتمد Audit قام بتنفيذه (يعتمده Quality Manager). |
| BR-9 | الحذف Soft Delete للبيانات الحساسة، مع الاحتفاظ بالـ Audit log. |
| BR-10 | المستخدم لا يرى إلا بيانات المنشآت المرتبط بها. |
| BR-11 | الشكوى لها SLA حسب الأولوية، وتُعتبر متأخرة لو تجاوزت المهلة دون حل. |
| BR-12 | لا تُغلق الشكوى (Closed) بدون سبب الحل (Resolution). |
| BR-13 | المطالبة لا تُرسل (Submitted) قبل اكتمال المراجعات المطلوبة. |
| BR-14 | المطالبة المرفوضة تتطلب سبب رفض، وإعادة الإرسال تحتفظ بسجل المحاولات السابقة. |
| BR-15 | نسبة الرفض = عدد المطالبات المرفوضة ÷ عدد المطالبات المرسلة × 100. |
| BR-16 | حالة الـ KPI تُحسب بمقارنة القيمة بالهدف حسب حدود (Thresholds) معرّفة. |

**مثال حسابي لـ BR-3:**

| المعيار | الوزن | الإجابة | القيمة | الوزن × القيمة |
|---|---|---|---|---|
| تخزين الأدوية | 3 | Compliant | 1 | 3 |
| تسجيل الجرعات | 2 | Partial | 0.5 | 1 |
| مكافحة العدوى | 5 | Non-compliant | 0 | 0 |
| تدريب الموظفين | 2 | N/A | — | (مستبعد) |

النتيجة = (3 + 1 + 0) ÷ (3 + 2 + 5) × 100 = **40%**

---

## 7. المتطلبات غير الوظيفية (Non-Functional Requirements)

### الأمان (Security)
- NFR-1 تشفير كلمات المرور (bcrypt/argon2).
- NFR-2 صلاحيات دقيقة (Policies) على كل Resource.
- NFR-3 حماية بيانات المرضى: وصول مقيد + تسجيل كل وصول.
- NFR-4 حماية من SQL Injection / XSS / CSRF (الإعدادات الافتراضية لـ Laravel + مراجعة).
- NFR-5 رفع الملفات بقيود نوع وحجم، وتخزين خارج الـ public.

### سلامة البيانات
- NFR-6 كل العمليات متعددة الخطوات داخل Database Transactions.
- NFR-7 Foreign keys وConstraints على مستوى الـ DB.
- NFR-8 Soft deletes + Audit log.

### الأداء
- NFR-9 Indexes على الأعمدة المستخدمة في الفلترة (status, facility_id, dates).
- NFR-10 تحميل العلاقات بـ Eager loading لتجنب N+1.
- NFR-11 التقارير الثقيلة تتولد في الـ Queue.

### الجودة والصيانة
- NFR-12 Tests (Feature + Unit) لقواعد العمل، خصوصًا حساب النتيجة والـ Corrective Actions.
- NFR-13 Static analysis (Larastan) وتنسيق الكود (Pint).
- NFR-14 CI على كل push.
- NFR-15 توثيق واضح (README, ERD, API docs).

### قابلية الاستخدام
- NFR-16 واجهة متجاوبة.
- NFR-17 دعم العربية والإنجليزية (RTL/LTR) كهدف مستقبلي.

---

## 8. الكيانات الرئيسية (Entities)

| المجموعة | الجداول |
|---|---|
| Auth | `users`, `roles`, `permissions` |
| Facilities | `facilities`, `departments`, `facility_user` |
| Audit setup | `audit_templates`, `audit_domains`, `audit_criteria` |
| Audit execution | `audits`, `audit_responses`, `audit_evidence` |
| Follow-up | `corrective_actions`, `action_comments` |
| Patient CRM | `patients`, `patient_followups`, `satisfaction_surveys`, `survey_questions`, `survey_responses` |
| Complaints | `complaints`, `complaint_comments`, `complaint_categories` |
| Claims | `insurers`, `claims`, `claim_items`, `claim_reviews`, `claim_rejections`, `rejection_reasons` |
| KPIs | `kpis`, `kpi_values` |
| System | `activity_log` |

**العلاقات الأساسية:**
- Template → Domains → Criteria (واحد لكتير)
- Audit → Template Version، Facility، Auditor
- Audit → Responses → Evidence
- Response (Non-compliant) → Corrective Action
- Facility → Patients → Follow-ups / Complaints / Survey responses

---

## 9. دورات العمل الأساسية (Core Flows)

### 9.1 دورة الـ Audit
```
Quality Manager يجهز Template
        ↓
إنشاء Audit لمنشأة وإسناده لمدقق
        ↓
المدقق يسجل الإجابات والأدلة (Draft)
        ↓
Quality Manager يراجع ويعتمد (Finalize)
        ↓
حساب Compliance Score + فتح Corrective Actions تلقائيًا
        ↓
المنشأة تنفذ الإجراءات ← Quality Manager يتحقق
        ↓
Audit جديد لقياس التحسن
```

### 9.2 دورة المريض (CRM)
```
تسجيل مريض ← زيارة ← جدولة متابعة
        ↓
إرسال / تسجيل استبيان الرضا
        ↓
تحليل التقييمات + معالجة الشكاوى
        ↓
مؤشرات الرضا على الـ Dashboard
```

---

## 10. قصص المستخدم (User Stories) — عينة

- كـ **Quality Manager** عايز أنشئ Template بمحاور ومعايير بأوزان علشان أقيّم المنشآت بنفس المعيار.
- كـ **Auditor** عايز أسجل إجابتي وأرفق صورة دليل علشان التقييم يكون موثق.
- كـ **Quality Manager** عايز أعتمد الـ Audit فتتحسب النسبة وتتفتح الإجراءات تلقائيًا علشان مفيش مخالفة تتنسي.
- كـ **Staff** عايز أشوف الإجراءات المسندة لي والـ Deadline علشان أنفذها في وقتها.
- كـ **Admin** عايز أشوف Compliance Trend لكل منشأة علشان أعرف التحسن عبر الوقت.
- كـ **Staff** عايز أسجل متابعة لمريض وأحدد موعد المتابعة القادمة.
- كـ **Quality Manager** عايز أشوف متوسط رضا المرضى وعدد الشكاوى المفتوحة لكل منشأة.

---

## 11. معايير القبول (Acceptance Criteria) — عينة

**اعتماد Audit:**
- لا يتم الاعتماد لو في معيار بدون إجابة.
- بعد الاعتماد لا يمكن تعديل أي إجابة.
- تظهر النسبة الكلية والنسبة لكل محور.
- كل إجابة Non-compliant ينتج عنها Corrective Action واحد بالضبط (بدون تكرار).

**الصلاحيات:**
- الـ Auditor لا يستطيع اعتماد Audit قام بتنفيذه.
- الـ Staff لا يرى بيانات منشأة غير مرتبط بها.

---

## 12. التقنيات المقترحة (Tech Stack)

### 12.1 الأساسية

| الطبقة | الاختيار | السبب |
|---|---|---|
| Backend | Laravel 11 (PHP 8.3) | Ecosystem كامل ومناسب لتطبيقات الشركات |
| Database | MySQL أو PostgreSQL | علاقات وTransactions قوية |
| Admin / UI | Filament | CRUD وDashboards وWidgets بسرعة |
| Permissions | Spatie Laravel Permission | Roles / Permissions جاهزة |
| Audit log | Spatie Laravel Activitylog | تسجيل التعديلات تلقائيًا |
| Queues | Redis + Laravel Horizon | تقارير ثقيلة وJobs دورية |
| Auth للـ API | Laravel Sanctum | Tokens بسيطة وآمنة |
| Tests | Pest | Feature + Unit tests |
| Quality | Larastan + Laravel Pint | Static analysis وتنسيق |
| DevOps | Docker Compose + GitHub Actions | تشغيل موحد وCI |

### 12.2 للتقارير والتكامل

| الاحتياج | الأداة |
|---|---|
| تصدير / استيراد Excel | Laravel Excel |
| تقارير PDF | DomPDF أو Snappy |
| توثيق الـ API | Scribe (OpenAPI) |
| تخزين الأدلة والمرفقات | Laravel Filesystem (local / S3) |

### 12.3 معرفة مفيدة للمجال (خارج الكود)
- **Power BI**: شائع في بناء Dashboards للجهات الصحية.
- **FHIR**: معيار لتبادل البيانات الصحية، يكفي فهم الأساسيات.
- **JCI / CQC**: المعايير اللي بتتحول لأسئلة في الـ Audit Templates.
- **SharePoint**: كمفهوم لإدارة المستندات والـ Workflows.

### 12.4 قرارات معمارية
- **Service / Action classes** لكل عملية أعمال (اعتماد Audit، حساب Score، إعادة إرسال مطالبة).
- **Form Requests + API Resources + Policies** لكل موديل.
- **Enums** لكل الحالات (Status).
- **Jobs مجدولة** لتمييز الـ Overdue (Actions والشكاوى) وتجميع الـ KPIs.
- **Transactions + lockForUpdate** في العمليات الحساسة.

---

## 13. المخاطر والحلول

| الخطر | الحل |
|---|---|
| توسع النطاق | الالتزام بالـ MVP وتأجيل باقي الأفكار للمرحلة التانية |
| تعقيد الـ Template وVersioning | بناء قواعد الـ Versioning واختبارها مبكرًا |
| حساسية بيانات المرضى | صلاحيات دقيقة، Audit log، وعدم تخزين بيانات حقيقية في الـ demo |
| صعوبة حساب النتيجة بشكل صحيح | Unit tests بأمثلة رقمية (مثل المثال في القسم 6) |
| الوقت | تنفيذ على مراحل مع Demo شغال في نهاية كل مرحلة |
| تعقيد موديول المطالبات (RCM) | البدء بنسخة مبسطة: حالات + أسباب رفض + تحليل، بدون ربط فعلي مع منصات التأمين |

---

## 14. خطة التنفيذ (Roadmap)

| المرحلة | المحتوى |
|---|---|
| **1** | Auth وRoles + Facilities |
| **2** | Audit Templates + Versioning + Seed (Infection Control) |
| **3** | Audit Sessions + Responses + Evidence |
| **4** | Compliance Score Service + Finalize + Tests |
| **5** | Corrective Actions + Overdue Jobs |
| **6** | KPIs Module + Compliance Dashboard |
| **7** | Patient CRM + Complaints & Inquiries (SLA) |
| **8** | Claims & Rejections + تحليل المرفوضات |
| **9** | تصدير PDF / Excel + REST API موثق |
| **10** | Docker + CI + Seeders + README والتوثيق |

> لو الوقت ضيق: ابدأ بالمراحل 1 إلى 6 كنواة شغالة، ثم أضف 7 و8 حسب الأولوية.

---

## 15. معايير نجاح المشروع

- كل الـ Flows الأساسية تعمل من البداية للنهاية (Template → Audit → Score → Action).
- حساب النتيجة مغطى بـ Tests ويعطي أرقام صحيحة.
- الصلاحيات تمنع الوصول غير المصرح به.
- المشروع يعمل بأمر واحد (`docker compose up`) مع بيانات demo واقعية.
- README يشرح الفكرة والمجال والـ ERD وطريقة التشغيل بوضوح.
