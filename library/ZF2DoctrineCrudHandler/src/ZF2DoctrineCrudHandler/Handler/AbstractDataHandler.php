<?php

namespace ZF2DoctrineCrudHandler\Handler;

use DoctrineEntityReader\EntityReader;
use DoctrineEntityReader\Property;

/**
 * Abstract Class for Form-Crud-Handlers
 * Offers some configuration options Form-Handlers need
 *
 * @author   Cyberrebell <chainsaw75@web.de>
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
				if (($useWhitelist && !in_array($name, $this->propertyWhitelist)) || ($useBlacklist && in_array($name, $this->propertyBlacklist))) {
					continue;	//handle black&whitelist
				}
				$getter = 'get' . ucfirst($name);
				$bootgridGetter = 'bootgrid' . ucfirst($getter);
				if (method_exists($entity, $bootgridGetter)) {
					$value = $entity->$bootgridGetter();
				} else {
					$value = $entity->$getter();
				}
				switch ($property->getType()) {
					case Property::PROPERTY_TYPE_COLUMN:
						if ($value instanceof \DateTime) {
							$value = $value->format('d.M.Y H:m:s');
						}
						break;
					case Property::PROPERTY_TYPE_REF_ONE:
						if ($value) {
							$targetEntity = $property->getTargetEntity();
							$targetPropertsGetter = 'get' . ucfirst($targetEntity::DISPLAY_NAME_PROPERTY);
							$value = $value->$targetPropertsGetter();
						}
						break;
					case Property::PROPERTY_TYPE_REF_MANY:
						$targetEntity = $property->getTargetEntity();
						$targetPropertsGetter = 'get' . ucfirst($targetEntity::DISPLAY_NAME_PROPERTY);
						$listString = '';
						foreach ($value as $targetEntity) {
							$listString .= $targetEntity->$targetPropertsGetter() . ', ';
						}
						$value = substr($listString, 0, -2);
						break;
					default:
						break;
				}
				$dataToDisplay[$name] = $value;
			}
			return $dataToDisplay;
		}
	}
}
