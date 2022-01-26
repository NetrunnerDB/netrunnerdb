<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Deck;
use Symfony\Component\HttpFoundation\Response;

class TagController extends Controller
{
    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function addAction(Request $request, EntityManagerInterface $entityManager)
    {
        $list_uuid = $request->get('uuids');
        if (!is_array($list_uuid)) {
            $list_uuid = explode(' ', $list_uuid);
        }
        $list_tag = $request->get('tags');
        if (!is_array($list_tag)) {
            $list_tag = explode(' ', $list_tag);
        }

        $list_tag = array_map(function ($tag) {
            return preg_replace('/[^a-zA-Z0-9-]/', '', $tag);
        }, $list_tag);

        $response = ["success" => true];

        foreach ($list_uuid as $uuid) {
            /** @var Deck $deck */
            $deck = $entityManager->getRepository('AppBundle:Deck')->findOneBy(["uuid" => $uuid]);
            if (!$deck) {
                continue;
            }
            if ($this->getUser()->getId() != $deck->getUser()->getId()) {
                continue;
            }
            $tags = array_values(array_filter(array_unique(array_merge(explode(' ', $deck->getTags()), $list_tag)), function ($tag) {
                return $tag != "";
            }));
            $response['tags'][$deck->getUuid()] = $tags;
            $deck->setTags(implode(' ', $tags));
        }
        $entityManager->flush();

        return new Response(json_encode($response));
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function removeAction(Request $request, EntityManagerInterface $entityManager)
    {
        $list_uuid = $request->get('uuids');
        $list_tag = $request->get('tags');

        $response = ["success" => true];

        foreach ($list_uuid as $uuid) {
            /** @var Deck $deck */
            $deck = $entityManager->getRepository('AppBundle:Deck')->findOneBy(["uuid" => $uuid]);
            if (!$deck) {
                continue;
            }
            if ($this->getUser()->getId() != $deck->getUser()->getId()) {
                continue;
            }
            $tags = array_values(array_diff(explode(' ', $deck->getTags()), $list_tag));
            $response['tags'][$deck->getUuid()] = $tags;
            $deck->setTags(implode(' ', $tags));
        }
        $entityManager->flush();

        return new Response(json_encode($response));
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function clearAction(Request $request, EntityManagerInterface $entityManager)
    {
        $list_uuid = $request->get('uuids');

        $response = ["success" => true];

        foreach ($list_uuid as $uuid) {
            /** @var Deck $deck */
            $deck = $entityManager->getRepository('AppBundle:Deck')->findOneBy(["uuid" => $uuid]);
            if (!$deck) {
                continue;
            }
            if ($this->getUser()->getId() != $deck->getUser()->getId()) {
                continue;
            }
            $response['tags'][$deck->getUuid()] = [];
            $deck->setTags('');
        }
        $entityManager->flush();

        return new Response(json_encode($response));
    }
}
