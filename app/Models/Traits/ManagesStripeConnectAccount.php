<?php
namespace App\Models\Traits;

use Exception;
use Stripe\Account;
use Stripe\Collection;
use Stripe\EphemeralKey;
use Laravel\Cashier\Cashier;

trait ManagesStripeConnectAccount
{
    /**
     * Retrieve the Stripe connect ID.
     *
     * @return string|null
     */
    public function stripeConnectId()
    {
        return $this->stripe_connect_id;
    }

    /**
     * Retrieve the default email the stripe connect will be used.
     *
     * @return void
     */
    public function stripeConnectEmail()
    {
        return $this->email;
    }

    /**
     * The name the stripe connect will be used for business name.
     *
     * @return void
     */
    public function stripeBusinessName()
    {
        return $this->full_name;
    }

    /**
     * The default MCC code that will be used to set business profile.
     *
     * @return void
     */
    public function stripeDefaultMCC()
    {
        return 5734;
    }

    /**
     * Determine if the entity has a Stripe connect ID.
     *
     * @return bool
     */
    public function hasStripeConnectId()
    {
        return ! empty($this->stripeConnectId());
    }

    /**
     * Create or get the users stripe connect account.
     *
     * @param  array  $parameters
     * @return \Stripe\Account
     */
    public function createOrGetStripeConnectAccount(array $parameters = [])
    {
        if ($this->hasStripeConnectId()) {
            return $this->getStripeConnectAccount();
        }

        return $this->createStripeConnectAccount($parameters);
    }

    /**
     * Update the users stripe connet account.
     *
     * @param array $parameters
     * @return \Stripe\Account
     */
    public function updateStripeConnectAccount(array $parameters = [])
    {
        $this->assertConnectAccountExists();

        return Account::update($this->stripeConnectId(), $parameters, Cashier::stripeOptions());
    }

    /**
     * Delete the stripe connect account.
     *
     * @param array $parameters
     * @return void
     */
    public function deleteStripeConnectAccount(array $parameters = [])
    {
        /** @var Account */
        $account = $this->getStripeConnectAccount();
        $result = $account->delete($parameters, Cashier::stripeOptions());

        $this->stripe_connect_id = null;
        $this->save();

        return $result;
    }

    /**
     * Get the Stripe connect account.
     *
     * @return \Stripe\Account
     */
    public function getStripeConnectAccount()
    {
        $this->assertConnectAccountExists();

        return Account::retrieve($this->stripeConnectId(), Cashier::stripeOptions());
    }

    /**
     * Creates the stripe connect account.
     *
     * @param array $parameters
     * @return \Stripe\Account
     */
    public function createStripeConnectAccount(array $parameters = [])
    {
        if ($this->hasStripeConnectId()) {
            throw new Exception(
                class_basename($this) . " has already a Stripe Connect Account with ID {$this->stripe_connect_id}."
            );
        }

        $defaultParameters = [
            'email'                  => $this->stripeConnectEmail(),
            'type'                   => 'custom',
            'business_type'          => 'individual',
            'requested_capabilities' => [
                'card_payments',
                'transfers'
            ],
            'business_profile' => [
                'mcc'                 => $this->stripeDefaultMCC(),
                'name'                => $this->stripeBusinessName(),
                'product_description' => 'Software, SaaS'
            ]
        ];

        $account = Account::create(array_replace_recursive($defaultParameters, $parameters), Cashier::stripeOptions());

        $this->stripe_connect_id = $account->id;

        $this->save();

        return $account;
    }

    public function getExternalAccount($externalAccountId)
    {
        $this->assertConnectAccountExists();

        return Account::retrieveExternalAccount(
            $this->stripeConnectId(),
            $externalAccountId,
            [],
            Cashier::stripeOptions()
        );
    }

    /**
     * A wrapper to create external account from token.
     *
     * @param array|string $externalProperty
     * @return \Stripe\BankAccount|\Stripe\Card
     */
    public function createExternalAccount($externalProperty)
    {
        $this->assertConnectAccountExists();

        $params = [
            'external_account' => $externalProperty
        ];

        return Account::createExternalAccount($this->stripeConnectId(), $params, Cashier::stripeOptions());
    }

    /**
     * List all bank accounts
     *
     * @param array $params
     * @return array
     */
    public function allExternalAccounts(array $params = []) : Collection
    {
        $this->assertConnectAccountExists();

        $defaultParameters = [
            'object' => 'bank_account',
            'limit' => 3
        ];

        return Account::allExternalAccounts(
            $this->stripeConnectId(),
            array_merge($defaultParameters, $params),
            Cashier::stripeOptions()
        );
    }

    /**
     * Update a bank account
     *
     * @param array $params
     * @return \Stripe\BankAccount|\Stripe\Card
     */
    public function updateExternalAccount(string $externalAccountId, array $params = [])
    {
        $this->assertConnectAccountExists();

        return Account::updateExternalAccount(
            $this->stripeConnectId(),
            $externalAccountId,
            $params,
            Cashier::stripeOptions()
        );
    }

    /**
     * Delete a bank account
     *
     * @param array $params
     * @return \Stripe\BankAccount|\Stripe\Card
     */
    public function deleteExternalAccount(string $externalAccountId)
    {
        $this->assertConnectAccountExists();

        return Account::deleteExternalAccount(
            $this->stripeConnectId(),
            $externalAccountId,
            [],
            Cashier::stripeOptions()
        );
    }


    /**
     * Determine if the entity has a Stripe connect ID and throw an exception if not.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function assertConnectAccountExists()
    {
        if (! $this->hasStripeConnectId()) {
            throw new Exception(
                class_basename($this) . ' does not have a Stripe Connect Account yet.'
            );
        }
    }
}
