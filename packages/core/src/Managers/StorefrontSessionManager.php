<?php

namespace Lunar\Managers;

use Closure;
use Illuminate\Session\SessionManager;
use Lunar\Base\StorefrontSessionInterface;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;

class StorefrontSessionManager implements StorefrontSessionInterface
{
    protected ?Channel $channel = null;

    protected ?CustomerGroup $customerGroup = null;

    public function __construct(
        protected SessionManager $sessionManager
    ) {
        $this->initChannel();
        $this->initCustomerGroup();
    }

    /**
     * {@inheritDoc}
     */
    public function forget()
    {
        $this->sessionManager->forget(
            $this->getSessionKey()
        );
    }

    public function initCustomerGroup()
    {
        if ($this->customerGroup) {
            return $this->customerGroup;
        }

        $handle = $this->sessionManager->get(
            $this->getSessionKey().'_customer_group'
        );

        if (!$handle) {
            return $this->setCustomerGroup(
                CustomerGroup::getDefault()
            );
        }

        $model = CustomerGroup::whereHandle($handle)->first();

        if (!$model) {
            throw new \Exception(
                "Unable to find customer group with handle {$handle}"
            );
        }

        return $this->setCustomerGroup($model);
    }

    public function initChannel()
    {
        if ($this->channel) {
            return $this->channel;
        }

        $channelHandle = $this->sessionManager->get(
            $this->getSessionKey().'_channel'
        );

        if (!$channelHandle) {
            return $this->setChannel(
                Channel::getDefault()
            );
        }

        $channel = Channel::whereHandle($channelHandle)->first();

        if (!$channel) {
            throw new \Exception(
                "Unable to find channel with handle {$channelHandle}"
            );
        }

        return $this->setChannel($channel);
    }

    /**
     * {@inheritDoc}
     */
    public function getSessionKey()
    {
        return config('lunar.cart.session_key');
    }

    /**
     * {@inheritDoc}
     */
    public function setChannel(Channel|string $channel)
    {
        $this->sessionManager->put(
            $this->getSessionKey().'_channel',
            $channel->handle
        );
        $this->channel = $channel;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCustomerGroup(CustomerGroup $customerGroup)
    {
        $this->sessionManager->put(
            $this->getSessionKey().'_customer_group',
            $customerGroup->handle
        );
        $this->customerGroup = $customerGroup;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getChannel(): Channel
    {
        return $this->channel ?: Channel::getDefault();
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomerGroup(): CustomerGroup
    {
        return $this->customerGroup ?: CustomerGroup::getDefault();
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrency(): Currency
    {
        return $this->currency ?: Currency::getDefault();
    }
}
