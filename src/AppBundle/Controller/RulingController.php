<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Ruling;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of RulingController
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class RulingController extends Controller
{

    public function postAction (Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_GURU');

        $card = $this->getDoctrine()->getRepository('AppBundle:Card')->find($request->request->get('card_id'));

        if (!$card) {
            throw $this->createNotFoundException();
        }

        $rawtext = $request->request->get('text');
        $text = $this->get('texts')->transform($rawtext);

        $ruling = new Ruling();
        $ruling->setCard($card);
        $ruling->setRawtext($rawtext);
        $ruling->setText($text);
        $this->getDoctrine()->getEntityManager()->persist($ruling);
        $this->getDoctrine()->getEntityManager()->flush();

        return $this->redirectToRoute('cards_zoom', ['card_code' => $card->getCode()]);
    }

    public function editAction (Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_GURU');

        $ruling = $this->getDoctrine()->getRepository('AppBundle:Ruling')->find($request->request->get('ruling_id'));

        if (!$ruling) {
            throw $this->createNotFoundException();
        }

        $rawtext = $request->request->get('text');
        $text = $this->get('texts')->transform($rawtext);

        $ruling->setRawtext($rawtext);
        $ruling->setText($text);
        $this->getDoctrine()->getEntityManager()->flush();

        return $this->redirectToRoute('cards_zoom', ['card_code' => $ruling->getCard()->getCode()]);
    }

    public function deleteAction (Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_GURU');

        $ruling = $this->getDoctrine()->getRepository('AppBundle:Ruling')->find($request->request->get('ruling_id'));

        if (!$ruling) {
            throw $this->createNotFoundException();
        }

        $this->getDoctrine()->getEntityManager()->remove($ruling);
        $this->getDoctrine()->getEntityManager()->flush();

        return $this->redirectToRoute('cards_zoom', ['card_code' => $ruling->getCard()->getCode()]);
    }

    public function listAction ()
    {
        $list = $this->getDoctrine()->getRepository('AppBundle:Card')->findAll();

        return $this->render('AppBundle:Rulings:list.html.twig', array(
                    'list' => $list
        ));
    }

}
