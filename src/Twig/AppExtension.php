<?php

// src/Twig/AppExtension.php
namespace App\Twig;

use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

use App\Service\DateFormatter;

class AppExtension extends AbstractExtension
{
    private $translator;
    private $urlGenerator;

    public function __construct(TranslatorInterface $translator,
                                UrlGeneratorInterface $urlGenerator)
    {
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('countryName', [ $this, 'getCountryName' ]),
        ];
    }

    public function getFilters()
    {
        return [
            // general
            new TwigFilter('without', [ $this, 'withoutFilter' ]),

            // app-specific
            // appbundle-specific
            new TwigFilter('placeTypeLabel', [ $this, 'placeTypeLabelFilter' ]),
        ];
    }

    // see https://github.com/symfony/symfony/issues/13641
    public function getCountryName($country, $displayLocale = null)
    {
        if (is_null($country)) {
            return '';
        }

        return Countries::getName($country, $displayLocale);
    }

    // see https://api.drupal.org/api/drupal/core%21themes%21engines%21twig%21twig.engine/function/twig_without/8.2.x
    public function withoutFilter($element)
    {
        if ($element instanceof \ArrayAccess) {
            $filtered_element = clone $element;
        }
        else {
            $filtered_element = $element;
        }

        $args = func_get_args();
        unset($args[0]);
        foreach ($args as $arg) {
            if (isset($filtered_element[$arg])) {
                unset($filtered_element[$arg]);
            }
        }

        return $filtered_element;
    }

    private function getLocale()
    {
        if (is_null($this->translator)) {
            return 'en';
        }

        return $this->translator->getLocale();
    }

    public function placeTypeLabelFilter($placeType, $count = 1, $locale = null)
    {
        if (is_null($locale)) {
            $locale = $this->getLocale();
        }

        return \App\Entity\Place::buildPluralizedTypeLabel($placeType, $count);
    }
}
