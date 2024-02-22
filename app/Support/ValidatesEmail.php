<?php

namespace App\Support;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\SpoofCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;

trait ValidatesEmail
{
    /**
     * Check if the given string value is a valid email
     * @param string $value The email to check
     * @return bool is it valid email
     */
    private function isEmail(string $value): bool
    {
        return (new EmailValidator())->isValid(
            $value,
            new MultipleValidationWithAnd(array_filter([
                new RFCValidation(),
                app()->isProduction() ? new DNSCheckValidation() : null,
                // new SpoofCheckValidation(),
            ]))
        ) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}
