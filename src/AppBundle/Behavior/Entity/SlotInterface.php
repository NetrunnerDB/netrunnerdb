<?php
/**
 * Created by PhpStorm.
 * User: cedric
 * Date: 05/10/17
 * Time: 13:29
 */

namespace AppBundle\Behavior\Entity;

interface SlotInterface
{

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity();


    /**
     * Get card
     *
     * @return \AppBundle\Entity\Card
     */
    public function getCard();
}
