<?php


namespace App\BaseBundle\Entity;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Filter\FilterHelper;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;


interface SearchRestrictableInterface
{
    public static function restrictSearch(Security $security, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, FilterHelper $filterHelper);
}
