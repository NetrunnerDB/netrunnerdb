<?php
namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('cast_to_array', [$this, 'objectFilter']),
        ];
    }

    public function objectFilter($stdClassObject) {
        $response = (array)$stdClassObject;

        return $response;
    }
}
