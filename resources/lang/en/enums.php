<?php

use App\Enums\ErrorCodes;
use App\Enums\RescheduleLessonReason;

return [

    ErrorCodes::class => [
        ErrorCodes::AUTHENTICATION_REQUIRED => 'We are unable to authenticate your account.',
        ErrorCodes::AUTHENTICATION_PHONE_NUMBER_REQUIRED => 'This account was created with a mobile number.',
        ErrorCodes::AUTHENTICATION_EMAIL_REQUIRED => 'This account was created with an email address.',
        ErrorCodes::UNVERIFIED_EMAIL => 'Email is not verified.',
        ErrorCodes::UNVERIFIED_PHONE_NUMBER => 'Phone number is not verified.',
        ErrorCodes::UNVERIFIED_ACCOUNT => 'User account is not verified.',
        ErrorCodes::INVALID_CREDENTIALS => 'We couldn\'t find any records that matches your credentials.',
        ErrorCodes::INVALID_ONE_TIME_PASSWORD => 'The one time password you provided is incorrect.',
        ErrorCodes::INVALID_USERNAME => 'We couldn\'t find any records that matches your username.',
        ErrorCodes::INVALID_PASSWORD => 'Your password did not match on our records.',
        ErrorCodes::USERNAME_NOT_FOUND => 'We couldn\'t find any records that matches your username.',
        ErrorCodes::EMAIL_NOT_FOUND => 'We couldn\'t find any records that matches your email.',
        ErrorCodes::PASSWORD_NOT_SUPPORTED => 'This account has no password associated. Please try using a different login method.',
        ErrorCodes::STRIPE_CONNECT_NOT_FOUND => 'There is no stripe connect associated with the current user.',
        ErrorCodes::USING_OLD_PASSWORD => 'Your new password cannot be the same as your current password.',
        ErrorCodes::ACCOUNT_BLOCKED => 'Your account has been locked. Please contact the administrator to unlock it.'
    ],

    RescheduleLessonReason::class => [
        RescheduleLessonReason::WORK => 'Work',
        RescheduleLessonReason::SICKNESS => 'Sickness',
        RescheduleLessonReason::PERSONAL_REASONS => 'Personal Reasons',
        RescheduleLessonReason::DATE_UNAVAILABILITY => 'Date Unavailability',
        RescheduleLessonReason::OTHERS => 'Others',
    ],
];
