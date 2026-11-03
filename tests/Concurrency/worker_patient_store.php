<?php

declare(strict_types=1);

/*
| The `actor` that registers a patient through the real controller action.
|
| The first MRN the controller generates is pinned to the value the competing
| transaction is inserting, so the unique key collision is deterministic; every
| retry after that uses a normal random value.
*/

require __DIR__.'/lib.php';

use App\Http\Controllers\PatientController;
use App\Http\Requests\PatientRequests\CreatePatientRequest;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

conc_boot();

$config = conc_config();
$actingUserId = (int) $config['acting_user_id'];
$waitFor = (string) ($config['wait_for'] ?? '');
$phone = (string) $config['phone'];

conc_acting_as($actingUserId);

if ($waitFor !== '' && ! conc_wait_for($waitFor)) {
    conc_finish(['role' => 'actor', 'action' => 'patient_store', 'ok' => false, 'error' => 'signal timeout']);
}

$payload = [
    'fullname' => (string) ($config['fullname'] ?? 'مريض متزامن'),
    'phone' => $phone,
    'gender' => 'male',
    'birthdate' => '1991-02-02',
    'blood_group' => 'A+',
];

$request = conc_form_request(
    CreatePatientRequest::class,
    'POST',
    '/patients',
    $payload,
);

Str::createRandomStringsUsingSequence(['AAAAAAAA']);

$actionStartWall = microtime(true);
$actionStart = microtime(true);
$outcome = 'response';
$errors = [];

try {
    app(PatientController::class)->store($request);
} catch (Throwable $exception) {
    $outcome = 'exception';
    $errors = conc_validation_errors($exception);
} finally {
    Str::createRandomStringsNormally();
}

$actionEndWall = microtime(true);
$actionElapsed = (int) round(($actionEndWall - $actionStart) * 1000);

$patient = Patient::query()->where('phone', $phone)->first();
$duplicateMrnGroups = DB::table('patients')
    ->select('mrn')
    ->groupBy('mrn')
    ->havingRaw('COUNT(*) > 1')
    ->count();

conc_finish([
    'role' => 'actor',
    'action' => 'patient_store',
    'ok' => true,
    'outcome' => $outcome,
    'errors' => $errors,
    'action_elapsed_ms' => $actionElapsed,
    'action_started_at' => $actionStartWall,
    'action_ended_at' => $actionEndWall,
    'stored_mrn' => $patient?->mrn,
    'stored_patient_id' => $patient?->id,
    'duplicate_mrn_groups' => $duplicateMrnGroups,
    'pinned_mrn_rows' => Patient::withTrashed()->where('mrn', 'MRN-AAAAAAAA')->count(),
]);
