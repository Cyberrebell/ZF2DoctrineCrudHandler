<?php

namespace ZF2DoctrineCrudHandler\Request;

use ZF2DoctrineCrudHandler\Reader\EntityReader;
use ZF2DoctrineCrudHandler\Reader\Property;

class RequestHandler
{
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param string $entityNamespace
     * @param \Zend\Form\Form $form
     * @param \Zend\Http\Request $request
     */
    static function handleAdd(\Doctrine\Common\Persistence\ObjectManager $objectManager, $entityNamespace, \Zend\Form\Form $form, \Zend\Http\Request $request) {
        if ($formData = self::getValidData($form, $request)) {
            $properties = EntityReader::getProperties($entityNamespace);
            $entity = new $entityNamespace();
            foreach ($formData as $elementName => $elementData) {
                if ($elementName == 'id' || $form->get($elementName)->getAttribute('type') == 'submit' || !array_key_exists($elementName, $properties)) {
                    continue;
                }
                $setter = 'set' . ucfirst($elementName);
                switch ($properties[$elementName]->getType()) {
                	case Property::PROPERTY_TYPE_COLUMN:
                	    $value = $elementData;
                	    break;
                	case Property::PROPERTY_TYPE_TOONE:
                	    $dbResult = $objectManager->getRepository($properties[$elementName]->getTargetEntity())->find($elementData);
                	    if ($dbResult === NULL) {
                	        continue 2;
                	    }
                	    $value = $dbResult;
                	    break;
                	case Property::PROPERTY_TYPE_TOMANY:
                	    $dbResult = $objectManager->getRepository($properties[$elementName]->getTargetEntity())->findById($elementData);
                	    if ($dbResult === NULL) {
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
        }
    }
    
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param string $entityNamespace
     * @param \Zend\Form\Form $form
     * @param \Zend\Http\Request $request
     * @param int $entityId
     * @return boolean
     */
    static function handleEdit(\Doctrine\Common\Persistence\ObjectManager $objectManager, $entityNamespace, \Zend\Form\Form $form, \Zend\Http\Request $request, $entityId) {
        if ($formData = self::getValidData($form, $request)) {
            $properties = EntityReader::getProperties($entityNamespace);
            $entity = $objectManager->getRepository($entityNamespace)->find($entityId);
            if ($entity === NULL) {
                return false;
            }
            foreach ($formData as $elementName => $elementData) {
                if ($elementName == 'id' || $form->get($elementName)->getAttribute('type') == 'submit' || !array_key_exists($elementName, $properties)) {
                    continue;
                }
                $setter = 'set' . ucfirst($elementName);
                switch ($properties[$elementName]->getType()) {
                	case Property::PROPERTY_TYPE_COLUMN:
                	    $value = $elementData;
                	    break;
                	case Property::PROPERTY_TYPE_TOONE:
                	    $dbResult = $objectManager->getRepository($properties[$elementName]->getTargetEntity())->find($elementData);
                	    if ($dbResult === NULL) {
                	        continue 2;
                	    }
                	    $value = $dbResult;
                	    break;
                	case Property::PROPERTY_TYPE_TOMANY:
                	    $dbResult = $objectManager->getRepository($properties[$elementName]->getTargetEntity())->findById($elementData);
                	    if ($dbResult === NULL) {
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
        } else {    //no valid post
            $entity = $objectManager->getRepository($entityNamespace)->find($entityId);
            if ($entity === NULL) {
                return false;
            }
            foreach ($form->getElements() as $element) {
                if (!($element instanceof \Zend\Form\Element\Password) && !($element instanceof \Zend\Form\Element\Submit)) {
                    $name = $element->getAttribute('name');
                    $getter = 'get' . ucfirst($name);
                    $element->setValue($entity->$getter());
                }
            }
        }
    }
    
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param string $entityNamespace
     * @param id $entityId
     * @return boolean
     */
    static function handleDelete(\Doctrine\Common\Persistence\ObjectManager $objectManager, $entityNamespace, $entityId) {
        $entity = $objectManager->getRepository($entityNamespace)->find($entityId);
        if ($entity === NULL) {
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
    protected static function getValidData(\Zend\Form\Form $form, \Zend\Http\Request $request) {
        if ($request->isPost()) {
            $post = $request->getPost();
            $form->setData($post);
            if ($form->isValid()) {
                return $form->getData();
            }
        }
        return false;
    }
}
