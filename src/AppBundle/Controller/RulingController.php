<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Card;
use AppBundle\Entity\Ruling;
use AppBundle\Service\TextProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of RulingController
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class RulingController extends Controller
{
    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @param TextProcessor          $textProcessor
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_GURU")
     */
    public function postAction(Request $request, EntityManagerInterface $entityManager, TextProcessor $textProcessor)
    {
        $card = $entityManager->getRepository('AppBundle:Card')->find($request->request->get('card_id'));

        if (!$card instanceof Card) {
            throw $this->createNotFoundException();
        }

        $rawtext = $request->request->get('text');
        $text = $textProcessor->transform($rawtext);

        $ruling = new Ruling();
        $ruling->setCard($card);
        $ruling->setRawtext($rawtext);
        $ruling->setText($text);
        $entityManager->persist($ruling);
        $entityManager->flush();

        return $this->redirectToRoute('cards_zoom', ['card_code' => $card->getCode()]);
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @param TextProcessor          $textProcessor
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_GURU")
     */
    public function editAction(Request $request, EntityManagerInterface $entityManager, TextProcessor $textProcessor)
    {
        $ruling = $entityManager->getRepository('AppBundle:Ruling')->find($request->request->get('ruling_id'));

        if (!$ruling instanceof Ruling) {
            throw $this->createNotFoundException();
        }

        $rawtext = $request->request->get('text');
        $text = $textProcessor->transform($rawtext);

        $ruling->setRawtext($rawtext);
        $ruling->setText($text);
        $entityManager->flush();

        return $this->redirectToRoute('cards_zoom', ['card_code' => $ruling->getCard()->getCode()]);
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @IsGranted("ROLE_GURU")
     */
    public function deleteAction(Request $request, EntityManagerInterface $entityManager)
    {
        $ruling = $entityManager->getRepository('AppBundle:Ruling')->find($request->request->get('ruling_id'));

        if (!$ruling instanceof Ruling) {
            throw $this->createNotFoundException();
        }

        $entityManager->remove($ruling);
        $entityManager->flush();

        return $this->redirectToRoute('cards_zoom', ['card_code' => $ruling->getCard()->getCode()]);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(EntityManagerInterface $entityManager)
    {
        $list = $entityManager->getRepository('AppBundle:Card')->findAll();

        $response = $this->render('/Rulings/list.html.twig', [
            'list' => $list,
        ]);
        $response->setPublic();
        $response->setMaxAge($this->getParameter('short_cache'));

        return $response;
    }
}
