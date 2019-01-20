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
            ->setDefault('template', '')
            ->setAllowedTypes('template', ['string', 'null', 'boolean'])

            ->setDefault('subject', '')
            ->setAllowedTypes('subject', ['string', 'null', 'boolean'])

            ->setDefault('translation_catalog', '')
            ->setAllowedTypes('translation_catalog', 'string')

            ->setDefault('subject_parameters', [])
            ->setAllowedTypes('subject_parameters', 'array')
            ->setNormalizer('subject_parameters', function ($opts, $value) {
                return array_values((array) $value);
            })

            ->setDefault('template_parameters', [])
            ->setAllowedTypes('template_parameters', 'array')
            ->setNormalizer('template_parameters', function ($opts, $value) {
                return array_values((array) $value);
            })

            ->setDefault('template_vars', [])
            ->setAllowedTypes('template_vars', 'array')
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

        if (!$this->options['subject']) {
            return '';
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

        if (!$this->options['template']) {
            return '';
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
