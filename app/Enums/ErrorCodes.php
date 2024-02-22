<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Contracts\LocalizedEnum;

final class ErrorCodes extends Enum implements LocalizedEnum
{
    /**
     * Error code for unverified email
     */
    const UNVERIFIED_EMAIL = 'UNVERIFIED_EMAIL';


    /**
     * Error code for unverified phone number
     */
    const UNVERIFIED_PHONE_NUMBER = 'UNVERIFIED_PHONE_NUMBER';

    /**
     * Error code for unverified account
     */
    const UNVERIFIED_ACCOUNT = 'UNVERIFIED_ACCOUNT';

    /**
     * Error code for invalid account Credentioals
     */
    const INVALID_CREDENTIALS = 'INVALID_CREDENTIALS';

    /**
     * Error code for invalid one time password
     */
    const INVALID_ONE_TIME_PASSWORD = 'INVALID_ONE_TIME_PASSWORD';

    /**
     * Error code for authentication required
     */
    const AUTHENTICATION_REQUIRED = 'AUTHENTICATION_REQUIRED';

    /**
     * Error code when an email is the primary authentication.
     */
    const AUTHENTICATION_EMAIL_REQUIRED = 'AUTHENTICATION_EMAIL_REQUIRED';

    /**
     * Error code when an phone number is the primary authentication.
     */
    const AUTHENTICATION_PHONE_NUMBER_REQUIRED = 'AUTHENTICATION_PHONE_NUMBER_REQUIRED';

    /**
     * Error code for using old password when updating password.
     */
    const USING_OLD_PASSWORD = 'USING_OLD_PASSWORD';

    /**
     * Error code for invalid username
     */
    const INVALID_USERNAME = 'INVALID_USERNAME';

    /**
     * Error code for invalid password
     */
    const INVALID_PASSWORD = 'INVALID_PASSWORD';

    /**
     * Error code for invalid password
     */
    const USERNAME_NOT_FOUND = 'USERNAME_NOT_FOUND';

    /**
     * Error code for invalid email
     */
    const EMAIL_NOT_FOUND = 'EMAIL_NOT_FOUND';

    /**
     * Error code for account that has been blocked
     */
    const ACCOUNT_BLOCKED = 'ACCOUNT_BLOCKED';

    /**
     * Error code for account that has no password
     */
    const PASSWORD_NOT_SUPPORTED = 'PASSWORD_NOT_SUPPORTED';

    /**
     * Error code for invalid token
     */
    const TOKEN_NOT_FOUND = 'TOKEN_NOT_FOUND';

    /**
     * Error code for stripe connect not found
     */
    const STRIPE_CONNECT_NOT_FOUND = 'STRIPE_CONNECT_NOT_FOUND';

    /**
     * Error code for stripe connect payouts is disabled;
     */
    const STRIPE_CONNECT_PAYOUTS_DISABLED = 'STRIPE_CONNECT_PAYOUTS_DISABLED';

    /**
     * Error code for no active subscription
     */
    const NO_SUBSCRIPTION_FOUND = 'NO_SUBSCRIPTION_FOUND';

    /**
     * Error code for subscription limit exceeded
     */
    const SUBSCRIPTION_MAX_LIMIT_EXCEEDED = 'SUBSCRIPTION_MAX_LIMIT_EXCEEDED';

    /**
     * Error for same subscription
     */
    const SAME_SUBSCRIPTION_FOUND = 'SAME_SUBSCRIPTION_FOUND';

    /**
     * Error for adding free trial
     */
    const INCORRECT_PLAN = 'INCORRECT_PLAN';

    /** Error for customer not yer onboarded */
    const INCOMPLETE_MASTER_PROFILE = 'INCOMPLETE_MASTER_PROFILE';

    /**
     * Error for already subscribe
     */
    const SUBSCRIPTION_FOUND = 'SUBSCRIPTION_FOUND';

    /** Error for user with no default payment method */
    const DEFAULT_PAYMENT_NOT_FOUND = 'DEFAULT_PAYMENT_NOT_FOUND';

    const ENROLLMENT_CONFLICT_SCHEDULE = 'ENROLLMENT_CONFLICT_SCHEDULE';

    const LESSON_INVALID_SCHEDULE = 'LESSON_INVALID_SCHEDULE';

    const ENROLLMENT_CANCELLATION_ERROR = 'ENROLLMENT_CANCELLATION_ERROR';

    const MASTER_STRIPE_CONNECT_PAYOUTS_DISABLED = 'MASTER_STRIPE_CONNECT_PAYOUTS_DISABLED';

    const PAYOUT_NO_AVAILABLE_BALANCE = 'PAYOUT_NO_AVAILABLE_BALANCE';
    const PAYOUT_INVALID_AMOUNT = 'PAYOUT_INVALID_AMOUNT';
    const PAYOUT_USER_HAS_ONGOING_REPORT = 'PAYOUT_USER_HAS_ONGOING_REPORT';

    const MASTER_RATING_ERROR = 'MASTER_RATING_ERROR';
    const MASTER_PROFILE_ERROR = 'MASTER_PROFILE_ERROR';
    const MASTER_LESSON_ERROR = 'MASTER_LESSON_ERROR';
    const USER_ACCOUNT_ERROR = 'USER_ACCOUNT_ERROR';
    const SUBSCRIPTION_ERROR = 'SUBSCRIPTION_ERROR';
}
