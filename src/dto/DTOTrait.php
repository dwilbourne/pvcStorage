<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\storage\dto;

use pvc\storage\err\DTOExtraPropertyException;
use pvc\storage\err\DTOInvalidPropertyValueException;
use pvc\storage\err\DTOMissingPropertyException;

use ReflectionClass;
use ReflectionProperty;
use Throwable;

/**
 * Class DTOTrait
 */
trait DTOTrait
{
    private readonly bool $extraPropertiesPermitted;

    public function permitExtraProperties(): void
    {
        $this->extraPropertiesPermitted = true;
    }

    public function extraPropertiesPermitted(): bool
    {
        return $this->extraPropertiesPermitted ?? false;
    }

    /**
     * @param array<string, mixed> $constructorProperties
     * @throws DTOExtraPropertyException
     * @throws DTOMissingPropertyException
     * @throws DTOInvalidPropertyValueException
     */
    public function hydrate(array $constructorProperties): void
    {
        $reflection = new ReflectionClass(static::class);

        $className = $reflection->getName();

        $requiredProperties = array_map(
            function (ReflectionProperty $value): string {
                return $value->getName();
            },
            $reflection->getProperties(ReflectionProperty::IS_PUBLIC)
        );

        $missingProperties = array_diff($requiredProperties, array_keys($constructorProperties));
        if ($missingProperties) {
            throw new DTOMissingPropertyException(implode(',', $missingProperties), $className);
        }

        $extraProperties = array_diff(array_keys($constructorProperties), $requiredProperties);
        if ($extraProperties && !$this->extraPropertiesPermitted()) {
            throw new DTOExtraPropertyException(implode(',', $extraProperties), $className);
        }

        foreach ($constructorProperties as $propertyName => $propertyValue) {
            if (in_array($propertyName, $requiredProperties)) {
                try {
                    $this->{$propertyName} = $propertyValue;
                } catch (Throwable $e) {
                    throw new DTOInvalidPropertyValueException($propertyName, $propertyValue, $className);
                }
            }
        }
    }

    /**
     * toArray
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return (array) $this;
    }
}
