<?php

namespace Yokai\MessengerBundle\Tests\Fixtures\Recipient;

use Yokai\MessengerBundle\Recipient\MobileRecipientInterface;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
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
     * @inheritdoc
     */
    public function getDevicesTokens()
    {
        return $this->tokens;
    }
}
