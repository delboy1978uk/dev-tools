<?php

declare(strict_types=1);

namespace Bone\DevTools;

trait ReflectionInvoker
{
    public function invokeMethod(&$object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new \ReflectionClass(get_class($object));

        return $reflection->getMethod($methodName)->invokeArgs($object, $parameters);
    }

    public function setPrivateProperty(&$object, string $propertyName, $value): void
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property   = $reflection->getProperty($propertyName);
        $property->setValue($object, $value);
    }

    public function getPrivateProperty(&$object, string $propertyName): mixed
    {
        $reflection = new \ReflectionClass(get_class($object));

        return $reflection->getProperty($propertyName)->getValue($object);
    }
}

