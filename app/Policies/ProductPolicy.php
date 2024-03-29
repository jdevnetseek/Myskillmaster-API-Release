<?php

namespace App\Policies;

use Stripe\Account;
use App\Models\User;
use App\Models\Product;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Exceptions\IncompleteStripeConnectPayout;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Product  $product
     * @return mixed
     */
    public function view(User $user, Product $product)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        /**
         * Check if account payouts is enabled to make sure we can transfer funds
         * to our seller.
         */
        throw_if(!$user->payouts_enabled, IncompleteStripeConnectPayout::class);

        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Product  $product
     * @return mixed
     */
    public function update(User $user, Product $product)
    {
        // Check if the product creator is the current user.
        return $product->isOwner($user) ? $this->allow() : $this->deny(__('error_messages.product.owner'));
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Product  $product
     * @return mixed
     */
    public function delete(User $user, Product $product)
    {
        // Check if the product creator is the current user.
        return $product->isOwner($user) ? $this->allow() : $this->deny(__('error_messages.product.owner'));
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Product  $product
     * @return mixed
     */
    public function restore(User $user, Product $product)
    {
        // Check if the product creator is the current user.
        return $product->isOwner($user) ? $this->allow() : $this->deny(__('error_messages.product.owner'));
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Product  $product
     * @return mixed
     */
    public function forceDelete(User $user, Product $product)
    {
        // Check if the product creator is the current user.
        return $product->isOwner($user) ? $this->allow() : $this->deny(__('error_messages.product.owner'));
    }
}
