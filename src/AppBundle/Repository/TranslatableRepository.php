<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Gedmo\Translatable\TranslatableListener;

/**
 * Class TranslatableRepository
 *
 * This is my translatable repository that offers methods to retrieve results with translations
 */
class TranslatableRepository extends EntityRepository
{
    /**
     * @var string Default locale
     */
    protected $defaultLocale;

    /**
     * Sets default locale
     *
     * @param string $locale
     */
    public function setDefaultLocale(string $locale)
    {
        $this->defaultLocale = $locale;
        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @param string|null $locale
     * @param int|null $hydrationMode
     * @return mixed
     */
    public function getOneOrNullResult(QueryBuilder $qb, string $locale = null, int $hydrationMode = null)
    {
        return $this->getTranslatedQuery($qb, $locale)->getOneOrNullResult($hydrationMode);
    }

    /**
     * @param QueryBuilder $qb
     * @param string|null $locale
     * @param int $hydrationMode
     * @return mixed
     */
    public function getResult(QueryBuilder $qb, string $locale = null, int $hydrationMode = AbstractQuery::HYDRATE_OBJECT)
    {
        return $this->getTranslatedQuery($qb, $locale)->getResult($hydrationMode);
    }

    /**
     * @param QueryBuilder $qb
     * @param string|null $locale
     * @return array
     */
    public function getArrayResult(QueryBuilder $qb, string $locale = null)
    {
        return $this->getTranslatedQuery($qb, $locale)->getArrayResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param string|null $locale
     * @param int|null $hydrationMode
     * @return mixed
     */
    public function getSingleResult(QueryBuilder $qb, string $locale = null, int $hydrationMode = null)
    {
        return $this->getTranslatedQuery($qb, $locale)->getSingleResult($hydrationMode);
    }

    /**
     * @param QueryBuilder $qb
     * @param string|null $locale
     * @return array
     */
    public function getScalarResult(QueryBuilder $qb, string $locale = null)
    {
        return $this->getTranslatedQuery($qb, $locale)->getScalarResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param string|null $locale
     * @return mixed
     */
    public function getSingleScalarResult(QueryBuilder $qb, string $locale = null)
    {
        return $this->getTranslatedQuery($qb, $locale)->getSingleScalarResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param string|null $locale
     * @return Query
     */
    protected function getTranslatedQuery(QueryBuilder $qb, string $locale = null)
    {
        $locale = null === $locale ? $this->defaultLocale : $locale;

        $query = $qb->getQuery();

        $query->setHint(
                Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );

        $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale);

        return $query;
    }
}
