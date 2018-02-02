<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ToolsController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function demoAction()
    {
        return $this->render('/Tools/demo.html.twig');
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function extdecklistAction(int $id)
    {
        return $this->render('/Tools/demo-ext-decklist.html.twig', [
                'id' => $id
        ]);
    }
}
