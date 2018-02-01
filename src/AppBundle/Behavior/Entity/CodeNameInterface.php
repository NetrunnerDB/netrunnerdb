<?php
/**
 * Created by PhpStorm.
 * User: cedric
 * Date: 01/02/18
 * Time: 17:57
 */

namespace AppBundle\Behavior\Entity;

interface CodeNameInterface
{
    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getName();
}
