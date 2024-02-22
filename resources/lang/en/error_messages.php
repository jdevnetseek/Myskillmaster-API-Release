<?php

return [
    'post' => [
        'owner' => 'You do not own this post.'
    ],
    'comment' => [
        'owner' => 'You do not own this comment.'
    ],
    'category' => [
        'delete_has_lesson' => 'Failed to delete category. It has lesson(s) associated with it.',
    ],
    'product' => [
        'owner' => 'You do not own this item.'
    ],
    'verification_token' => [
        'required' => 'The verification token is required.',
        'invalid'  => 'The verification token is invalid.'
    ],
    'lesson' => [
        'owner' => 'Unauthorized. Only the owner can delete the lesson.',
        'enrolled_students' => 'Failed to delete lesson. There are students enrolled in the lesson.',
        'cancelled_subscription' => 'Your subscription has been cancelled. Please renew your subscription to continue using the service.',
        'no_active_subscription' => 'You need to subscribe to one of our master plans to access exclusive features and take your experience to the next level.',
        'max_lesson' => 'You have reached the maximum number of lessons for your current plan. Please upgrade your plan to continue using the service.',
    ],
    'lesson_schedule_delete' => [
        'owner' => 'Unauthorized. Only the owner of the lesson can delete schedules.',
        'has_student_enrolled' => 'Failed to delete schedule. There are students enrolled in the schedule.'
    ],
    'lesson_schedule_create' => [
        'owner' => 'Unauthorized. Only the owner of the lesson can create schedules.'
    ],
    'stripe_external_account' => [
        'cannot_delete_default' => 'Unable to delete a default bank account. Please select a different bank account as default first.'
    ],

];
