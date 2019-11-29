<?php


namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;


abstract class AbstractWebantFilter extends AbstractContextAwareFilter
{
    use AbstractWebantFilterTrait;

    protected function getFilters(array $context = []){
        $filters = $context['filters'] ?? [];
        // apiplatform is migrating from getting filters from requestStack to getting filters from context key
        // currently(v2.4) it supports both ways so I support both too
        if(empty($filters) && $this->requestStack){
            $request = $this->requestStack->getCurrentRequest();
            $filters = $request->query->all();
        }
        return $filters;
    }

}
