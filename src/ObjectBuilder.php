<?php

namespace Pixelcone\Fraction;

use Pixelcone\Fraction\Exceptions\MethodMissingException;
use Pixelcone\Fraction\Exceptions\RequiredAttributeMissingException;
use ReflectionClass;
use ReflectionException;

class ObjectBuilder
{
    private ReflectionClass $reflection;

    public function __construct (
        private string $className,
    )
    {
        $this->reflection = new ReflectionClass($this->className);
    }

    public function build (mixed $data = []): object
    {
        if (!$data) {
            return $this->reflection->newInstance();
        }

        /**
         * A non-array value should be converted into a single-item indexed array;
         * An associative array should be mapped with constructor parameters;
         * An indexed array should be wrapped into an array only in specific conditions;
         */

        if (!is_array($data)) {
            $data = [$data];
        } else if ($this->dataFitsIntoConstructorParams($data)) {
            $data = $this->mapDataWithConstructorParams($data);
        } else if ($this->dataShouldBeWrappedIntoArray()) {
            $data = [$data];
        }

        return $this->reflection->newInstanceArgs($data);
    }

    public function run (mixed $data = [], string $method = 'handle'): mixed
    {
        $this->ensureReflectionHasMethod($method);

        $object = $this->build($data);
        $args = [];

        // If running within a Laravel app, auto-inject method dependencies
        if (class_exists('Illuminate\Support\Facades\App')) {
            $args = $this->buildMethodDependencies($method);
        }

        return call_user_func([$object, $method], ...$args);
    }

    private function buildMethodDependencies(string $method): array
    {
        $app    = app();
        $args   = [];
        $params = $this->reflection->getMethod($method)->getParameters();

        foreach ($params as $param) {
            $args[] = $app->make((string)$param->getType());
        }

        return $args;
    }

    private function dataFitsIntoConstructorParams (array $data): bool
    {
        return !array_is_list($data) && $this->dataEqualsToConstructorParams($data);
    }

    private function dataEqualsToConstructorParams (array $data): bool
    {
        /**
         * Get attribute names from both data array and constructor parameters
         */
        $params            = array_keys($data);
        $constructorParams = $this->getConstructorParams();

        /**
         * Sort both arrays in even way
         */
        sort($params);
        sort($constructorParams);

        /**
         * Check both arrays for equity
         */
        return !array_diff($params, $constructorParams);
    }

    private function getConstructorParams (): array
    {
        $this->ensureReflectionHasMethod('__construct');

        $constructor = $this->reflection->getConstructor();
        $parameters  = $constructor->getParameters();

        return array_map(function ($param) {
            return $param->getName();
        }, $parameters);
    }

    private function mapDataWithConstructorParams (array $data): array
    {
        return $this->mapDataWithMethodParams('__construct', $data);
    }

    private function mapDataWithMethodParams (string $method, array $data): array
    {
        $this->ensureReflectionHasMethod($method);

        $constructorParams = $this->reflection->getMethod($method)->getParameters();

        return array_map(function ($param) use ($data, $method) {
            return $this->getParamValueOrDefault($param, $data, $method);
        }, $constructorParams);
    }

    private function getParamValueOrDefault($param, $data, $method): mixed
    {
        try {
            return array_key_exists($param->getName(), $data)
                ? $data[$param->getName()]
                : $param->getDefaultValue();
        } catch (ReflectionException) {
            throw new RequiredAttributeMissingException(
                'Property ' . __CLASS__ . '::' . $param->getName()
                . " has no corresponding key in the data passed into $method() method"
            );
        }
    }

    private function dataShouldBeWrappedIntoArray (): bool
    {
        $this->ensureReflectionHasMethod('__construct');

        /**
         * Return True only if:
         *  - a constructor has only one parameter
         *  - the constructor's parameter type is an array
         */

        $constructor    = $this->reflection->getConstructor();
        $numberOfParams = $constructor->getNumberOfParameters();

        if (!$numberOfParams || $numberOfParams > 1) {
            return false;
        }

        $constructorParams = $constructor->getParameters();
        $firstParamType    = $constructorParams[0]->getType()->getName();

        if ($firstParamType !== 'array') {
            return false;
        }

        return true;
    }

    private function ensureReflectionHasMethod (string $method): void
    {
        if (!$this->reflection->hasMethod($method)) {
            throw new MethodMissingException(
                __CLASS__ . " class has no mandatory $method() method"
            );
        }
    }
}