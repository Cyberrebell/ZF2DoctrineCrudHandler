<?php
/**
 * File containing AbstractDataHandler class
 *
 * PHP version 5
 *
 * @category  ZF2DoctrineCrudHandler
 * @package   ZF2DoctrineCrudHandler\Handler
 * @author    Cyberrebell <cyberrebell@web.de>
 * @copyright 2014 - 2014 Cyberrebell
 * @license   http://www.gnu.org/licenses/gpl-3.0 GPL-3.0
 * @version   GIT: <git_id>
 * @link      https://github.com/Cyberrebell/ZF2DoctrineCrudHandler
 */

namespace ZF2DoctrineCrudHandler\Handler;

use ZF2DoctrineCrudHandler\Reader\EntityReader;
use ZF2DoctrineCrudHandler\Reader\Property;

/**
 * Abstract Class for Form-Crud-Handlers
 * Offers some configuration options Form-Handlers need
 *
 * @category ZF2DoctrineCrudHandler
 * @package  ZF2DoctrineCrudHandler\Handler
 * @author   Cyberrebell <cyberrebell@web.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0 GPL-3.0
 * @link     https://github.com/Cyberrebell/ZF2DoctrineCrudHandler
 */
abstract class AbstractDataHandler extends AbstractCrudHandler
{
    /**
     * 
     * 
     * @param int $entityId
     * 
     * @return array
     */
    protected function getEntityData($entityId)
    {
        $entity = $this->objectManager->getRepository($this->entityNamespace)->find($entityId);
        if ($entity === null) {//check if entity with requested id exists
            $this->render404();
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
                    $value = $function($value);
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
