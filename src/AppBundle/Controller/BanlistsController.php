<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BanlistsController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(EntityManagerInterface $entityManager)
    {
        $mwls = $entityManager->getRepository('AppBundle:Mwl')->findBy([], ['dateStart' => 'DESC']);
        $banlists = array();

        // TODO(plural): Do some refactoring later for other places that need similar things and Move this to the CardData service.
        $q = $entityManager->createQuery("select c from AppBundle:Card c where c.id IN (SELECT DISTINCT mc.card_id FROM AppBundle:MwlCard mc) ORDER BY c.code DESC");
        $cards = $q->getResult();
        $code_to_title = array();
        $title_to_most_recent_printing = array();
        $unique_cards = array();
        foreach ($cards as $card) {
            $code_to_title[$card->getCode()] = $card->getTitle();
            if (!array_key_exists($card->getTitle(), $title_to_most_recent_printing)) {
                // We haven't seen this card yet, so add it. We can do this simply since we are sorting the cards in reverse chronological order.
                $title_to_most_recent_printing[$card->getTitle()] = $card->getCode();
                $unique_cards[$card->getCode()] = $card;
            }
        }

        // Until the MWL entity has some more metadata, keep track of this here.
        $mwl_codes_all_currents_banned = [
            'standard-ban-list-20-06' => true,
            'standard-ban-list-20-09' => true,
            'standard-ban-list-21-04' => true,
            'standard-ban-list-21-05' => true
        ];

        foreach ($mwls as $mwl) {
            $x = array();
            $x['name'] = $mwl->getName();
            $x['active'] = $mwl->getActive();
            $x['code'] = $mwl->getCode();
            $x['start_date'] = $mwl->getDateStart();
            $x['mwl_object_delete'] = $mwl;
            $x['all_currents_banned'] = array_key_exists($mwl->getCode(), $mwl_codes_all_currents_banned);
            $mwl_cards = $mwl->getCards(); 
            krsort($mwl_cards);
            $x['cards'] = array();

            $num_cards = 0;
            foreach ($mwl_cards as $code => $mwl_entry) {
                // Use the MWL/Ban List verdict as the key for the array of cards with that same verdict.
                // This simplifies the template logic to have these grouped in the controller.
                $verdict = '';
                if (array_key_exists('deck_limit', $mwl_entry)) {
                  $verdict = 'Banned';
                } elseif (array_key_exists('is_restricted', $mwl_entry)) {
                  $verdict = 'Restricted';
                } elseif (array_key_exists('global_penalty', $mwl_entry)) {
                  $verdict = 'Identity Influence Reduction';
                } elseif (array_key_exists('universal_faction_cost', $mwl_entry)) {
                  $verdict = '+' . $mwl_entry['universal_faction_cost'] . ' Universal Influence';
                }
                if (!array_key_exists($verdict, $x['cards'])) {
                  $x['cards'][$verdict] = array();
                }
                // Only add the most recent printing for each card.
                $card = $unique_cards[$title_to_most_recent_printing[$code_to_title[$code]]];
                $x['cards'][$verdict][$card->getCode()]['card'] = $card; 
                $x['cards'][$verdict][$card->getCode()]['banlist_entry'] = $mwl_entry; 
                ++$num_cards;
            }
            // Keep track of the number of cards here so the template doesn't have to walk all the maps to count the cards. 
            $x['num_cards'] = $num_cards;
            $banlists[] = $x;
        }

        return $this->render('/Banlists/banlists.html.twig', [
            'pagetitle'                     => "Ban Lists",
            'banlists'                      => $banlists,
            'unique_cards'                  => $unique_cards,
            'mwl_codes_all_currents_banned' => $mwl_codes_all_currents_banned,
        ]);
    }
}
