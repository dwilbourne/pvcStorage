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
 *
 * a Data Transfer Object (DTO) is a small step up from an associative array, maybe best thought of as an array whose
 * values have prescribed data types.  Notably, the properties that a DTO contemplates hydrating from an array must
 * be public.  There should not be any logic in the DTO, and by convention they should not have setters and getters
 * (for the public properties).
 *
 * usage:  using this trait in any DTO, you can hydrate the DTO from an array and dehydrate the DTO to an array.
 * It is possible that you want to hydrate the DTO with some, but not all, of the fields in the array.  If the array
 * is going to contain extra fields which are not necessary to hydrate the DTO, call the permitExtraProperties method
 * before hydrating the DTO.
 *
 * By contrast, if the array does not contain all the fields necessary to hydrate the DTO completely, the
 * hydrateFromArray method will throw an exception. All public properties in the DTO are required.
 */
trait DTOTrait
{
    /**
     * @var bool
     */
    private readonly bool $extraPropertiesPermitted;

    /**
     * permitExtraProperties
     */
    public function permitExtraProperties(): void
    {
        $this->extraPropertiesPermitted = true;
    }

    /**
     * extraPropertiesPermitted
     * @return bool
     */
    public function extraPropertiesPermitted(): bool
    {
        return $this->extraPropertiesPermitted ?? false;
    }

    /**
     * hydrateFromArray
     * @param array $values
     * @param array $propertyMap
     * @throws DTOExtraPropertyException
     * @throws DTOInvalidPropertyValueException
     * @throws DTOMissingPropertyException
     *
     * reflects the public properties from the object and hydrates them from the array.  If the propertyMap array
     * is not empty, then use it to translate array keys to DTO property names.  Entries in the property map
     * array should be of the form $dtoPropertyName => $arrayKeyName.  If there is a key in the propertyMap array
     * which is the same as the DTO property being hydrated, the code will look for a key equal to $arrayKeyName
     * (instead of $dtoPropertyName) in the array.
     */
    public function hydrateFromArray(array $values, array $propertyMap = []): void
    {
        $reflection = new ReflectionClass(static::class);
        $className = $reflection->getName();

        /**
         * get the names of all the public properties in the DTO
         */
        $dtoRequiredProperties = array_map(
            function (ReflectionProperty $value): string {
                return $value->getName();
            },
            $reflection->getProperties(ReflectionProperty::IS_PUBLIC),
        );

        $missingProperties = array_diff($dtoRequiredProperties, array_keys($values));
        if ($missingProperties) {
            throw new DTOMissingPropertyException(implode(',', $missingProperties), $className);
        }

        /**
         * check to see if the array contains extra property names besides those that are required by the DTO
         */
        $extraProperties = array_diff(array_keys($values), $dtoRequiredProperties);
        if ($extraProperties && !$this->extraPropertiesPermitted()) {
            throw new DTOExtraPropertyException(implode(',', $extraProperties), $className);
        }

        foreach ($dtoRequiredProperties as $dtoPropertyName) {
            $arrayKey = $propertyMap[$dtoPropertyName] ?? $dtoPropertyName;
            $dtoPropertyValue = $values[$arrayKey];
            try {
                $this->$dtoPropertyName = $dtoPropertyValue;
            } catch (Throwable $e) {
                throw new DTOInvalidPropertyValueException($dtoPropertyName, $dtoPropertyValue, $className);
            }
        }
    }

    /**
     * toArray
     * @return array<string, mixed>
     */
    public function toArray(array $propertymap = []): array
    {
        $array = (array) $this;
        foreach ($array as $oldKey => $value) {
            $newKey = $propertyMap[$oldKey] ?? null;
            if ($newKey) {
                $array[$newKey] = $value;
                unset($array[$oldKey]);
            }
        }
        return $array;
    }
}
