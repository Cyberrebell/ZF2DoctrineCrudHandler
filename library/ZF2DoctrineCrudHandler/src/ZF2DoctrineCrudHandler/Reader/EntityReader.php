<?php
/**
 * File containing EntityReader class
 *
 * PHP version 5
 *
 * @category  ZF2DoctrineCrudHandler
 * @package   ZF2DoctrineCrudHandler\Reader
 * @author    Cyberrebell <cyberrebell@web.de>
 * @copyright 2014 - 2014 Cyberrebell
 * @license   http://www.gnu.org/licenses/gpl-3.0 GPL-3.0
 * @version   GIT: <git_id>
 * @link      https://github.com/Cyberrebell/ZF2DoctrineCrudHandler
 */

namespace ZF2DoctrineCrudHandler\Reader;

use Doctrine\Common\Annotations\AnnotationReader;

/**
 * Creates profiles about entity Properties by reading the doctrine annotations
 *
 * @category ZF2DoctrineCrudHandler
 * @package  ZF2DoctrineCrudHandler\Reader
 * @author   Cyberrebell <cyberrebell@web.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0 GPL-3.0
 * @link     https://github.com/Cyberrebell/ZF2DoctrineCrudHandler
 */
class EntityReader
{
    /**
     * Returns Properties determined by Entity-Namespace
     * 
     * @param string $entityNamespace Entity-Namespace
     * 
     * @return array:\ZF2DoctrineCrudHandler\Annotation\Property
     */
    public static function getProperties($entityNamespace)
    {
        $reflectionClass = new \ReflectionClass($entityNamespace);
        $reflectionProperties = $reflectionClass->getProperties();
        
        $properties = [];
        foreach ($reflectionProperties as $reflectionProperty) {
            $property = self::createProperty($reflectionProperty);
            if ($property) {
                $properties[$property->getName()] = $property;
            }
        }
        return $properties;
    }
    
    /**
     * Returns created Property-Object
     * gets Information from Reflection-Property which contains Doctrine-Annotations
     * 
     * @param \ReflectionProperty $reflectionProperty Reflection-Property of Entity
     * 
     * @throws \Exception
     * @return boolean|\ZF2DoctrineCrudHandler\Reader\Property
     */
    protected static function createProperty(\ReflectionProperty $reflectionProperty)
    {
        $property = new Property();
        $property->setName($reflectionProperty->getName());
        
        $annotationReader = new AnnotationReader();
        $annotations = $annotationReader->getPropertyAnnotations($reflectionProperty);
        foreach ($annotations as $annotation) {
            $annotationClassName = get_class($annotation);
            if ($annotationClassName == 'Doctrine\ORM\Mapping\Id') {
                return false;
            } elseif ($annotationClassName == 'Doctrine\ORM\Mapping\Column') {
                $property->setAnnotation($annotation);
                $property->setType(Property::PROPERTY_TYPE_COLUMN);
            } elseif ($annotationClassName == 'Doctrine\ORM\Mapping\ManyToOne'
                || $annotationClassName == 'Doctrine\ORM\Mapping\OneToOne'
            ) {
                $property->setAnnotation($annotation);
                $property->setType(Property::PROPERTY_TYPE_TOONE);
                $property->setTargetEntity($annotation->targetEntity);
            } elseif ($annotationClassName == 'Doctrine\ORM\Mapping\ManyToMany'
                || $annotationClassName == 'Doctrine\ORM\Mapping\OneToMany'
            ) {
                $property->setAnnotation($annotation);
                $property->setType(Property::PROPERTY_TYPE_TOMANY);
                $property->setTargetEntity($annotation->targetEntity);
            }
        }
        
        if ($property->getType() == -1) {
            throw new \Exception(
                'Entity "' . $reflectionProperty->getDeclaringClass()->getName()
                . '": defining annotation is missing at property "' . $property->getName() . '"!'
            );
        }
        
        return $property;
    }
}
