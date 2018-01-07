<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Entity\Deck;
use AppBundle\Entity\Decklist;
use AppBundle\Entity\Decklistslot;
use AppBundle\Entity\Comment;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Legality;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SocialController extends Controller
{

    /**
     * checks to see if a deck can be published in its current saved state
     */
    public function publishAction ($deck_id)
    {
        $response = new JsonResponse();

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();

        /* @var $deck \AppBundle\Entity\Deck */
        $deck = $em->getRepository('AppBundle:Deck')->find($deck_id);

        if($this->getUser()->getId() != $deck->getUser()->getId()) {
            throw $this->createAccessDeniedException("Unauthorized user.");
        }

        $lastPack = $deck->getLastPack();
        if(!$lastPack->getDateRelease() || $lastPack->getDateRelease() > new \DateTime()) {
            $response->setData([
                'allowed' => FALSE,
                'message' => "You cannot publish this deck yet, because it has unreleased cards.",
            ]);
            return $response;
        }
        $judge = $this->get('judge');
        $analyse = $judge->analyse($deck->getSlots());

        if(is_string($analyse)) {
            $response->setData([
                'allowed' => FALSE,
                'message' => $judge->problem($analyse),
            ]);
            return $response;
        }

        $new_content = json_encode($deck->getContent());
        $new_signature = md5($new_content);
        $old_decklists = $this->getDoctrine()
                ->getRepository('AppBundle:Decklist')
                ->findBy(array(
            'signature' => $new_signature
        ));
        foreach($old_decklists as $decklist) {
            if(json_encode($decklist->getContent()) == $new_content) {
                $url = $this->generateUrl('decklist_detail', array(
                    'decklist_id' => $decklist->getId(),
                    'decklist_name' => $decklist->getPrettyName()
                ));
                $response->setData([
                    'allowed' => TRUE,
                    'message' => 'This deck is <a href="' . $url . '">already published</a>. Are you sure you want to publish a duplicate?',
                ]);
                return $response;
            }
        }

        $response->setData([
            'allowed' => TRUE,
            'message' => '',
        ]);
        return $response;
    }

    /**
     * creates a new decklist from a deck (publish action)
     */
    public function newAction (Request $request)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();

        /* @var $manager \AppBundle\Service\DecklistManager */
        $manager = $this->get('decklists');
        
        $deck_id = filter_var($request->request->get('deck_id'), FILTER_SANITIZE_NUMBER_INT);
        /* @var $deck \AppBundle\Entity\Deck */
        $deck = $this->getDoctrine()
                ->getRepository('AppBundle:Deck')
                ->find($deck_id);
        if($this->getUser()->getId() != $deck->getUser()->getId()) {
            throw $this->createAccessDeniedException("Unauthorized user.");
        }

        $lastPack = $deck->getLastPack();
        if(!$lastPack->getDateRelease() || $lastPack->getDateRelease() > new \DateTime()) {
            throw $this->createAccessDeniedException("Cannot publish deck because of unreleased cards.");
        }

        /* @var $judge \AppBundle\Service\Judge */
        $judge = $this->get('judge');
        $analyse = $judge->analyse($deck->getSlots());
        if(is_string($analyse)) {
            throw $this->createAccessDeniedException($judge->problem($analyse));
        }

        $new_content = json_encode($deck->getContent());
        $new_signature = md5($new_content);

        $name = substr(filter_var($request->request->get('name'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES), 0, 60);
        if(empty($name)) {
            $name = "Untitled";
        }

        $rawdescription = \trim($request->request->get('description'));
        $description = $this->get('texts')->markdown($rawdescription);

        $tournament_id = filter_var($request->request->get('tournament'), FILTER_SANITIZE_NUMBER_INT);
        $tournament = $em->getRepository('AppBundle:Tournament')->find($tournament_id);

        $decklist = new Decklist();
        $decklist->setName($name);
        $decklist->setPrettyname(preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($name)));
        $decklist->setRawdescription($rawdescription);
        $decklist->setDescription($description);
        $decklist->setUser($this->getUser());
        $decklist->setSignature($new_signature);
        $decklist->setIdentity($deck->getIdentity());
        $decklist->setFaction($deck->getIdentity()
                        ->getFaction());
        $decklist->setSide($deck->getSide());
        $decklist->setLastPack($deck->getLastPack());
        $decklist->setNbvotes(0);
        $decklist->setNbfavorites(0);
        $decklist->setNbcomments(0);
        $decklist->setDotw(0);
        $decklist->setModerationStatus(Decklist::MODERATION_PUBLISHED);
        $decklist->setTournament($tournament);
        $decklist->setIsLegal(true);
		$decklist->setMwl($deck->getMwl());
        foreach($deck->getSlots() as $slot) {
            $card = $slot->getCard();
            $decklistslot = new Decklistslot();
            $decklistslot->setQuantity($slot->getQuantity());
            $decklistslot->setCard($card);
            $decklistslot->setDecklist($decklist);
            $decklist->getSlots()->add($decklistslot);
        }
        if(count($deck->getChildren())) {
            $decklist->setPrecedent($deck->getChildren()[0]);
        } else
        if($deck->getParent()) {
            $decklist->setPrecedent($deck->getParent());
        }
        $decklist->setParent($deck);
        $decklist->setRotation($this->get('rotation_service')->findCompatibleRotation($decklist));

        $em->persist($decklist);

        $mwls = $em->getRepository('AppBundle:Mwl')->findAll();
        foreach($mwls as $mwl) {
            $legality = new Legality();
            $legality->setDecklist($decklist);
            $legality->setMwl($mwl);
            $judge->computeLegality($legality);
            $em->persist($legality);
        }

        $em->flush();
        $decklist->setIsLegal($manager->isDecklistLegal($decklist));
        $em->flush();

        return $this->redirect($this->generateUrl('decklist_detail', array(
                            'decklist_id' => $decklist->getId(),
                            'decklist_name' => $decklist->getPrettyName()
        )));
    }

    /**
     * displays the content of a decklist along with comments, siblings, similar, etc.
     */
    public function viewAction ($decklist_id, $decklist_name)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('short_cache'));

        $dbh = $this->get('doctrine')->getConnection();
        $rows = $dbh->executeQuery(
                        "SELECT
				d.id,
				d.date_update,
				d.name,
				d.prettyname,
				d.date_creation,
				d.rawdescription,
				d.description,
                		d.signature,
				d.precedent_decklist_id precedent,
                                d.tournament_id,
                                t.description tournament,
				u.id user_id,
				u.username,
				u.faction usercolor,
				u.reputation,
				u.donation,
				c.code identity_code,
				f.code faction_code,
				d.nbvotes,
				d.nbfavorites,
				d.nbcomments,
                                d.moderation_status,
                                d.is_legal,
                                d.rotation_id
				from decklist d
				join user u on d.user_id=u.id
				join card c on d.identity_id=c.id
				join faction f on d.faction_id=f.id
                                left join tournament t on d.tournament_id=t.id
				where d.id=?
                                and d.moderation_status in (0,1,2)
				", array(
                    $decklist_id
                ))->fetchAll();

        if(empty($rows)) {
            throw $this->createNotFoundException();
        }

        $decklist = $rows[0];

        $comments = $dbh->executeQuery(
                        "SELECT
				c.id,
				c.date_creation,
				c.user_id,
				u.username author,
				u.faction authorcolor,
                u.donation,
				c.text,
                c.hidden
				from comment c
				join user u on c.user_id=u.id
				where c.decklist_id=?
				order by date_creation asc", array(
                    $decklist_id
                ))->fetchAll();

        $commenters = array_values(array_unique(array_merge(array($decklist['username']), array_map(function ($item) {
                                    return $item['author'];
                                }, $comments))));

        $cards = $dbh->executeQuery("SELECT
				c.code card_code,
				s.quantity qty
				from decklistslot s
				join card c on s.card_id=c.id
				where s.decklist_id=?
				order by c.code asc", array(
                    $decklist_id
                ))->fetchAll();


        $decklist['comments'] = $comments;
        $decklist['cards'] = $cards;

        $precedent_decklists = $dbh->executeQuery(
                        "SELECT
					d.id,
					d.name,
					d.prettyname,
					d.nbvotes,
					d.nbfavorites,
					d.nbcomments
					from decklist d
					where d.id=?
                                        and d.moderation_status in (0,1)
					order by d.date_creation asc", array(
                    $decklist['precedent']
                ))->fetchAll();

        $successor_decklists = $dbh->executeQuery(
                        "SELECT
					d.id,
					d.name,
					d.prettyname,
					d.nbvotes,
					d.nbfavorites,
					d.nbcomments
					from decklist d
					where d.precedent_decklist_id=?
                                        and d.moderation_status in (0,1)
					order by d.date_creation asc", array(
                    $decklist_id
                ))->fetchAll();

        $duplicate = $dbh->executeQuery(
                        "SELECT
					d.id,
					d.name,
					d.prettyname
					from decklist d
					where d.signature=?
					and d.date_creation<?
                                        and d.moderation_status in (0,1)
					order by d.date_creation asc
					limit 0,1", array(
                    $decklist['signature'],
                    $decklist['date_creation']
                ))->fetch();

        $tournaments = $dbh->executeQuery(
                        "SELECT
					t.id,
					t.description
                FROM tournament t
                ORDER BY t.description desc")->fetchAll();

        $legalities = $dbh->executeQuery(
                        "SELECT
        			m.code,
        			m.name,
        			l.is_legal
        		FROM legality l
        		LEFT JOIN mwl m ON l.mwl_id=m.id
        		WHERE l.decklist_id=?
        		ORDER BY m.date_start DESC", array($decklist_id))->fetchAll();

        $mwl = $dbh->executeQuery(
                        "SELECT
                m.code
            FROM mwl m
            WHERE m.active=1")->fetch();
        if($mwl) {
            $mwl = $mwl['code'];
        }

        $rotation = $decklist['rotation_id']
            ? $dbh->executeQuery("SELECT r.name FROM rotation r WHERE r.id=?", [$decklist['rotation_id']])->fetch()['name']
            : null;

        $claims = $dbh->executeQuery("SELECT "
                . "c.url, "
                . "c.rank, "
                . "c.name, "
                . "c.participants, "
                . "u.id user_id, "
                . "u.username "
                . "FROM Claim c "
                . "JOIN decklist d ON d.id=c.decklist_id "
                . "JOIN user u ON u.id=c.user_id "
                . "WHERE d.id=?", array($decklist_id))->fetchAll();
        
        $packs = $dbh->executeQuery("SELECT DISTINCT
				p.code code,
				p.name name,
				p.position pack_position,
				y.code cycle_code,
				y.name cycle_name,
				y.position cycle_position
				from pack p
				join cycle y on p.cycle_id=y.id
        		join card c on c.pack_id=p.id
        		join decklistslot s on s.card_id=c.id
				where s.decklist_id=?
				order by y.position asc, p.position asc", array($decklist_id))->fetchAll();

        return $this->render('AppBundle:Decklist:decklist.html.twig', array(
                    'pagetitle' => $decklist['name'],
                    'decklist' => $decklist,
                    'commenters' => $commenters,
                    'precedent_decklists' => $precedent_decklists,
                    'successor_decklists' => $successor_decklists,
                    'duplicate' => $duplicate,
                    'tournaments' => $tournaments,
                    'legalities' => $legalities,
                    'claims' => $claims,
                    'mwl' => $mwl,
                    'rotation' => $rotation,
                    'packs' => $packs,
                        ), $response);
    }

    /**
     * adds a decklist to a user's list of favorites
     */
    public function favoriteAction (Request $request)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();

        $user = $this->getUser();
        if(!$user) {
            throw $this->createAccessDeniedException("No user.");
        }

        $decklist_id = filter_var($request->get('id'), FILTER_SANITIZE_NUMBER_INT);

        /* @var $decklist \AppBundle\Entity\Decklist */
        $decklist = $em->getRepository('AppBundle:Decklist')->find($decklist_id);
        if(!$decklist) {
            throw $this->createNotFoundException();
        }            

        $author = $decklist->getUser();

        $dbh = $this->get('doctrine')->getConnection();
        $is_favorite = $dbh->executeQuery("SELECT
				count(*)
				from decklist d
				join favorite f on f.decklist_id=d.id
				where f.user_id=?
				and d.id=?", array(
                            $user->getId(),
                            $decklist_id
                        ))
                        ->fetch(\PDO::FETCH_NUM)[0];

        if($is_favorite) {
            $decklist->setNbfavorites($decklist->getNbfavorites() - 1);
            $user->removeFavorite($decklist);
            if($author->getId() != $user->getId()) {
                $author->setReputation($author->getReputation() - 5);
            }
        } else {
            $decklist->setNbfavorites($decklist->getNbfavorites() + 1);
            $user->addFavorite($decklist);
            if($author->getId() != $user->getId()) {
                $author->setReputation($author->getReputation() + 5);
            }
        }
        $this->get('doctrine')
                ->getManager()
                ->flush();

        return new Response(count($decklist->getFavorites()));
    }

    /**
     * records a user's comment
     */
    public function commentAction (Request $request)
    {
        /* @var $user User */
        $user = $this->getUser();
        if(!$user) {
            throw $this->createAccessDeniedException("No user.");
        }

        $decklist_id = filter_var($request->get('id'), FILTER_SANITIZE_NUMBER_INT);
        $decklist = $this->getDoctrine()
                ->getRepository('AppBundle:Decklist')
                ->find($decklist_id);

        $comment_text = trim($request->get('comment'));
        if($decklist && !empty($comment_text)) {
            $comment_text = preg_replace(
                    '%(?<!\()\b(?:(?:https?|ftp)://)(?:((?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?)(?:[^\s]*)?%iu', '[$1]($0)', $comment_text);

            $mentionned_usernames = array();
            $matches = array();
            if(preg_match_all('/`@([\w_]+)`/', $comment_text, $matches, PREG_PATTERN_ORDER)) {
                $mentionned_usernames = array_unique($matches[1]);
            }

            $comment_html = $this->get('texts')->markdown($comment_text);

            $comment = new Comment();
            $comment->setText($comment_html);
            $comment->setAuthor($user);
            $comment->setDecklist($decklist);
            $comment->setHidden($user->getSoftBan());

            $this->get('doctrine')->getManager()->persist($comment);

            $decklist->setNbcomments($decklist->getNbcomments() + 1);

            $this->get('doctrine')->getManager()->flush();

            // send emails
            $spool = array();
            if($decklist->getUser()->getNotifAuthor()) {
                if(!isset($spool[$decklist->getUser()->getEmail()])) {
                    $spool[$decklist->getUser()->getEmail()] = 'AppBundle:Emails:newcomment_author.html.twig';
                }
            }
            foreach($decklist->getComments() as $comment) {
                /* @var $comment Comment */
                $commenter = $comment->getAuthor();
                if($commenter && $commenter->getNotifCommenter()) {
                    if(!isset($spool[$commenter->getEmail()])) {
                        $spool[$commenter->getEmail()] = 'AppBundle:Emails:newcomment_commenter.html.twig';
                    }
                }
            }
            foreach($mentionned_usernames as $mentionned_username) {
                /* @var $mentionned_user User */
                $mentionned_user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(array('username' => $mentionned_username));
                if($mentionned_user && $mentionned_user->getNotifMention()) {
                    if(!isset($spool[$mentionned_user->getEmail()])) {
                        $spool[$mentionned_user->getEmail()] = 'AppBundle:Emails:newcomment_mentionned.html.twig';
                    }
                }
            }
            unset($spool[$user->getEmail()]);

            $email_data = array(
                'username' => $user->getUsername(),
                'decklist_name' => $decklist->getName(),
                'url' => $this->generateUrl('decklist_detail', array('decklist_id' => $decklist->getId(), 'decklist_name' => $decklist->getPrettyname()), UrlGeneratorInterface::ABSOLUTE_URL) . '#' . $comment->getId(),
                'comment' => $comment_html,
                'profile' => $this->generateUrl('user_profile', array(), UrlGeneratorInterface::ABSOLUTE_URL)
            );
            foreach($spool as $email => $view) {
                $message = \Swift_Message::newInstance()
                        ->setSubject("[NetrunnerDB] New comment")
                        ->setFrom(array("alsciende@netrunnerdb.com" => $user->getUsername()))
                        ->setTo($email)
                        ->setBody($this->renderView($view, $email_data), 'text/html');
                $this->get('mailer')->send($message);
            }
        }

        return $this->redirect($this->generateUrl('decklist_detail', array(
                            'decklist_id' => $decklist_id,
                            'decklist_name' => $decklist->getPrettyName()
        )));
    }

    /**
     * hides a comment, or if $hidden is false, unhide a comment
     */
    public function hidecommentAction ($comment_id, $hidden)
    {
        /* @var $user User */
        $user = $this->getUser();
        if(!$user) {
            throw $this->createAccessDeniedException("No user.");
        }

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();

        $comment = $em->getRepository('AppBundle:Comment')->find($comment_id);
        if(!$comment) {
            throw $this->createNotFoundException();
        }

        if($comment->getDecklist()->getUser()->getId() !== $user->getId()) {
            return new JsonResponse("You don't have permission to edit this comment.");
        }

        $comment->setHidden((boolean) $hidden);
        $em->flush();

        return new JsonResponse(TRUE);
    }

    /**
     * records a user's vote
     */
    public function voteAction (Request $request)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();

        $user = $this->getUser();
        if(!$user) {
            throw $this->createAccessDeniedException("No user.");
        }

        $decklist_id = filter_var($request->get('id'), FILTER_SANITIZE_NUMBER_INT);

        $decklist = $em->getRepository('AppBundle:Decklist')->find($decklist_id);

        if($decklist->getUser()->getId() != $user->getId()) {
            $query = $em->getRepository('AppBundle:Decklist')
                    ->createQueryBuilder('d')
                    ->innerJoin('d.votes', 'u')
                    ->where('d.id = :decklist_id')
                    ->andWhere('u.id = :user_id')
                    ->setParameter('decklist_id', $decklist_id)
                    ->setParameter('user_id', $user->getId())
                    ->getQuery();

            $result = $query->getResult();
            if(empty($result)) {
                $user->addVote($decklist);
                $author = $decklist->getUser();
                $author->setReputation($author->getReputation() + 1);
                $decklist->setNbvotes($decklist->getNbvotes() + 1);
                $this->get('doctrine')->getManager()->flush();
            }
        }
        return new Response(count($decklist->getVotes()));
    }

    /**
     * returns a text file with the content of a decklist
     */
    public function textexportAction ($decklist_id)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('long_cache'));

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();

        /* @var $decklist \AppBundle\Entity\Decklist */
        $decklist = $em->getRepository('AppBundle:Decklist')->find($decklist_id);
        if(!$decklist) {
            throw $this->createNotFoundException();
        }

        /* @var $judge \AppBundle\Service\Judge */
        $judge = $this->get('judge');
        $classement = $judge->classe($decklist->getSlots(), $decklist->getIdentity());

        $lines = array();
        $types = array(
            "Event",
            "Hardware",
            "Resource",
            "Icebreaker",
            "Program",
            "Agenda",
            "Asset",
            "Upgrade",
            "Operation",
            "Barrier",
            "Code Gate",
            "Sentry",
            "ICE"
        );

        $lines[] = $decklist->getIdentity()->getTitle() . " (" . $decklist->getIdentity()
                        ->getPack()
                        ->getName() . ")";
        foreach($types as $type) {
            if(isset($classement[$type]) && $classement[$type]['qty']) {
                $lines[] = "";
                $lines[] = $type . " (" . $classement[$type]['qty'] . ")";
                foreach($classement[$type]['slots'] as $slot) {
                    $inf = "";
                    for($i = 0; $i < $slot['influence']; $i ++) {
                        if($i % 5 == 0) {
                            $inf .= " ";
                        }
                        $inf .= "•";
                    }
                    $lines[] = $slot['qty'] . "x " . $slot['card']->getTitle() . " (" . $slot['card']->getPack()->getName() . ") " . $inf;
                }
            }
        }
        $content = implode("\r\n", $lines);

        $name = mb_strtolower($decklist->getName());
        $name = preg_replace('/[^a-zA-Z0-9_\-]/', '-', $name);
        $name = preg_replace('/--+/', '-', $name);

        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set('Content-Disposition', 'attachment;filename=' . $name . ".txt");

        $response->setContent($content);
        return $response;
    }

    /**
     * returns a octgn file with the content of a decklist
     */
    public function octgnexportAction ($decklist_id)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('long_cache'));

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();

        /* @var $decklist \AppBundle\Entity\Decklist */
        $decklist = $em->getRepository('AppBundle:Decklist')->find($decklist_id);
        if(!$decklist) {
            throw $this->createNotFoundException();
        }

        $rd = array();
        $identity = null;
        /** @var $slot Decklistslot */
        foreach($decklist->getSlots() as $slot) {
            if($slot->getCard()
                            ->getType()
                            ->getName() == "Identity") {
                $identity = array(
                    "index" => $slot->getCard()->getCode(),
                    "name" => $slot->getCard()->getTitle()
                );
            } else {
                $rd[] = array(
                    "index" => $slot->getCard()->getCode(),
                    "name" => $slot->getCard()->getTitle(),
                    "qty" => $slot->getQuantity()
                );
            }
        }
        $name = mb_strtolower($decklist->getName());
        $name = preg_replace('/[^a-zA-Z0-9_\-]/', '-', $name);
        $name = preg_replace('/--+/', '-', $name);
        if(empty($identity)) {
            return new Response('no identity found');
        }
        return $this->octgnexport("$name.o8d", $identity, $rd, $decklist->getRawdescription(), $response);
    }

    /**
     * does the "downloadable file" part of the export
     */
    public function octgnexport ($filename, $identity, $rd, $description, $response)
    {

        $content = $this->renderView('AppBundle::octgn.xml.twig', array(
            "identity" => $identity,
            "rd" => $rd,
            "description" => strip_tags($description)
        ));

        $response->headers->set('Content-Type', 'application/octgn');
        $response->headers->set('Content-Disposition', 'attachment;filename=' . $filename);

        $response->setContent($content);
        return $response;
    }

    /**
     * edits name and description of a decklist by its publisher
     */
    public function editAction ($decklist_id, Request $request)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();

        $user = $this->getUser();
        if(!$user) {
            throw $this->createAccessDeniedException("No user.");
        }
        /* @var $decklist Decklist */
        $decklist = $em->getRepository('AppBundle:Decklist')->find($decklist_id);
        if(!$decklist || $decklist->getUser()->getId() != $user->getId()) {
            throw $this->createAccessDeniedException("No decklist or not authorized to edit decklist.");
        }

        $name = trim(filter_var($request->request->get('name'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
        $name = substr($name, 0, 60);
        if(empty($name)) {
            $name = "Untitled";
        }
        $rawdescription = trim($request->request->get('description'));
        $description = $this->get('texts')->markdown($rawdescription);

        $tournament_id = filter_var($request->request->get('tournament'), FILTER_SANITIZE_NUMBER_INT);
        $tournament = $em->getRepository('AppBundle:Tournament')->find($tournament_id);

        $derived_from = $request->request->get('derived');
        $matches = array();
        if(preg_match('/^(\d+)$/', $derived_from, $matches)) {
            
        } else if(preg_match('/decklist\/(\d+)\//', $derived_from, $matches)) {
            $derived_from = $matches[1];
        } else {
            $derived_from = null;
        }

        if(!$derived_from) {
            $precedent_decklist = null;
        } else {
            /* @var $precedent_decklist Decklist */
            $precedent_decklist = $em->getRepository('AppBundle:Decklist')->find($derived_from);
            if(!$precedent_decklist || $precedent_decklist->getDateCreation() > $decklist->getDateCreation()) {
                $precedent_decklist = $decklist->getPrecedent();
            }
        }

        $decklist->setName($name);
        $decklist->setPrettyname(preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($name)));
        $decklist->setRawdescription($rawdescription);
        $decklist->setDescription($description);
        $decklist->setPrecedent($precedent_decklist);
        $decklist->setTournament($tournament);
        
        if($decklist->getModerationStatus() === Decklist::MODERATION_TRASHED) {
            $this->get('moderation_helper')->changeStatus($this->getUser(), $decklist, Decklist::MODERATION_RESTORED);
        }        
        
        $em->flush();

        return $this->redirect($this->generateUrl('decklist_detail', array(
                            'decklist_id' => $decklist_id,
                            'decklist_name' => $decklist->getPrettyName()
        )));
    }

    /**
     * deletes a decklist if it has no comment, no vote, no favorite
     */
    public function deleteAction ($decklist_id)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();

        $user = $this->getUser();
        if(!$user) {
            throw $this->createAccessDeniedException("No user.");
        }

        $decklist = $em->getRepository('AppBundle:Decklist')->find($decklist_id);
        if(!$decklist || $decklist->getUser()->getId() != $user->getId()) {
            throw $this->createAccessDeniedException("No decklist or not authorized to delete decklist.");
        }
        if($decklist->getNbvotes() || $decklist->getNbfavorites() || $decklist->getNbcomments()) {
            throw $this->createAccessDeniedException("Decklist cannot be deleted because of votes, favorites or comments.");
        }

        $precedent = $decklist->getPrecedent();

        $children_decks = $decklist->getChildren();
        /* @var $children_deck Deck */
        foreach($children_decks as $children_deck) {
            $children_deck->setParent($precedent);
        }

        $successor_decklists = $decklist->getSuccessors();
        /* @var $successor_decklist Decklist */
        foreach($successor_decklists as $successor_decklist) {
            $successor_decklist->setPrecedent($precedent);
        }

        $em->remove($decklist);
        $em->flush();

        return $this->redirect($this->generateUrl('decklists_list', array(
                            'type' => 'mine'
        )));
    }

    /**
     * displays details about a user and the list of decklists he published
     */
    public function profileAction ($user_id, $user_name, $page)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('short_cache'));

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();

        /* @var $user \AppBundle\Entity\User */
        $user = $em->getRepository('AppBundle:User')->find($user_id);
        if(!$user) {
            throw $this->createNotFoundException();
        }            

        $decklists = $em->getRepository('AppBundle:Decklist')->findBy(array('user' => $user));
        $nbdecklists = count($decklists);

        $reviews = $em->getRepository('AppBundle:Review')->findBy(array('user' => $user));
        $nbreviews = count($reviews);


        return $this->render('AppBundle:Default:public_profile.html.twig', array(
                    'pagetitle' => $user->getUsername(),
                    'user' => $user,
                    'nbdecklists' => $nbdecklists,
                    'nbreviews' => $nbreviews
                        ), $response);
    }

    public function followAction ($user_id, Request $request)
    {
        /* who is following */
        $follower = $this->getUser();
        /* who is followed */
        $following = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->find($user_id);

        if(!$follower) {
            throw $this->createAccessDeniedException("No user.");
        }

        $found = FALSE;
        foreach($follower->getFollowing() as $user) {
            if($user->getId() === $following->getId()) {
                $found = TRUE;
                break;
            }
        }

        if(!$found) {
            $follower->addFollowing($following);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirect($this->generateUrl('user_profile_view', array(
                            "_locale" => $request->getLocale(),
                            "user_id" => $following->getId(),
                            "user_name" => $following->getUsername()
        )));
    }

    public function unfollowAction ($user_id, Request $request)
    {
        /* who is following */
        $follower = $this->getUser();
        /* who is followed */
        $following = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->find($user_id);

        if(!$follower) {
            throw $this->createAccessDeniedException("No user.");
        }

        $found = FALSE;
        foreach($follower->getFollowing() as $user) {
            if($user->getId() === $following->getId()) {
                $found = TRUE;
                break;
            }
        }

        if($found) {
            $follower->removeFollowing($following);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirect($this->generateUrl('user_profile_view', array(
                            "_locale" => $request->getLocale(),
                            "user_id" => $following->getId(),
                            "user_name" => $following->getUsername()
        )));
    }

    public function usercommentsAction ($page, Request $request)
    {
        $response = new Response();
        $response->setPrivate();

        /* @var $user \AppBundle\Entity\User */
        $user = $this->getUser();

        $limit = 100;
        if($page < 1) {
            $page = 1;
        }            
        $start = ($page - 1) * $limit;

        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->get('doctrine')->getConnection();

        $comments = $dbh->executeQuery(
                        "SELECT SQL_CALC_FOUND_ROWS
				c.id,
				c.text,
				c.date_creation,
				d.id decklist_id,
				d.name decklist_name,
				d.prettyname decklist_prettyname
				from comment c
				join decklist d on c.decklist_id=d.id
				where c.user_id=?
				order by date_creation desc
				limit $start, $limit", array(
                    $user->getId()
                ))
                ->fetchAll(\PDO::FETCH_ASSOC);

        $maxcount = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        // pagination : calcul de nbpages // currpage // prevpage // nextpage
        // à partir de $start, $limit, $count, $maxcount, $page

        $currpage = $page;
        $prevpage = max(1, $currpage - 1);
        $nbpages = min(10, ceil($maxcount / $limit));
        $nextpage = min($nbpages, $currpage + 1);

        $route = $request->get('_route');

        $pages = array();
        for($page = 1; $page <= $nbpages; $page ++) {
            $pages[] = array(
                "numero" => $page,
                "url" => $this->generateUrl($route, array(
                    "page" => $page
                )),
                "current" => $page == $currpage
            );
        }

        return $this->render('AppBundle:Default:usercomments.html.twig', array(
                    'user' => $user,
                    'comments' => $comments,
                    'url' => $request
                            ->getRequestUri(),
                    'route' => $route,
                    'pages' => $pages,
                    'prevurl' => $currpage == 1 ? null : $this->generateUrl($route, array(
                        "page" => $prevpage
                    )),
                    'nexturl' => $currpage == $nbpages ? null : $this->generateUrl($route, array(
                        "page" => $nextpage
                    ))
                        ), $response);
    }

    public function commentsAction ($page, Request $request)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('short_cache'));

        $limit = 100;
        if($page < 1) {
            $page = 1;
        }            
        $start = ($page - 1) * $limit;

        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->get('doctrine')->getConnection();

        $comments = $dbh->executeQuery(
                        "SELECT SQL_CALC_FOUND_ROWS
				c.id,
				c.text,
				c.date_creation,
				d.id decklist_id,
				d.name decklist_name,
				d.prettyname decklist_prettyname,
				u.id user_id,
				u.username author
				from comment c
				join decklist d on c.decklist_id=d.id
				join user u on c.user_id=u.id
				order by date_creation desc
				limit $start, $limit", array())->fetchAll(\PDO::FETCH_ASSOC);

        $maxcount = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];

        // pagination : calcul de nbpages // currpage // prevpage // nextpage
        // à partir de $start, $limit, $count, $maxcount, $page

        $currpage = $page;
        $prevpage = max(1, $currpage - 1);
        $nbpages = min(10, ceil($maxcount / $limit));
        $nextpage = min($nbpages, $currpage + 1);

        $route = $request->get('_route');

        $pages = array();
        for($page = 1; $page <= $nbpages; $page ++) {
            $pages[] = array(
                "numero" => $page,
                "url" => $this->generateUrl($route, array(
                    "page" => $page
                )),
                "current" => $page == $currpage
            );
        }

        return $this->render('AppBundle:Default:allcomments.html.twig', array(
                    'comments' => $comments,
                    'url' => $request
                            ->getRequestUri(),
                    'route' => $route,
                    'pages' => $pages,
                    'prevurl' => $currpage == 1 ? null : $this->generateUrl($route, array(
                        "page" => $prevpage
                    )),
                    'nexturl' => $currpage == $nbpages ? null : $this->generateUrl($route, array(
                        "page" => $nextpage
                    ))
                        ), $response);
    }

    public function donatorsAction ()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('short_cache'));

        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->get('doctrine')->getConnection();

        $users = $dbh->executeQuery("SELECT * FROM user WHERE donation>0 ORDER BY donation DESC, username", array())->fetchAll(\PDO::FETCH_ASSOC);

        return $this->render('AppBundle:Default:donators.html.twig', array(
                    'pagetitle' => 'The Gracious Donators',
                    'donators' => $users
                        ), $response);
    }

    /**
     * Displays a list of items from the activity feed of the User
     * Those items are events linked to the Users followed by our User
     * Possible items
     *  - decklist publish
     *  - decklist comment
     *  - review publish
     *  - review comment 
     * @param integer $days number of days of activity to display
     * @param Request $request
     */
    public function activityAction ($days)
    {
        $response = new Response();
        $response->setPrivate();
        $response->setMaxAge($this->container->getParameter('short_cache'));

        $securityContext = $this->get('security.authorization_checker');
        if(!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException("Access denied");
        }

        $em = $this->getDoctrine()->getManager();

        // max number of displayed items for each category
        $max_items = 30;

        $items = $this->get('activity_helper')->getItems($this->getUser(), $max_items, $days);
        $items_by_day = $this->get('activity_helper')->sortByDay($items);

        // recording date of activity check
        $this->getUser()->setLastActivityCheck(new \DateTime());
        $em->flush();

        return $this->render('AppBundle:Activity:activity.html.twig', array(
                    'pagetitle' => 'Activity',
                    'items_by_day' => $items_by_day,
                    'max' => $days
                        ), $response);
    }

    /**
     * Change the moderation status of a decklist
     * @param integer $decklist_id
     * @param integer $status
     * @param integer $modflag_id
     */
    public function moderateAction ($decklist_id, $status, $modflag_id = null)
    {
        $response = new Response();
        $response->setPrivate();
        $response->setMaxAge($this->container->getParameter('short_cache'));

        $securityContext = $this->get('security.authorization_checker');
        if(!$securityContext->isGranted('ROLE_MODERATOR')) {
            throw $this->createAccessDeniedException('Access denied');
        }

        $em = $this->getDoctrine()->getManager();

        /* @var $decklist Decklist */
        $decklist = $em->getRepository('AppBundle:Decklist')->find($decklist_id);
        if(!$decklist) {
            throw $this->createNotFoundException();
        }

        $this->get('moderation_helper')->changeStatus($this->getUser(), $decklist, $status, $modflag_id);

        $em->flush();

        return new JsonResponse(['success' => true]);
    }

}
