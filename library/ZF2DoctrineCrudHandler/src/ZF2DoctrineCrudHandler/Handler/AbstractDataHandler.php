<?php

namespace ZF2DoctrineCrudHandler\Handler;

/**
 * Abstract Class for Form-Crud-Handlers
 * Offers some configuration options Form-Handlers need
 *
 * @author   Cyberrebell <cyberrebell@web.de>
 */
abstract class AbstractDataHandler extends AbstractCrudHandler
{
    /**
     * @param int $entityId
     * @return array
     */
    protected function getEntityData($entityId)
    {
        $entity = $this->objectManager->getRepository($this->entityNamespace)->find($entityId);
        $entity = $this->filterEntity($entity);
        if ($entity === null || $entity === false) {//check if entity with requested id exists
            return false;
        } else {
            $useBlacklist = (count($this->propertyBlacklist) > 0) ? true : false;
            $useWhitelist = (count($this->propertyWhitelist) > 0) ? true : false;

            $properties = EntityReader::getProperties($this->entityNamespace);

            $dataToDisplay = [];
            foreach ($properties as $property) {
                $name = $property->getName();
                if (//handle black&whitelist
                ($useWhitelist && !in_array($name, $this->propertyWhitelist))
                || ($useBlacklist && in_array($name, $this->propertyBlacklist))
                ) {
                    continue;
                }
                $getter = 'get' . ucfirst($name);
                $value = $entity->$getter();
                
                if (array_key_exists($name, $this->propertiesOutputs)) {//use injected output Closure
                    $function = $this->propertiesOutputs[$name];
                    $viewHelperManager = $this->serviceManager->get('viewhelpermanager');
                    $value = $function($viewHelperManager, $value);
                } else {
                    if ($value === null) {
                        $dataToDisplay[$name] = null;
                        continue;
                    }
                    switch ($property->getType()) {
                    	case Property::PROPERTY_TYPE_COLUMN:
                    	    break;
                    	case Property::PROPERTY_TYPE_TOONE:
                    	    $targetEntity = $property->getTargetEntity();
                    	    $targetPropertsGetter = 'get' . ucfirst($targetEntity::DISPLAY_NAME_PROPERTY);
                    	    $value = $value->$targetPropertsGetter();
                    	    break;
                    	case Property::PROPERTY_TYPE_TOMANY:
                    	    $targetEntity = $property->getTargetEntity();
                    	    $targetPropertsGetter = 'get' . ucfirst($targetEntity::DISPLAY_NAME_PROPERTY);
                    	    $listString = '';
                    	    foreach ($value as $targetEntity) {
                    	        $listString .= $targetEntity->$targetPropertsGetter() . ', ';
                    	    }
                    	    $value = substr($listString, 0, -2);
                    	    break;
                    	default:
                    	    continue 2;
                    }
                }
                $dataToDisplay[$name] = $value;
            }
            return $dataToDisplay;
        }
    }
}
