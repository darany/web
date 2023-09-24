<?php

namespace App\Filter;

use \DateTime;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;


final class DateFilter extends AbstractFilter
{

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        // Otherwise filter is applied to order and page as well
        if (
            !$this->isPropertyEnabled($property, $resourceClass) ||
            !$this->isPropertyMapped($property, $resourceClass)
        ) {
            return;
        }
        // Generate a unique parameter name to avoid collisions with other filters
        $dateMin = $queryNameGenerator->generateParameterName($property);
        $dateMax = $queryNameGenerator->generateParameterName($property);
        $valueDate = DateTime::createFromFormat('m-d-Y', $value);
        $queryBuilder
            ->andWhere(sprintf('%s BETWEEN :%s AND :%s', $property, $dateMin, $dateMax))
            ->setParameters(
                [
                    $dateMin => $valueDate->format('Y-m-d 00:00:00'),
                    $dateMax => $valueDate->format('Y-m-d 23:59:59'),
                ]
             );
    }
    
    // This function is only used to hook in documentation generators (supported by Swagger and Hydra)
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }
        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description["$property"] = [
                'property' => $property,
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Filter on date for a custom state provider',
                'openapi' => [
                    'example' => '2023-09-07',
                    'allowReserved' => false, // if true, query parameters will be not percent-encoded
                    'allowEmptyValue' => true,
                    'explode' => false, // to be true, the type must be Type::BUILTIN_TYPE_ARRAY, ?product=blue,green will be ?product=blue&product=green
                ],
            ];
        }
        return $description;
    }
}
