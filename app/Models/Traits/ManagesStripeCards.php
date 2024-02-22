<?php

namespace App\Models\Traits;

use Stripe\Card;
use Stripe\Collection;
use Stripe\EphemeralKey;
use Stripe\PaymentIntent;
use Illuminate\Support\Arr;
use Laravel\Cashier\Payment;
use Laravel\Cashier\Concerns\ManagesCustomer;

trait ManagesStripeCards
{
    use ManagesCustomer;

    /**
     * Create a stripe card from token, like the ones returned by Stripe.js.
     * Stripe will automatically validate the card.
     *
     * @param string $source
     * @return Card
     */
    public function createStripeCardFromToken(string $source) : Card
    {
        $stripeCustomer = $this->createOrGetStripeCustomer();

        return $stripeCustomer->createSource($stripeCustomer->id, compact('source'), $this->stripeOptions());
    }

    /**
     * Remove a stripe card.
     *
     * @param string $sourceId
     * @return Card
     */
    public function deleteStripeCard(string $sourceId) : Card
    {
        $stripeCustomer = $this->createOrGetStripeCustomer();

        return $stripeCustomer->deleteSource($stripeCustomer->id, $sourceId, null, $this->stripeOptions());
    }

    /**
     * Lets you retrieve details about a specific card stored on the customer.
     *
     * @param string $sourceId
     * @return Card
     */
    public function getStripeCard(string $sourceId) : Card
    {
        $stripeCustomer = $this->createOrGetStripeCustomer();

        $object = $stripeCustomer->retrieveSource($stripeCustomer->id, $sourceId, [], $this->stripeOptions());

        $this->markDefaultSource($object, $stripeCustomer->default_source);

        return $object;
    }

    /**
     * Get list of stripe cards.
     *
     * @param array $options
     * @return Collection
     */
    public function listStripeCards(array $options = [])
    {
        $stripeCustomer = $this->createOrGetStripeCustomer();

        $collection = $stripeCustomer->allSources(
            $stripeCustomer->id,
            array_merge(['object' => 'card'], $options),
            $this->stripeOptions()
        );

        /**
         * We will attach `is_default` property here to easily check
         * if card is default or not.
         */
        $mapped = array_map(function ($item) use ($stripeCustomer) {
            $this->markDefaultSource($item, $stripeCustomer->default_source);
            return $item;
        }, $collection->data);

        /**
         * We will then sort the result by brand so that it will not get messed
         * up as stripe put the default source first.
         */
        $collection->data = array_values(Arr::sort($mapped, function ($item) {
            return $item->brand;
        }));

        return $collection;
    }

    /**
     * Mark a card as default
     *
     * @param string $sourceId
     * @return void
     */
    public function makeStripeCardDefault($sourceId) : void
    {
        $this->updateStripeCustomer([
            'default_source' => $sourceId
        ]);
    }

    /**
     * Add a is_default property to Card object
     *
     * @param Card $object
     * @param string $sourceId
     * @return void
     */
    protected function markDefaultSource(Card &$object, string $sourceId)
    {
        return data_set($object, 'is_default', $object->id === $sourceId);
    }

    /**
     * Creates EphemeralKey
     *
     * @return void
     */
    public function createEphemeralKey($options = [])
    {
        if ($this->hasStripeId()) {
            $stripeId = $this->stripeId();
        } else {
            $stripeId = $this->createOrGetStripeCustomer()->id;
        }

        return EphemeralKey::create(['customer' => $stripeId], array_merge($this->stripeOptions(), $options));
    }
}
