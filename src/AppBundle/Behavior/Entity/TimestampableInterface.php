<?php
/**
 * Created by PhpStorm.
 * User: cedric
 * Date: 01/02/18
 * Time: 17:43
 */

namespace AppBundle\Behavior\Entity;

interface TimestampableInterface
{
    /**
     * @return \DateTime
     */
    public function getDateUpdate();
}
