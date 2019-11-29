<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 10.07.18
 * Time: 17:12
 */


namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

final class AggregateFilter extends AbstractWebantFilter
{
    private $security;
    private $em;

    public function __construct(ManagerRegistry $managerRegistry, ?RequestStack $requestStack = null, LoggerInterface $logger = null, array $properties = null, Security $security = null, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->logger = $logger;
        $this->em = $em;

        parent::__construct($managerRegistry, $requestStack, $logger, $properties);
    }


    protected $operators = [
        'eq'  => ' =',
        'lt'  => '< ',
        'lte' => '<=',
        'gt'  => '> ',
        'gte' => '>=',
    ];

    public function toSearchKey($func, $property){
        return sprintf('%s(%s)',$func, $property);
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null, array $context = [])
    {
        $filters = $this->getFilters($context);
        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->addGroupBy(sprintf('%s.id', $alias));

        foreach ($this->properties as $property => $funcs) {
            foreach ($funcs as $func => $alias){
                $values = $filters[$alias] ?? null;
                foreach ($this->operators as $op => $sign){
                    $value = $values[$op] ?? null;
                    if(isset($value)){
                        $this->addOpFilter($func, $property, $sign, $value, $queryBuilder, $queryNameGenerator, $resourceClass, null, Join::LEFT_JOIN);
                    }
                }

                $order = $filters['order'][$alias] ?? '';
                if(in_array(strtolower($order), ['asc', 'desc'])){
                    $this->addOrderBy($func, $property, $order, $queryBuilder, $queryNameGenerator, $resourceClass, null, Join::LEFT_JOIN);
                }
            }
        }

//        dump($queryBuilder->getDQL());
    }




    /**
     * @param string $property
     * @param $operator
     * @param $value
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param Composite|null $composite
     * @param string $joinType
     */
    protected function addOpFilter($func,
                                   $property,
                                   $operator,
                                   $value,
                                   QueryBuilder $queryBuilder,
                                   QueryNameGeneratorInterface $queryNameGenerator,
                                   string $resourceClass,
                                   ?Composite $composite = null,
                                   $joinType = Join::LEFT_JOIN)
    {
        if(!$this->isPropertyMapped($property, $resourceClass, true)){
            return;
        }

        [$fieldName, $valueParameter] = $this->prepareFuncPropertyFilter($func, $property, $queryBuilder, $queryNameGenerator, $resourceClass, $joinType);

        $dql = sprintf('%s %s :%s', $fieldName, $operator, $valueParameter);
//        $dql = sprintf('%s > 0', $fieldName);

        $queryBuilder->setParameter($valueParameter, $value);
        $queryBuilder->andHaving($dql);
    }

    /**
     * @param string $property
     * @param $operator
     * @param $value
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param Composite|null $composite
     * @param string $joinType
     */
    protected function addOrderBy($func,
                                   $property,
                                   $value,
                                   QueryBuilder $queryBuilder,
                                   QueryNameGeneratorInterface $queryNameGenerator,
                                   string $resourceClass,
                                   ?Composite $composite = null,
                                   $joinType = Join::LEFT_JOIN)
    {
        if(!$this->isPropertyMapped($property, $resourceClass, true)){
            return;
        }

        [$fieldName] = $this->prepareFuncPropertyFilter($func, $property, $queryBuilder, $queryNameGenerator, $resourceClass, $joinType);

        $queryBuilder->addOrderBy($fieldName, $value);
    }





    /**
     *
     * subselect version
     * switch to plain func version when doctrine issue is solved
     *
     * @param $func
     * @param $property
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param null $joinType
     * @return array
     */
    protected function prepareFuncPropertyFilter($func,
                                                 $property,
                                                 QueryBuilder $queryBuilder,
                                                 QueryNameGeneratorInterface $queryNameGenerator,
                                                 string $resourceClass,
                                                 $joinType = null){

        list($alias, $field, $valueParameter, $associations) = $this->preparePropertyFilter($property, $queryBuilder, $queryNameGenerator, $resourceClass, $joinType);
        // item_id_count
        $subAlias = sprintf('%s_by_%s_%s', $alias, $func, $field);
        $fieldName = sprintf('%s_%s_%s', $func, $alias, $field);

        // find existing subselect
        $regexp = sprintf('/as\s+(HIDDEN)?\s*%s$/', preg_quote($fieldName));
        foreach($queryBuilder->getDQLPart('select') as $select){
            if($select instanceof Select){
                $parts = $select->getParts();
            } else {
                $parts = [$select];
            }
            foreach ($parts as $part){
                if(preg_match($regexp, $part)){
                    return [$fieldName, $valueParameter];
                }
            }
        }

        // prev alias - find aggregated objects by that value
        $propertyParts = explode('.', $property);
        $prevAlias = $queryBuilder->getRootAliases()[0];

        if(count($propertyParts) > 1){
            $propertyParts = array_slice($propertyParts, 0, -1);
            [$prevAlias] = $this->preparePropertyFilter(implode('.', $propertyParts), $queryBuilder, $queryNameGenerator, $resourceClass, $joinType);
        }

        // find mappedBy - find aggregated objects by that field
        $metadata = $this->getClassMetadata($resourceClass);

        foreach ($associations as $association) {
            if ($metadata->hasAssociation($association)) {
                $associationClass = $metadata->getAssociationTargetClass($association);
                $mappedBy = $metadata->getAssociationMappedByTargetField($association);

                $metadata = $this->getClassMetadata($associationClass);
//                dump(compact('associationClass', 'mappedBy'));
            }
        }

        // create subselect
        $sub = $this->em->createQueryBuilder();
        $sub->select(sprintf('%s(%s.%s)', $func, $subAlias, $field));
        $sub->from($metadata->getName(), $subAlias);
        $sub->andWhere(sprintf('%s.%s = %s', $subAlias, $mappedBy, $prevAlias));

        $queryBuilder->addSelect(sprintf('(%s) as HIDDEN %s', $sub->getDQL(), $fieldName));

        return [$fieldName, $valueParameter];
    }


