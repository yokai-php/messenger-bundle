<?php

namespace Yokai\MessengerBundle\Tests\Fixtures\Recipient;

use Yokai\MessengerBundle\Recipient\NotificationRecipientInterface;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class NotificationRecipient implements NotificationRecipientInterface
{
    /**
     * @var array
     */
    private $tokens;

    /**
     * @param array $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @inheritdoc
     */
    public function getDevicesTokens()
    {
        return $this->tokens;
    }
}
