<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Decklist;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class DecklistListener
{
    private $logger;
    private $cache;

    // Since public API requests will cache, even for items that don't exist,
    // we need to clear the cache if a missing decklist exists now.
    // Likewise, we need to return fresh data if the source decklist changes.
    public function __construct(LoggerInterface $logger, AdapterInterface $cache)
    {
      $this->logger = $logger;
      $this->cache = $cache;
    }

    private function clearFromCache(LifecycleEventArgs $args) {
      $entity = $args->getObject();

      if (!$entity instanceof Decklist) {
        return;
      }
      $this->cache->deleteItem('public-api-decklist-' . $entity->getId());
    }

    public function postPersist(LifecycleEventArgs $args)
    {
      $this->clearFromCache($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
      $this->clearFromCache($args);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
      $this->clearFromCache($args);
    }
}
