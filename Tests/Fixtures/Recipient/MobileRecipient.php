<?php

namespace Yokai\MessengerBundle\Tests\Fixtures\Recipient;

use Yokai\MessengerBundle\Recipient\MobileRecipientInterface;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
class MobileRecipient implements MobileRecipientInterface
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
     * @inheritDoc
     */
    public function getDevicesTokens()
    {
        return $this->tokens;
    }
}
