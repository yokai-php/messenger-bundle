<?php

namespace Yokai\MessengerBundle;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class Message
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var array
     */
    private $defaults;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string $id
     * @param array  $defaults
     */
    public function __construct($id, array $defaults = [])
    {
        $this->id = $id;
        $this->defaults = $defaults;
        $this->options = [];
    }

    /**
     * @param string $channel
     * @param array  $options
     */
    public function setOptions($channel, array $options)
    {
        $this->options[$channel] = $options;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $channel
     *
     * @return array
     */
    public function getOptions($channel)
    {
        $options = $this->defaults;

        if (isset($this->options[$channel])) {
            $options = array_merge(
                $options,
                $this->options[$channel]
            );
        }

        return $options;
    }
}
