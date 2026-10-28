<?php

namespace App\Enums;

enum PermissionEnum: string
{
    // Users
    case USERS_VIEW = 'users.view';
    case USERS_CREATE = 'users.create';
    case USERS_UPDATE = 'users.update';
    case USERS_DELETE = 'users.delete';
    // doctors
    case DOCTORS_VIEW = 'doctors.view';
    case DOCTORS_CREATE = 'doctors.create';
    case DOCTORS_UPDATE = 'doctors.update';
    case DOCTORS_DELETE = 'doctors.delete';
    // Patients
    case PATIENTS_VIEW = 'patients.view';
    case PATIENTS_CREATE = 'patients.create';
    case PATIENTS_UPDATE = 'patients.update';
    case PATIENTS_DELETE = 'patients.delete';

    // Visits
    case VISITS_VIEW = 'visits.view';
    case VISITS_CREATE = 'visits.create';
    case VISITS_UPDATE = 'visits.update';
    case VISITS_STATUS = 'visits.status';
    case VISITS_DELETE = 'visits.delete';

    // Follow-ups
    case FOLLOWUPS_VIEW = 'followups.view';
    case FOLLOWUPS_CREATE = 'followups.create';
    case FOLLOWUPS_COMPLETE = 'followups.complete';
    case FOLLOWUPS_STATUS = 'followups.status';
    case FOLLOWUPS_UPDATE = 'followups.update';
    case FOLLOWUPS_DELETE = 'followups.delete';

    // Clinic
    case CLINICS_VIEW = 'clinics.view';
    case CLINICS_CREATE = 'clinics.create';
    case CLINICS_UPDATE = 'clinics.update';
    case CLINICS_DELETE = 'clinics.delete';

    // Survey
    case SURVEYS_VIEW = 'surveys.view';
    case SURVEYS_CREATE = 'surveys.create';
    case SURVEYS_UPDATE = 'surveys.update';
    case SURVEYS_DELETE = 'surveys.delete';

    // Complaint
    case COMPLAINTS_VIEW = 'complaints.view';
    case COMPLAINTS_CREATE = 'complaints.create';
    case COMPLAINTS_UPDATE = 'complaints.update';
    case COMPLAINTS_DELETE = 'complaints.delete';
    case COMPLAINTS_ASSIGN = 'complaints.assign';
    case COMPLAINTS_ADVANCE = 'complaints.advance';
    case COMPLAINTS_REOPEN = 'complaints.reopen';
    
    public static function values(): array
    {
        return array_map(
            fn (self $permission) => $permission->value,
            self::cases()
        );
    }
}
