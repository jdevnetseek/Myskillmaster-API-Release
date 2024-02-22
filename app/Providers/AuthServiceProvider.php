<?php

namespace App\Providers;

use App\Models\Job;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\MasterLesson;
use App\Models\Product;
use App\Policies\JobPolicy;
use App\Policies\PostPolicy;
use App\Policies\CommentPolicy;
use App\Policies\LessonSchedulePolicy;
use App\Policies\MasterLessonPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Job::class      => JobPolicy::class,
        Comment::class  => CommentPolicy::class,
        Post::class     => PostPolicy::class,
        Product::class  => ProductPolicy::class,
        MasterLessonPolicy::class => MasterLessonPolicy::class,
        LessonSchedulePolicy::class => LessonSchedulePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('update-onboarding-details', function (User $user) {
            return !$user->isOnboarded();
        });

        Gate::define('store-lesson', [MasterLessonPolicy::class, 'create']);
    }
}
