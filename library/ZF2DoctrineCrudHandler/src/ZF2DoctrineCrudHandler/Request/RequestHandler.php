<?php

namespace ZF2DoctrineCrudHandler\Request;

use Zend\Form\Form;
use Zend\Http\Request;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineEntityReader\EntityReader;
use DoctrineEntityReader\Property;

class RequestHandler
{
	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
	 * @param string $entityNamespace
	 * @param \Zend\Form\Form $form
	 * @param \Zend\Http\Request $request
	 */
	public static function handleAdd(ObjectManager $objectManager, $entityNamespace, Form $form, Request $request) {
		if ($formData = self::getValidData($form, $request)) {
			$properties = EntityReader::getProperties($entityNamespace);
			$entity = new $entityNamespace();
			foreach ($formData as $elementName => $elementData) {
				if (
					$elementName == 'id'
					|| $form->get($elementName)->getAttribute('type') == 'submit'
					|| !array_key_exists($elementName, $properties)
				) {
					continue;
				}
				
				switch ($properties[$elementName]->getType()) {
					case Property::PROPERTY_TYPE_COLUMN:
						$value = $elementData;
						$setter = 'set' . ucfirst($elementName);
						$entity->$setter($value);
						break;
					case Property::PROPERTY_TYPE_REF_ONE:
						$dbResult = $objectManager->getRepository($properties[$elementName]->getTargetEntity())->find($elementData);
						if ($dbResult === null) {
							continue 2;
						}
						$value = $dbResult;
						$setter = 'set' . ucfirst($elementName);
						$entity->$setter($value);
						break;
					case Property::PROPERTY_TYPE_REF_MANY:
						$dbResult = $objectManager->getRepository($properties[$elementName]->getTargetEntity())->findById($elementData);
						if ($dbResult === null) {
							continue 2;
						}
						$adder = 'add' . ucfirst(substr($elementName, 0, -1));
						foreach ($dbResult as $result) {
							$entity->$adder($result);
						}
						break;
					default:
						continue 2;
				}
			}
			$objectManager->persist($entity);
			$objectManager->flush();
			return true;
		}
	}
	
	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
	 * @param string $entityNamespace
	 * @param \Zend\Form\Form $form
	 * @param \Zend\Http\Request $request
	 * @param int $entityId
	 * @param \Closure $filter
	 * @return boolean
	 */
	public static function handleEdit(ObjectManager $objectManager, $entityNamespace, Form $form, Request $request, $entityId, $filter) {
		if ($formData = self::getValidData($form, $request)) {
			$properties = EntityReader::getProperties($entityNamespace);
			$entity = $objectManager->getRepository($entityNamespace)->find($entityId);
			$entity = self::filterEntity($filter, $entity);
			if ($entity === null || $entity === false) {//check if entity with requested id exists
				return false;
			}
			foreach ($formData as $elementName => $elementData) {
				if (
					$elementName == 'id'
					|| $form->get($elementName)->getAttribute('type') == 'submit'
					|| !array_key_exists($elementName, $properties)
				) {
					continue;
				}
				$setter = 'set' . ucfirst($elementName);
				switch ($properties[$elementName]->getType()) {
					case Property::PROPERTY_TYPE_COLUMN:
						$value = $elementData;
						break;
					case Property::PROPERTY_TYPE_REF_ONE:
						$dbResult = $objectManager->getRepository($properties[$elementName]->getTargetEntity())->find($elementData);
						if ($dbResult === null) {
							continue 2;
						}
						$value = $dbResult;
						break;
					case Property::PROPERTY_TYPE_REF_MANY:
						$dbResult = $objectManager->getRepository($properties[$elementName]->getTargetEntity())->findById($elementData);
						if ($dbResult === null) {
							continue 2;
						}
						$value = $dbResult;
						break;
					default:
						continue 2;
				}
				$entity->$setter($value);
			}
			$objectManager->persist($entity);
			$objectManager->flush();
			return true;
		} else {	//no valid post
			$entity = $objectManager->getRepository($entityNamespace)->find($entityId);
			$entity = self::filterEntity($filter, $entity);
			if ($entity === null || $entity === false) {//check if entity with requested id exists
				return false;
			}
			$properties = EntityReader::getProperties($entityNamespace);
			foreach ($form->getElements() as $element) {
				if (
					!($element instanceof \Zend\Form\Element\Password)
					&& !($element instanceof \Zend\Form\Element\Submit)
				) {
					$name = $element->getAttribute('name');
					$getter = 'get' . ucfirst($name);
					$value = $entity->$getter();
					$value = $properties[$name]->ensurePrintableValue($value, true);
					$element->setValue($value);
				}
			}
		}
	}
	
	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
	 * @param string $entityNamespace
	 * @param id $entityId
	 * @param \Closure $filter
	 * @return boolean
	 */
	public static function handleDelete(ObjectManager $objectManager, $entityNamespace,	$entityId, $filter) {
		$entity = $objectManager->getRepository($entityNamespace)->find($entityId);
		if ($entity === null || self::filterEntity($filter, $entity) === false) {
			return false;
		}
		$objectManager->remove($entity);
		$objectManager->flush();
		return true;
	}
	
	/**
	 * @param \Zend\Form\Form $form
	 * @param \Zend\Http\Request $request
	 * @return array
	 */
	protected static function getValidData(Form $form, Request $request) {
		if ($request->isPost()) {
			$post = $request->getPost();
			$form->setData($post);
			if ($form->isValid()) {
				return $form->getData();
			}
		}
		return false;
	}
	
	/**
	 * Use given entityFilter to check if entity is allowed
	 * 
	 * @param object $entity Doctrine-Entity
	 * 
	 * @return object|boolean
	 */
	protected static function filterEntity($filter, $entity) {
		if ($filter !== null && $filter($entity) === false) {
			return false;
		} else {
			return $entity;
		}
	}
}
