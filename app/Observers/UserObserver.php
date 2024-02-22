<?php

namespace App\Observers;

use App\Enums\Role;
use App\Models\User;
use Spatie\Permission\Models\Role as SpatieRole;

class UserObserver
{
    /**
     * Handle the user "creating" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function creating(User $user)
    {
        $user->email_verification_code = $this->randomCode();
        $user->phone_number_verification_code = $this->randomCode();
    }

    /**
     * Handle the user "created" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function created(User $user)
    {
        // check if the default role is exist in our db
        if (SpatieRole::whereName(Role::default())->count() > 0) {
            // If the user does'nt have any role
            // Set ther user default role
            if ($user->roles()->count() == 0) {
                $user->assignRole(Role::default());
            }
        }
    }

    /**
     * Handle the user "updating" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updating(User $user)
    {
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
            $user->email_verification_code = $this->randomCode();
        }

        if ($user->isDirty('phone_number')) {
            $user->phone_number_verified_at = null;
            $user->phone_number_verification_code = $this->randomCode();
        }
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        //
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the user "restored" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }

    /**
     * Generate random 5 digit string integers
     *
     * @return string
     */
    private function randomCode(): string
    {
        return (string) random_int(10000, 99999);
    }
}