    /**
     *
     * plain func version
     * waiting for issue https://github.com/doctrine/orm/issues/7775
     *
     * @param $func
     * @param $property
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param null $joinType
     * @return array
     */
    protected function prepareFuncPropertyFilter0($func,
                                                 $property,
                                                 QueryBuilder $queryBuilder,
                                                 QueryNameGeneratorInterface $queryNameGenerator,
                                                 string $resourceClass,
                                                 $joinType = null){
        list($alias, $field, $valueParameter) = $this->preparePropertyFilter($property, $queryBuilder, $queryNameGenerator, $resourceClass, $joinType);

        $foundSelect = null;

        $paramExpr = sprintf('%s(%s.%s)', $func, $alias, $field);
        $regexp = sprintf('/%s\ as\s+(HIDDEN)?\s*(?P<fieldName>\w*)/', preg_quote($paramExpr));

        foreach($queryBuilder->getDQLPart('select') as $select){
            if($select instanceof Select){
                $parts = $select->getParts();
            } else {
                $parts = [$select];
            }
            foreach ($parts as $part){
                if(preg_match($regexp, $part, $matches)){
                    $fieldName = $matches['fieldName'];
                    return [$fieldName, $valueParameter, $paramExpr];
                }
            }
        }

        $fieldName = $queryNameGenerator->generateParameterName(sprintf('%s_%s', $func, $field));
        $queryBuilder->addSelect(sprintf('%s(%s.%s) as HIDDEN %s', $func, $alias, $field, $fieldName));

        return [$fieldName, $valueParameter, $paramExpr];
    }



    /**
     * Gets the description of this filter for the given resource.
     *
     * Returns an array with the filter parameter names as keys and array with the following data as values:
     *   - property: the property where the filter is applied
     *   - type: the type of the filter
     *   - required: if this filter is required
     *   - strategy: the used strategy
     *   - is_collection (optional): is this filter is collection
     *   - swagger (optional): additional parameters for the path operation,
     *     e.g. 'swagger' => [
     *       'description' => 'My Description',
     *       'name' => 'My Name',
     *       'type' => 'integer',
     *     ]
     *   - openapi (optional): additional parameters for the path operation in the version 3 spec,
     *     e.g. 'openapi' => [
     *       'description' => 'My Description',
     *       'name' => 'My Name',
     *       'schema' => [
     *          'type' => 'integer',
     *       ]
     *     ]
     * The description can contain additional data specific to a filter.
     *
     * @see \ApiPlatform\Core\Swagger\Serializer\DocumentationNormalizer::getFiltersParameters
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [];
        $properties = $this->getProperties();
        if(!is_array($properties)){
            return [];
        }

        foreach ($properties as $property => $funcs){
            foreach ($funcs as $func => $alias){
                $description += $this->getPropertyDescription($property, $func, $alias);
            }
        }
        return $description;
    }

    /**
     * Gets filter description.
     */
    protected function getPropertyDescription(string $fieldName, string $func, $alias): array
    {
        $description = [];
        foreach ($this->operators as $op => $_){
            $description += $this->getFilterDescription($alias, $op);
        }

        $description += $this->getOrderDescription($alias);

        return $description;
    }


    /**
     * Gets filter description.
     */
    protected function getFilterDescription(string $alias, string $operator): array
    {
        return [
            sprintf('%s[%s]', $alias, $operator) => [
                'property' => $alias,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }

    /**
     * Gets filter description.
     */
    protected function getOrderDescription(string $property, $orderParameterName = 'order'): array
    {
        $enum = ['asc', 'desc'];
        return [
            sprintf('%s[%s]', $orderParameterName, $property) => [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => ['enum' => $enum],
                'openapi' => ['enum' => $enum],
            ],
        ];
    }






}
