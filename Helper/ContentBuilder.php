<?php

namespace Yokai\MessengerBundle\Helper;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Yokai\MessengerBundle\Channel\ChannelInterface;
use Yokai\MessengerBundle\Exception\BadMethodCallException;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class ContentBuilder
{
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var array
     */
    private $defaults;

    /**
     * @var array
     */
    private $options;

    /**
     * @param EngineInterface     $templating
     * @param TranslatorInterface $translator
     * @param array               $defaults
     */
    public function __construct(EngineInterface $templating, TranslatorInterface $translator, array $defaults)
    {
        $this->templating = $templating;
        $this->translator = $translator;
        $this->defaults = $defaults;
    }

    /**
     * @param array $options
     */
    public function configure($options)
    {
        $resolver = (new OptionsResolver)
            ->setRequired(['template'])
            ->setDefault('subject', '')
            ->setDefault('translation_catalog', '')
            ->setDefault('subject_parameters', [])
            ->setDefault('template_parameters', [])
            ->setDefault('template_vars', [])
            ->setNormalizer('subject_parameters', function ($opts, $value) {
                return array_values((array) $value);
            })
            ->setNormalizer('template_parameters', function ($opts, $value) {
                return array_values((array) $value);
            })
            ->setNormalizer('template_vars', function ($opts, $value) {
                return (array) $value;
            })
        ;

        foreach ($resolver->getDefinedOptions() as $option) {
            if (isset($this->defaults[$option])) {
                $resolver->setDefault($option, $this->defaults[$option]);
            }
        }

        $options = array_intersect_key(
            $options,
            array_flip($resolver->getDefinedOptions())
        );

        $this->options = $resolver->resolve($options);
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    public function getSubject(array $parameters)
    {
        if (null === $this->options) {
            throw BadMethodCallException::createMissingCall(
                __CLASS__.'::configure',
                __METHOD__
            );
        }

        return $this->translator->trans(
            $this->options['subject'],
            array_intersect_key($parameters, array_flip($this->options['subject_parameters'])),
            $this->options['translation_catalog']
        );
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    public function getBody(array $parameters)
    {
        if (null === $this->options) {
            throw BadMethodCallException::createMissingCall(
                __CLASS__.'::configure',
                __METHOD__
            );
        }

        return $this->templating->render(
            strtr(
                $this->options['template'],
                array_intersect_key($parameters, array_flip($this->options['template_parameters']))
            ),
            array_merge($parameters, $this->options['template_vars'])
        );
    }
}
