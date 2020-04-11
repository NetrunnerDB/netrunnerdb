<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * User
 */
class User extends BaseUser
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    private $reputation;

    /**
     * @var string
     */
    private $faction;

    /**
     * @var \DateTime
     */
    private $creation;

    /**
     * @var string
     */
    private $resume;

    /**
     * @var integer
     */
    private $status;

    /*
     * @var integer
     */

    /**
     * @var string
     */
    private $avatar;

    private $donation;

    private $patreon_pledge_cents;

    /**
     * @var Collection|Deck[]
     */
    private $decks;

    /**
     * @var Collection|Decklist[]
     */
    private $decklists;

    /**
     * @var Collection|Comment[]
     */
    private $comments;

    /**
     * @var Collection|Decklist[]
     */
    private $favorites;

    /**
     * @var Collection|Decklist[]
     */
    private $votes;

    /**
     * @var Collection|User[]
     */
    private $following;

    /**
     * @var Collection|User[]
     */
    private $followers;

    /**
     * @var boolean
     */
    private $notif_author = true;

    /**
     * @var boolean
     */
    private $notif_commenter = true;

    /**
     * @var boolean
     */
    private $notif_mention = true;

    /**
     * @var boolean
     */
    private $notif_follow = true;

    /**
     * @var boolean
     */
    private $notif_successor = true;

    /**
     * @var boolean
     */
    private $share_decks = false;

    /**
     * @var Collection
     */
    private $reviewvotes;

    /**
     * @var Collection
     */
    private $reviews;

    /**
     * @var boolean
     */
    private $soft_ban = false;

    /**
     * @var \DateTime
     */
    private $last_activity_check;

    /**
     * @var boolean
     */
    private $autoload_images;

    /**
     * @var array
     */

    private $introductions;

    public function __construct()
    {
        $this->decks = new ArrayCollection();
        $this->decklists = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->reputation = 1;
        $this->faction = 'neutral-runner';
        $this->creation = new \DateTime();
        $this->donation = 0;
        $this->patreon_pledge_cents = 0;

        parent::__construct();
    }

    /**
     * @return integer
     */
    public function getReputation()
    {
        return $this->reputation;
    }

    /**
     * @param integer $reputation
     * @return User
     */
    public function setReputation(int $reputation)
    {
        $this->reputation = $reputation;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreation()
    {
        return $this->creation;
    }

    /**
     * @param \DateTime $creation
     * @return User
     */
    public function setCreation(\DateTime $creation)
    {
        $this->creation = $creation;

        return $this;
    }

    /**
     * @return string
     */
    public function getResume()
    {
        return $this->resume;
    }

    /**
     * @param string $resume
     * @return User
     */
    public function setResume(string $resume)
    {
        $this->resume = $resume;

        return $this;
    }

    /**
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param integer $status
     * @return User
     */
    public function setStatus(int $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * @param string $faction
     * @return $this
     */
    public function setFaction(string $faction)
    {
        $this->faction = $faction;

        return $this;
    }

    /**
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param string $avatar
     * @return User
     */
    public function setAvatar(string $avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return integer
     */
    public function getDonation()
    {
        return $this->donation;
    }

    /**
     * @param integer $donation
     * @return User
     */
    public function setDonation(int $donation)
    {
        $this->donation = $donation;

        return $this;
    }

    /**
     * @return integer
     */
    public function getPatreonPledgeCents()
    {
        return $this->patreon_pledge_cents;
    }

    /**
     * @param integer $pledge_cents
     * @return User
     */
    public function setPatreonPledgeCents(int $pledge_cents)
    {
        $this->patreon_pledge_cents = $pledge_cents;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSupporter()
    {
        return ($this->donation > 0 || $this->patreon_pledge_cents > 0);
    }

    /**
     * @return Deck[]|Collection
     */
    public function getDecks()
    {
        return $this->decks;
    }

    /**
     * @return Decklist[]|Collection
     */
    public function getDecklists()
    {
        return $this->decklists;
    }

    /**
     * @return Comment[]|Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param Decklist $decklist
     * @return $this
     */
    public function addFavorite(Decklist $decklist)
    {
        $decklist->addFavorite($this);
        $this->favorites[] = $decklist;

        return $this;
    }

    /**
     * @param Decklist $decklist
     * @return $this
     */
    public function removeFavorite(Decklist $decklist)
    {
        $decklist->removeFavorite($this);
        $this->favorites->removeElement($decklist);

        return $this;
    }

    /**
     * @return Decklist[]|Collection
     */
    public function getFavorites()
    {
        return $this->favorites;
    }

    /**
     * @param Decklist $decklist
     * @return $this
     */
    public function addVote(Decklist $decklist)
    {
        $decklist->addVote($this);
        $this->votes[] = $decklist;

        return $this;
    }

    /**
     * @return Decklist[]|Collection
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function addFollowing(User $user)
    {
        $user->addFollower($this);
        $this->following[] = $user;

        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function addFollower(User $user)
    {
        $this->followers[] = $user;

        return $this;
    }

    /**
     * @return User[]|Collection
     */
    public function getFollowing()
    {
        return $this->following;
    }

    /**
     * Remove from following
     * @param User $user
     * @return User
     */
    public function removeFollowing(User $user)
    {
        $user->removeFollower($this);
        $this->following->removeElement($user);

        return $this;
    }

    /**
     * Remove from followers
     * @param User $user
     * @return User
     */
    public function removeFollower(User $user)
    {
        $this->followers->removeElement($user);

        return $this;
    }

    /**
     * @return User[]|Collection
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    public function getMaxNbDecks()
    {
        return 2*(100+floor($this->reputation/ 10));
    }

    /**
     * @return boolean
     */
    public function getNotifAuthor()
    {
        return $this->notif_author;
    }

    /**
     * @param boolean $notifAuthor
     * @return User
     */
    public function setNotifAuthor(bool $notifAuthor)
    {
        $this->notif_author = $notifAuthor;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getNotifCommenter()
    {
        return $this->notif_commenter;
    }

    /**
     * @param boolean $notifCommenter
     * @return User
     */
    public function setNotifCommenter(bool $notifCommenter)
    {
        $this->notif_commenter = $notifCommenter;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getNotifMention()
    {
        return $this->notif_mention;
    }

    /**
     * @param boolean $notifMention
     * @return User
     */
    public function setNotifMention(bool $notifMention)
    {
        $this->notif_mention = $notifMention;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getNotifFollow()
    {
        return $this->notif_follow;
    }

    /**
     * @param boolean $notifFollow
     * @return User
     */
    public function setNotifFollow(bool $notifFollow)
    {
        $this->notif_follow = $notifFollow;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getNotifSuccessor()
    {
        return $this->notif_successor;
    }

    /**
     * @param boolean $notifSuccessor
     * @return User
     */
    public function setNotifSuccessor(bool $notifSuccessor)
    {
        $this->notif_successor = $notifSuccessor;

        return $this;
    }

    /**
     * Add decks
     * @param Deck $decks
     * @return User
     */
    public function addDeck(Deck $decks)
    {
        $this->decks[] = $decks;

        return $this;
    }

    /**
     * Remove decks
     * @param Deck $decks
     */
    public function removeDeck(Deck $decks)
    {
        $this->decks->removeElement($decks);
    }

    /**
     * Add decklists
     * @param Decklist $decklists
     * @return User
     */
    public function addDecklist(Decklist $decklists)
    {
        $this->decklists[] = $decklists;

        return $this;
    }

    /**
     * Remove decklists
     * @param Decklist $decklists
     */
    public function removeDecklist(Decklist $decklists)
    {
        $this->decklists->removeElement($decklists);
    }

    /**
     * Add comments
     * @param Comment $comments
     * @return User
     */
    public function addComment(Comment $comments)
    {
        $this->comments[] = $comments;

        return $this;
    }

    /**
     * Remove comments
     * @param Comment $comments
     */
    public function removeComment(Comment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Remove votes
     * @param Decklist $votes
     */
    public function removeVote(Decklist $votes)
    {
        $this->votes->removeElement($votes);
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Review $review
     * @return $this
     */
    public function addReviewvote(Review $review)
    {
        $review->addVote($this);
        $this->reviewvotes[] = $review;

        return $this;
    }

    /**
     * Remove reviewvotes
     * @param Review $reviewvotes
     */
    public function removeReviewvote(Review $reviewvotes)
    {
        $this->reviewvotes->removeElement($reviewvotes);
    }

    /**
     * @return Collection
     */
    public function getReviewvotes()
    {
        return $this->reviewvotes;
    }

    /**
     * Add reviews
     * @param Review $reviews
     * @return User
     */
    public function addReview(Review $reviews)
    {
        $this->reviews[] = $reviews;

        return $this;
    }

    /**
     * Remove reviews
     * @param Review $reviews
     */
    public function removeReview(Review $reviews)
    {
        $this->reviews->removeElement($reviews);
    }

    /**
     * @return Collection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * @return boolean
     */
    public function getShareDecks()
    {
        return $this->share_decks;
    }

    /**
     * @param boolean $shareDecks
     * @return User
     */
    public function setShareDecks(bool $shareDecks)
    {
        $this->share_decks = $shareDecks;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getSoftBan()
    {
        return $this->soft_ban;
    }

    /**
     * @param boolean $softBan
     * @return User
     */
    public function setSoftBan(bool $softBan)
    {
        $this->soft_ban = $softBan;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastActivityCheck()
    {
        return $this->last_activity_check;
    }

    /**
     * @param \DateTime $lastActivityCheck
     * @return User
     */
    public function setLastActivityCheck(\DateTime $lastActivityCheck)
    {
        $this->last_activity_check = $lastActivityCheck;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAutoloadImages()
    {
        return $this->autoload_images;
    }

    /**
     * @param boolean $autoloadImages
     * @return User
     */
    public function setAutoloadImages(bool $autoloadImages)
    {
        $this->autoload_images = $autoloadImages;

        return $this;
    }

    /**
     * @return array
     */
    public function getIntroductions()
    {
        return $this->introductions;
    }

    /**
     * @param array $introductions
     * @return User
     */
    public function setIntroductions(array $introductions)
    {
        $this->introductions = $introductions;

        return $this;
    }
}
