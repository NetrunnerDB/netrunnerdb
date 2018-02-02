<?php

namespace AppBundle\Behavior\Entity;

use Gedmo\Translatable\Translatable;

/**
 * Description of AbstractTranslatableEntity
 *
 * @author Alsciende <alsciende@icloud.com>
 */
abstract class AbstractTranslatableEntity implements Translatable
{
    protected $locale = 'en';

    public function setTranslatableLocale(string $locale)
    {
        $this->locale = $locale;
    }
}
