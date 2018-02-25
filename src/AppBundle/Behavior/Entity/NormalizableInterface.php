<?php
/**
 * Created by PhpStorm.
 * User: cedric
 * Date: 01/02/18
 * Time: 17:47
 */

namespace AppBundle\Behavior\Entity;

interface NormalizableInterface
{
    /**
     * @return array
     */
    public function normalize();

    /**
     * @return int
     */
    public function getId();
}
