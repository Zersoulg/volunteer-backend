<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 10.07.18
 * Time: 17:12
 */


namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Common\PropertyHelperTrait;
use ApiPlatform\Core\Bridge\Doctrine\Orm\PropertyHelperTrait as OrmPropertyHelperTrait;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;


class FilterHelper
{
    use AbstractWebantFilterTrait{
        preparePropertyFilter as public;
        filterFields as public;
        filterProperty as public;
    }


    protected $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

}
