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
    private $role;

    /**
     * @var integer
     */
    private $status;

    /**
     * @var string
     */
    private $avatar;

    /*
     * @var integer
     */
    private $donation;
    
    /**
     * @var Deck[]
     */
    private $decks;

    /**
     * @var Decklist[]
     */
    private $decklists;
    
    /**
     * @var Comment[]
     */
    private $comments;
    
    /**
     * @var Decklist[]
     */
    private $favorites;
    
    /**
     * @var Decklist[]
     */
    private $votes;
    
    /**
     * @var User[]
     */
    private $following;
    
    /**
     * @var User[]
     */
    private $followers;
    
    /**
     * Set reputation
     *
     * @param integer $reputation
     * @return User
     */
    public function setReputation($reputation)
    {
        $this->reputation = $reputation;
    
        return $this;
    }

    /**
     * Get reputation
     *
     * @return integer
     */
    public function getReputation()
    {
        return $this->reputation;
    }

    /**
     * Set creation
     *
     * @param \DateTime $creation
     * @return User
     */
    public function setCreation($creation)
    {
        $this->creation = $creation;
    
        return $this;
    }

    /**
     * Get creation
     *
     * @return \DateTime
     */
    public function getCreation()
    {
        return $this->creation;
    }

    /**
     * Set resume
     *
     * @param string $resume
     * @return User
     */
    public function setResume($resume)
    {
        $this->resume = $resume;
    
        return $this;
    }

    /**
     * Get resume
     *
     * @return string
     */
    public function getResume()
    {
        return $this->resume;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return User
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Set faction
     *
     * @param string $faction
     * @return User
     */
    public function setFaction($faction)
    {
        $this->faction = $faction;
    
        return $this;
    }
    
    /**
     * Get faction
     *
     * @return string
     */
    public function getFaction()
    {
        return $this->faction;
    }
    
    /**
     * Set avatar
     *
     * @param string $avatar
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    
        return $this;
    }

    /**
     * Get avatar
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set donation
     *
     * @param integer $donation
     * @return User
     */
    public function setDonation($donation)
    {
        $this->donation = $donation;
    
        return $this;
    }
    
    /**
     * Get donation
     *
     * @return integer
     */
    public function getDonation()
    {
        return $this->donation;
    }
    
    /**
     * Set deck
     *
     * @param string $decks
     * @return User
     */
    public function setDecks($decks)
    {
        $this->decks = $decks;
    
        return $this;
    }
    
    /**
     * Get deck
     *
     * @return Deck[]
     */
    public function getDecks()
    {
        return $this->decks;
    }

    /**
     * Set decklists
     *
     * @param string $decklists
     * @return $this
     */
    public function setDecklists($decklists)
    {
        $this->decklists = $decklists;
    
        return $this;
    }
    
    /**
     * Get decklists
     *
     * @return string
     */
    public function getDecklists()
    {
        return $this->decklists;
    }
    
    /**
     * Set comments
     *
     * @param string $comments
     * @return $this
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    
        return $this;
    }
    
    /**
     * Get comments
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add to favorites
     *
     * @param Decklist $favorites
     * @return User
     */
    public function addFavorite(Decklist $decklist)
    {
        $decklist->addFavorite($this);
        $this->favorites[] = $decklist;
    
        return $this;
    }

    /**
     * Remove from favorites
     *
     * @param Decklist $favorites
     * @return User
     */
    public function removeFavorite(Decklist $decklist)
    {
        $decklist->removeFavorite($this);
        $this->favorites->removeElement($decklist);
        
        return $this;
    }

    /**
     * @return Decklist[]|ArrayCollection
     */
    public function getFavorites()
    {
        return $this->favorites;
    }
    
    /**
     * Set votes
     *
     * @param Decklist $votes
     * @return User
     */
    public function addVote(Decklist $decklist)
    {
        $decklist->addVote($this);
        $this->votes[] = $decklist;
    
        return $this;
    }

    /**
     * @return Decklist[]|ArrayCollection
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * Set following
     *
     * @param User $following
     * @return User
     */
    public function addFollowing(User $user)
    {
        $user->addFollower($this);
        $this->following[] = $user;
    
        return $this;
    }

    /**
     * @return User[]|ArrayCollection
     */
    public function getFollowing()
    {
        return $this->following;
    }

    /**
     * Remove from following
     *
     * @param User $user
     * @return User
     */
    public function removeFollowing($user)
    {
        $user->removeFollower($this);
        $this->following->removeElement($user);
         
        return $this;
    }
    
    /**
     * Add follower
     *
     * @param User $follower
     * @return User
     */
    public function addFollower($user)
    {
        $this->followers[] = $user;
    
        return $this;
    }

    /**
     * @return User[]|ArrayCollection
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * Remove from followers
     *
     * @param User $user
     * @return User
     */
    public function removeFollower($user)
    {
        $this->followers->removeElement($user);
    
        return $this;
    }
    
    public function getMaxNbDecks()
    {
        return 2*(100+floor($this->reputation/ 10));
    }
    
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
           
        parent::__construct();
    }
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
     * Set notif_author
     *
     * @param boolean $notifAuthor
     * @return User
     */
    public function setNotifAuthor($notifAuthor)
    {
        $this->notif_author = $notifAuthor;
    
        return $this;
    }

    /**
     * Get notif_author
     *
     * @return boolean
     */
    public function getNotifAuthor()
    {
        return $this->notif_author;
    }

    /**
     * Set notif_commenter
     *
     * @param boolean $notifCommenter
     * @return User
     */
    public function setNotifCommenter($notifCommenter)
    {
        $this->notif_commenter = $notifCommenter;
    
        return $this;
    }

    /**
     * Get notif_commenter
     *
     * @return boolean
     */
    public function getNotifCommenter()
    {
        return $this->notif_commenter;
    }

    /**
     * Set notif_mention
     *
     * @param boolean $notifMention
     * @return User
     */
    public function setNotifMention($notifMention)
    {
        $this->notif_mention = $notifMention;
    
        return $this;
    }

    /**
     * Get notif_mention
     *
     * @return boolean
     */
    public function getNotifMention()
    {
        return $this->notif_mention;
    }

    /**
     * Set notif_follow
     *
     * @param boolean $notifFollow
     * @return User
     */
    public function setNotifFollow($notifFollow)
    {
        $this->notif_follow = $notifFollow;
    
        return $this;
    }

    /**
     * Get notif_follow
     *
     * @return boolean
     */
    public function getNotifFollow()
    {
        return $this->notif_follow;
    }

    /**
     * Set notif_successor
     *
     * @param boolean $notifSuccessor
     * @return User
     */
    public function setNotifSuccessor($notifSuccessor)
    {
        $this->notif_successor = $notifSuccessor;
    
        return $this;
    }

    /**
     * Get notif_successor
     *
     * @return boolean
     */
    public function getNotifSuccessor()
    {
        return $this->notif_successor;
    }

    /**
     * Add decks
     *
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
     *
     * @param Deck $decks
     */
    public function removeDeck(Deck $decks)
    {
        $this->decks->removeElement($decks);
    }

    /**
     * Add decklists
     *
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
     *
     * @param Decklist $decklists
     */
    public function removeDecklist(Decklist $decklists)
    {
        $this->decklists->removeElement($decklists);
    }

    /**
     * Add comments
     *
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
     *
     * @param Comment $comments
     */
    public function removeComment(Comment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Remove votes
     *
     * @param Decklist $votes
     */
    public function removeVote(Decklist $votes)
    {
        $this->votes->removeElement($votes);
    }

    /**
     * @var Collection
     */
    private $reviewvotes;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set role
     *
     * @param integer $role
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return integer
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Add reviewvotes
     *
     * @param Review $reviewvotes
     * @return User
     */
    public function addReviewvote(Review $review)
    {
        $review->addVote($this);
        $this->reviewvotes[] = $review;

        return $this;
    }
    
    /**
     * Remove reviewvotes
     *
     * @param Review $reviewvotes
     */
    public function removeReviewvote(Review $reviewvotes)
    {
        $this->reviewvotes->removeElement($reviewvotes);
    }

    /**
     * Get reviewvotes
     *
     * @return Collection
     */
    public function getReviewvotes()
    {
        return $this->reviewvotes;
    }

    /**
     * @var Collection
     */
    private $reviews;


    /**
     * Add reviews
     *
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
     *
     * @param Review $reviews
     */
    public function removeReview(Review $reviews)
    {
        $this->reviews->removeElement($reviews);
    }

    /**
     * Get reviews
     *
     * @return Collection
     */
    public function getReviews()
    {
        return $this->reviews;
    }
    /**
     * @var integer
     */
    protected $id;

    /**
     * Set share_decks
     *
     * @param boolean $shareDecks
     * @return User
     */
    public function setShareDecks($shareDecks)
    {
        $this->share_decks = $shareDecks;

        return $this;
    }

    /**
     * Get share_decks
     *
     * @return boolean
     */
    public function getShareDecks()
    {
        return $this->share_decks;
    }
    /**
     * @var boolean
     */
    private $soft_ban = false;


    /**
     * Set softBan
     *
     * @param boolean $softBan
     *
     * @return User
     */
    public function setSoftBan($softBan)
    {
        $this->soft_ban = $softBan;

        return $this;
    }

    /**
     * Get softBan
     *
     * @return boolean
     */
    public function getSoftBan()
    {
        return $this->soft_ban;
    }
    /**
     * @var \DateTime
     */
    private $last_activity_check;


    /**
     * Set lastActivityCheck
     *
     * @param \DateTime $lastActivityCheck
     *
     * @return User
     */
    public function setLastActivityCheck($lastActivityCheck)
    {
        $this->last_activity_check = $lastActivityCheck;

        return $this;
    }

    /**
     * Get lastActivityCheck
     *
     * @return \DateTime
     */
    public function getLastActivityCheck()
    {
        return $this->last_activity_check;
    }
    /**
     * @var boolean
     */
    private $autoload_images;


    /**
     * Set autoloadImages
     *
     * @param boolean $autoloadImages
     *
     * @return User
     */
    public function setAutoloadImages($autoloadImages)
    {
        $this->autoload_images = $autoloadImages;

        return $this;
    }

    /**
     * Get autoloadImages
     *
     * @return boolean
     */
    public function getAutoloadImages()
    {
        return $this->autoload_images;
    }
    
    /**
     * @var array
     */
    
    private $introductions;


    /**
     * Set introductions
     *
     * @param array $introductions
     *
     * @return User
     */
    public function setIntroductions($introductions)
    {
        $this->introductions = $introductions;

        return $this;
    }

    /**
     * @return array
     */
    public function getIntroductions()
    {
        return $this->introductions;
    }
}
