<?php

namespace ZF2DoctrineCrudHandler\Reader;

use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Creates profiles about entity Properties by reading the doctrine annotations
 * @author Cyberrebell
 */
class EntityReader
{
    /**
     * @param string $entityNamespace
     * @return array:\ZF2DoctrineCrudHandler\Annotation\Property
     */
    static function getProperties($entityNamespace) {
        $reflectionClass = new \ReflectionClass($entityNamespace);
        $reflectionProperties = $reflectionClass->getProperties();
        
        $properties = [];
        foreach ($reflectionProperties as $reflectionProperty) {
            $properties[] = $this->createProperty($reflectionProperty);
        }
        return $properties;
    }
    
    /**
     * @param \ReflectionProperty $reflectionProperty
     * @throws \Exception
     * @return \ZF2DoctrineCrudHandler\Annotation\Property
     */
    static protected function createProperty(\ReflectionProperty $reflectionProperty) {
        $property = new Property();
        $property->setName($reflectionProperty->getName());
        
        $annotationReader = new AnnotationReader();
        $annotations = $annotationReader->getPropertyAnnotations($reflectionProperty);
        foreach ($annotations as $annotation) {
            $annotationClassName = get_class($annotation);
            if ($annotationClassName == 'Doctrine\ORM\Mapping\Column') {
                $property->setAnnotation($annotation);
                $property->setType(Property::PROPERTY_TYPE_COLUMN);
            } else if ($annotationClassName == 'Doctrine\ORM\Mapping\ManyToOne' || $annotationClassName == 'Doctrine\ORM\Mapping\OneToOne') {
                $property->setAnnotation($annotation);
                $property->setType(Property::PROPERTY_TYPE_TOONE);
                $property->setTargetEntity($annotation->targetEntity);
            } else if ($annotationClassName == 'Doctrine\ORM\Mapping\ManyToMany' || $annotationClassName == 'Doctrine\ORM\Mapping\OneToMany') {
                $property->setAnnotation($annotation);
                $property->setType(Property::PROPERTY_TYPE_TOMANY);
                $property->setTargetEntity($annotation->targetEntity);
            }
        }
        
        if ($property->getType() == -1) {
            throw new \Exception('Entity "' . $reflectionProperty->getDeclaringClass()->getName() . '": defining annotation is missing at property "' . $property->getName() . '"!');
        }
        
        return $property;
    }
}
