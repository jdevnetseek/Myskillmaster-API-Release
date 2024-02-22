<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class CategoryType extends Enum
{
    const GENERAL        = 'general';
    const JOB            = 'job';
    const USER           = 'user';
    const USER_REPORT    = 'user_report';
    const JOB_REPORT     = 'job_report';
    const COMMENT_REPORT = 'comment_report';
    const REPORT         = 'report';
    const PRODUCT        = 'product';
    const LESSON         = 'lesson';
}
