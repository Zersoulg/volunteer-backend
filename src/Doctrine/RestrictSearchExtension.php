<?php
// api/src/Doctrine/CurrentUserExtension.php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\BaseBundle\Entity\SearchRestrictableInterface;
use App\Entity\{Achievement, Category, Event, ModeratingEvent, Task};
use App\Filter\FilterHelper;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

final class RestrictSearchExtension implements QueryCollectionExtensionInterface
{

    private $filterHelper;
    private $security;

    public function __construct(Security $security, FilterHelper $filterHelper)
    {
        $this->filterHelper = $filterHelper;
        $this->security= $security;
    }

    /**
     * {@inheritdoc}
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null): void
    {
        // instanceof doesn't work for classname
        if(is_a($resourceClass, SearchRestrictableInterface::class, true)){
            /** @var SearchRestrictableInterface|string $resourceClass - type hints doesn't work for classname */
            $resourceClass::restrictSearch($this->security, $queryBuilder, $queryNameGenerator, $this->filterHelper);
        }
        $this->addWhere($queryBuilder, $resourceClass);

    }
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ((Task::class || Event::class || Category::class || ModeratingEvent::class || Achievement::class) === $resourceClass){
            $queryBuilder->andWhere('o.deleted = false');
        }
    }
}
