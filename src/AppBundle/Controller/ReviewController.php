<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Service\TextProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Review;
use AppBundle\Entity\Card;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use AppBundle\Entity\Reviewcomment;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ReviewController extends Controller
{
    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @param TextProcessor          $textProcessor
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function postAction(Request $request, EntityManagerInterface $entityManager, TextProcessor $textProcessor)
    {
        /** @var User $user */
        $user = $this->getUser();

        // a user cannot post more reviews than her reputation
        if ($user->getReviews()->count() >= $user->getReputation()) {
            return new Response(json_encode("Your reputation doesn't allow you to write more reviews."));
        }

        // bot prevention - this shouldn't ever happen unless a user messes with the comment input source
        if (!$user->isVerified()) {
            return new Response(json_encode("You must have at least one private decklist before you may write a review."));
        }

        $card_id = filter_var($request->get('card_id'), FILTER_SANITIZE_NUMBER_INT);
        /** @var Card $card */
        $card = $entityManager->getRepository('AppBundle:Card')->find($card_id);
        if (!$card) {
            throw new BadRequestHttpException("Unable to find card.");
        }
        if (!$card->getPack()->getDateRelease()) {
            return new Response(json_encode("You may not write a review for an unreleased card."));
        }

        // checking the user didn't already write a review for that card
        $review = $entityManager->getRepository('AppBundle:Review')->findOneBy(['card' => $card, 'user' => $user]);
        if ($review) {
            return new Response(json_encode("You cannot write more than 1 review for a given card."));
        }

        $review_raw = trim($request->get('review'));

        $review_raw = preg_replace(
            '%(?<!\()\b(?:(?:https?|ftp)://)(?:((?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?)(?:[^\s]*)?%iu',
            '[$1]($0)',

            $review_raw

        );

        $review_html = $textProcessor->markdown($review_raw);
        if (!$review_html) {
            return new Response(json_encode("Your review is empty."));
        }

        $review = new Review();
        $review->setCard($card);
        $review->setUser($user);
        $review->setRawtext($review_raw);
        $review->setText($review_html);
        $review->setNbvotes(0);

        $entityManager->persist($review);

        $entityManager->flush();

        return new Response(json_encode(true));
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @param TextProcessor          $textProcessor
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function editAction(Request $request, EntityManagerInterface $entityManager, TextProcessor $textProcessor)
    {
        $user = $this->getUser();

        $review_id = filter_var($request->get('review_id'), FILTER_SANITIZE_NUMBER_INT);
        /** @var Review $review */
        $review = $entityManager->getRepository('AppBundle:Review')->find($review_id);
        if (!$review) {
            throw new BadRequestHttpException("Unable to find review.");
        }
        if ($review->getUser()->getId() !== $user->getId()) {
            throw new UnauthorizedHttpException("You cannot edit this review.");
        }

        $review_raw = trim($request->get('review'));

        $review_raw = preg_replace(
            '%(?<!\()\b(?:(?:https?|ftp)://)(?:((?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?)(?:[^\s]*)?%iu',
            '[$1]($0)',

            $review_raw

        );

        $review_html = $textProcessor->markdown($review_raw);
        if (!$review_html) {
            return new Response('Your review is empty.');
        }

        $review->setRawtext($review_raw);
        $review->setText($review_html);

        $entityManager->flush();

        return new Response(json_encode(true));
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function likeAction(Request $request, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();

        $review_id = filter_var($request->request->get('id'), FILTER_SANITIZE_NUMBER_INT);
        /** @var Review $review */
        $review = $entityManager->getRepository('AppBundle:Review')->find($review_id);
        if (!$review) {
            throw $this->createNotFoundException();
        }

        // a user cannot vote on her own review
        if ($review->getUser()->getId() != $user->getId()) {
            // checking if the user didn't already vote on that review
            $query = $entityManager
                ->createQueryBuilder()
                ->select('r')
                ->from(Review::class, 'r')
                ->innerJoin('r.votes', 'u')
                ->where('r.id = :review_id')
                ->andWhere('u.id = :user_id')
                ->setParameter('review_id', $review_id)
                ->setParameter('user_id', $user->getId())
                ->getQuery();

            $result = $query->getResult();
            if (empty($result)) {
                $author = $review->getUser();
                $author->setReputation($author->getReputation() + 1);
                $user->addReviewVote($review);
                $review->setNbvotes($review->getNbvotes() + 1);
                $entityManager->flush();
            }
        }

        return new Response($review->getVotes()->count());
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     *
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function removeAction(Request $request, EntityManagerInterface $entityManager)
    {
        $review_id = filter_var($request->get('id'), FILTER_SANITIZE_NUMBER_INT);
        /** @var Review $review */
        $review = $entityManager->getRepository('AppBundle:Review')->find($review_id);
        if (!$review) {
            throw $this->createNotFoundException();
        }

        $votes = $review->getVotes();
        foreach ($votes as $vote) {
            $review->removeVote($vote);
        }
        $entityManager->remove($review);
        $entityManager->flush();

        return new Response('Done');
    }

    /**
     * @param int                    $page
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function listAction(int $page = 1, Request $request, EntityManagerInterface $entityManager)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('short_cache'));

        $limit = 5;
        if ($page < 1) {
            $page = 1;
        }
        $start = ($page - 1) * $limit;

        $pagetitle = "Card Reviews";

        $dql = "SELECT r FROM AppBundle:Review r JOIN r.card c JOIN c.pack p WHERE p.dateRelease IS NOT NULL ORDER BY r.dateCreation DESC";
        $query = $entityManager->createQuery($dql)->setFirstResult($start)->setMaxResults($limit);

        $paginator = new Paginator($query, false);
        $maxcount = count($paginator);

        $reviews = [];
        foreach ($paginator as $review) {
            $reviews[] = $review;
        }

        // pagination : calcul de nbpages // currpage // prevpage // nextpage
        // à partir de $start, $limit, $count, $maxcount, $page

        $currpage = $page;
        $prevpage = max(1, $currpage - 1);
        $nbpages = min(10, ceil($maxcount / $limit));
        $nextpage = min($nbpages, $currpage + 1);

        $route = $request->get('_route');

        $params = $request->query->all();

        $pages = [];
        for ($page = 1; $page <= $nbpages; $page++) {
            $pages[] = [
                "numero"  => $page,
                "url"     => $this->generateUrl($route, $params + [
                        "page" => $page,
                    ]),
                "current" => $page == $currpage,
            ];
        }

        $user = $this->getUser();

        return $this->render(

            '/Reviews/reviews.html.twig',
            [
                'pagetitle'       => $pagetitle,
                'pagedescription' => "Read the latest user-submitted reviews on the cards.",
                'reviews'         => $reviews,
                'url'             => $request->getRequestUri(),
                'route'           => $route,
                'pages'           => $pages,
                'prevurl'         => $currpage == 1 ? null : $this->generateUrl($route, $params + [
                        "page" => $prevpage,
                    ]),
                'nexturl'         => $currpage == $nbpages ? null : $this->generateUrl($route, $params + [
                        "page" => $nextpage,
                    ]),
                'comments_enabled' => $user->isVerified(),
            ],

            $response

        );
    }

    /**
     * @param User $user
     * @param int                    $page
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     *
     * @ParamConverter("user", class="AppBundle:User", options={"id" = "user_id"})
     */
    public function byauthorAction(User $user, int $page = 1, Request $request, EntityManagerInterface $entityManager)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->getParameter('short_cache'));

        $limit = 5;
        if ($page < 1) {
            $page = 1;
        }
        $start = ($page - 1) * $limit;

        $pagetitle = "Card Reviews by " . $user->getUsername();

        $dql = "SELECT r FROM AppBundle:Review r WHERE r.user = :user ORDER BY r.dateCreation DESC";
        $query = $entityManager->createQuery($dql)->setFirstResult($start)->setMaxResults($limit)->setParameter('user', $user);

        $paginator = new Paginator($query, false);
        $maxcount = count($paginator);

        $reviews = [];
        foreach ($paginator as $review) {
            $reviews[] = $review;
        }

        // pagination : calcul de nbpages // currpage // prevpage // nextpage
        // à partir de $start, $limit, $count, $maxcount, $page

        $currpage = $page;
        $prevpage = max(1, $currpage - 1);
        $nbpages = min(10, ceil($maxcount / $limit));
        $nextpage = min($nbpages, $currpage + 1);

        $route = $request->get('_route');

        $params = $request->query->all();

        $pages = [];
        for ($page = 1; $page <= $nbpages; $page++) {
            $pages[] = [
                "numero"  => $page,
                "url"     => $this->generateUrl($route, $params + [
                        "user_id" => $user->getId(),
                        "page"    => $page,
                    ]),
                "current" => $page == $currpage,
            ];
        }

        return $this->render(

            '/Reviews/reviews.html.twig',
            [
                'pagetitle'       => $pagetitle,
                'pagedescription' => "Read the latest user-submitted reviews on the cards.",
                'reviews'         => $reviews,
                'url'             => $request->getRequestUri(),
                'route'           => $route,
                'pages'           => $pages,
                'prevurl'         => $currpage == 1 ? null : $this->generateUrl($route, $params + [
                        "user_id" => $user->getId(),
                        "page"    => $prevpage,
                    ]),
                'nexturl'         => $currpage == $nbpages ? null : $this->generateUrl($route, $params + [
                        "user_id" => $user->getId(),
                        "page"    => $nextpage,
                    ]),
                'comments_enabled' => $user->isVerified(),
            ],

            $response

        );
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     * @param TextProcessor          $textProcessor
     * @return Response
     *
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function commentAction(Request $request, EntityManagerInterface $entityManager, TextProcessor $textProcessor)
    {
        $user = $this->getUser();

        // bot prevention - this shouldn't ever happen unless a user messes with the comment input source
        if (!$user->isVerified()) {
            return new Response(json_encode("You must have at least one private decklist before you may post comments."));
        }

        $review_id = filter_var($request->get('comment_review_id'), FILTER_SANITIZE_NUMBER_INT);
        /** @var Review $review */
        $review = $entityManager->getRepository('AppBundle:Review')->find($review_id);
        if (!$review) {
            throw new BadRequestHttpException("Unable to find review.");
        }

        $comment_text = trim($request->get('comment'));
        $comment_text = htmlspecialchars($comment_text);
        if (!$comment_text) {
            return new Response('Your comment is empty.');
        }

        $comment = new Reviewcomment();
        $comment->setReview($review);
        $comment->setAuthor($user);
        $comment_html = $textProcessor->markdown($comment_text);
        if (!$comment_html) {
            return new Response('Your comment is empty.');
        }

        $comment->setText($comment_html);

        $entityManager->persist($comment);

        $entityManager->flush();

        return new Response(json_encode(true));
    }
}
