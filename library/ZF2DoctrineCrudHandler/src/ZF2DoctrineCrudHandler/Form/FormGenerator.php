<?php

namespace ZF2DoctrineCrudHandler\Form;

use ZF2DoctrineCrudHandler\Reader\EntityReader;
use ZF2DoctrineCrudHandler\Reader\Property;
use Zend\Form\Form;
use Zend\Form\Element\Submit;
use Zend\Form\Element\DateTime;
use Zend\Form\Element\Date;
use Zend\Form\Element\Time;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Textarea;
use Zend\Form\Element\Text;
use Zend\Form\Element\Email;
use Zend\Form\Element\Password;
use Zend\Form\Element\Select;
use ZF2DoctrineCrudHandler;

/**
 * Generates Form
 * @author Cyberrebell
 */
class FormGenerator
{
    const TO_ONE_ELEMENT_SELECT = 0;
    const TO_ONE_ELEMENT_RADIO = 1;
    
    const TO_MANY_ELEMENT_MULTISELECT = 2;
    const TO_MANY_ELEMENT_MULTICHECK = 3;
    
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;
    /**
     * @var \Zend\Cache\Storage\Adapter\AbstractAdapter
     */
    protected $storageAdapter;
    protected $entityNamespace;
    
    protected $propertyBlacklist = [];
    protected $propertyWhitelist = [];
    protected $emailProperties = [];
    protected $passwordProperties = [];
    
    protected $toOneElement = self::TO_ONE_ELEMENT_SELECT;  //select / radio
    protected $toManyElement = self::TO_MANY_ELEMENT_MULTISELECT;   //multiselect / multicheck
    
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param string $entityNamespace
     * @param \Zend\Cache\Storage\Adapter\AbstractAdapter $storageAdapter
     */
    function __construct(\Doctrine\Common\Persistence\ObjectManager $objectManager, $entityNamespace, \Zend\Cache\Storage\Adapter\AbstractAdapter $storageAdapter){
        $this->objectManager = $objectManager;
        $this->entityNamespace = $entityNamespace;
        $this->storageAdapter = $storageAdapter;
    }
    
    function setPropertyBlacklist(array $blacklist) {
        $this->propertyBlacklist = $blacklist;
        
        return $this;
    }
    
    function setPropertyWhitelist(array $whitelist) {
        $this->propertyWhitelist = $whitelist;
        
        return $this;
    }
    
    function setEmailProperties(array $emailProperties) {
        $this->emailProperties = $emailProperties;
        
        return $this;
    }
    
    function setPasswordProperties(array $passwordProperties) {
        $this->passwordProperties = $passwordProperties;
        
        return $this;
    }
    
    /**
     * Generates the form
     * @return \Zend\Form\Form
     */
    function getForm() {
        $form = new Form();
        
        $useBlacklist = (count($this->propertyBlacklist) > 0) ? true : false;
        $useWhitelist = (count($this->propertyWhitelist) > 0) ? true : false;
        
        $properties = EntityReader::getProperties($this->entityNamespace);
        foreach ($properties as $property) {
            $name = $property->getName();
            if (    //handle black&whitelist
                ($useWhitelist && !in_array($name, $this->propertyWhitelist))
                || ($useBlacklist && in_array($name, $this->propertyBlacklist))
            ) {
                continue;
            }
            
            switch ($property->getType()) {
            	case Property::PROPERTY_TYPE_COLUMN:
            	    $this->addColumnElementToForm($form, $property);
            	    break;
            	case Property::PROPERTY_TYPE_TOONE:
            	    $this->addSingleSelecterElementToForm($form, $property);
            	    break;
            	case Property::PROPERTY_TYPE_TOMANY:
            	    $this->addMultiSelecterElementToForm($form, $property);
            	    break;
            	default:
            	    continue 2;
            }
        }
        
        return $form;
    }
    
    /**
     * Adds a property depending column-element to the form
     * @param \Zend\Form\Form $form
     * @param \ZF2DoctrineCrudHandler\Annotation\Property $property
     */
    protected function addColumnElementToForm(\Zend\Form\Form $form, \ZF2DoctrineCrudHandler\Annotation\Property $property) {
        $annotationType = $property->getAnnotation()->type;
        switch ($annotationType) {
        	case 'datetime':
        	    $element = new DateTime($property->getName());
        	    break;
        	case 'date':
        	    $element = new Date($property->getName());
        	    break;
        	case 'time':
        	    $element = new Time($property->getName());
        	    break;
        	case 'text':
        	    $element = new Textarea($property->getName());
        	    break;
        	case 'boolean':
        	    $element = new Checkbox($property->getName());
        	    break;
        	default:
        	    if (in_array($property->getName(), $this->emailProperties)) {
        	        $element = new Email($property->getName());
        	    } else if (in_array($property->getName(), $this->passwordProperties)) {
        	        $element = new Password($property->getName());
        	        $element->setLabel($property->getName());
        	        $form->add($element);
        	    
        	        $element = new Password($property->getName() . '2');   //repeat password field
        	        $label = $property->getName() . ' (repeat)';
        	    } else {
        	        $element = new Text($property->getName());
        	    }
    	        break;
        }
        
        $element->setLabel($property->getName());
        $form->add($element);
    }
    
    /**
     * Adds a property depending single-selecter-element to the form
     * @param \Zend\Form\Form $form
     * @param \ZF2DoctrineCrudHandler\Annotation\Property $property
     */
    protected function addSingleSelecterElementToForm(\Zend\Form\Form $form, \ZF2DoctrineCrudHandler\Annotation\Property $property) {
        $element = new Select($property->getName());
        
        $options = $this->getValueOptionsFromEntity($property->getTargetEntity());
        $element->setValueOptions($options);
        
        $element->setLabel($property->getName());
        $form->add($element);
    }
    
    /**
     * Adds a property depending multi-selecter-element to the form
     * @param \Zend\Form\Form $form
     * @param \ZF2DoctrineCrudHandler\Annotation\Property $property
     */
    protected function addMultiSelecterElementToForm(\Zend\Form\Form $form, \ZF2DoctrineCrudHandler\Annotation\Property $property) {
        $element = new Select($property->getName());
        $element->setAttribute('multiple', true);
        
        $options = $this->getValueOptionsFromEntity($property->getTargetEntity());
        $element->setValueOptions($options);
        
        $element->setLabel($property->getName());
        $form->add($element);
    }
    
    /**
     * Get ValueOptions for Form-Elements by entity
     * @param string $entityNamespace
     * @return array [$id => $displayName]
     */
    protected function getValueOptionsFromEntity($entityNamespace) {
        $targets = $this->objectManager->getRepository($entityNamespace)->findBy([], ['id' => 'ASC']);
        $displayNameGetter = 'get' . ucfirst($entityNamespace::DISPLAY_NAME_PROPERTY);
        $options = [];
        foreach ($targets as $target) {
            $options[$target->getId()] = $target->$displayNameGetter();
        }
    
        return $options;
    }
}
