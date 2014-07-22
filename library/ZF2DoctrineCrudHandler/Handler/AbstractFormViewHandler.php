<?php

namespace ZF2DoctrineCrudHandler\Handler;

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

abstract class AbstractFormViewHandler extends AbstractCrudHandler
{
    protected $propertyBlacklist = [];
    
    protected $emailProperties = [];
    
    protected $passwordProperties = [];
    
    protected $form;
    
    protected $request;
    
    /**
     * Blacklist Properties that should not be in the form (e.g. ['creationDate'])
     * @param array $properties
     * @return \Portalbasics\Model\CrudList\AbstractFormViewHandler
     */
    function setPropertyBlacklist(array $properties) {
        $this->propertyBlacklist = $properties;
        
        return $this;
    }
    
    /**
     * Switch Text-Input to Email-Input (e.g. ['emailAddress'])
     * @param array $properties
     * @return \Portalbasics\Model\CrudList\AbstractFormViewHandler
     */
    function setEmailProperties(array $properties) {
        $this->emailProperties = $properties;
        
        return $this;
    }
    
    /**
     * Switch Text-Input to Password-Input (e.g. ['changePassword'])
     * Password-Inputs will have an repeat-passwort-input
     * @param array $properties
     * @return \Portalbasics\Model\CrudList\AbstractFormViewHandler
     */
    function setPasswordProperties(array $properties) {
        $this->passwordProperties = $properties;
        
        return $this;
    }
    
    function setRequest(\Zend\Http\Request $request) {
        $this->request = $request;
        
        return $this;
    }
    
    /**
     * creates form from Entity Annotations
     */
    protected function createForm() {
        $this->form = new Form();
        
        $reflectionClass = new \ReflectionClass($this->entityNamespace);
        
        $entityProperties = $reflectionClass->getProperties();
        foreach ($entityProperties as $property) {
            $this->createElement($property);
        }
        $submit = new Submit('save');
        $submit->setValue('save');
        $this->form->add($submit);
    }
    
    /**
     * creates the form Element for the Entity-Property
     * @param \ReflectionProperty $property
     * @return boolean
     */
    protected function createElement(\ReflectionProperty $property) {
        $elementName = $property->getName();
        if (in_array($elementName, $this->propertyBlacklist)) {
            return false;
        } else {
            $annotations = $this->getAnnotationReader()->getPropertyAnnotations($property);
            $propertyType = $this->getPropertyType($annotations);
            $annotationType = reset(array_keys($propertyType));
            switch ($annotationType) {
            	case 0:    //column
            	    $columnAnnotation = $annotations[$propertyType[$annotationType]];
            	    $this->createElementFromColumnAnnotation($elementName, $columnAnnotation);
            	    break;
            	case 1:    //xToOne
            	    $relationAnnotation = $annotations[$propertyType[$annotationType]];
            	    $this->createElementFromToOneAnnotation($elementName, $relationAnnotation);
            	    break;
            	case 2:    //xToMany
            	    $relationAnnotation = $annotations[$propertyType[$annotationType]];
            	    $this->createElementFromToManyAnnotation($elementName, $relationAnnotation);
            	    break;
            	default:   //Id or unknown
            	    return false;
            }
        }
    }
    
    /**
     * 
     * @param array $annotations (\Doctrine\ORM\Mapping\Annotation)
     * @return array [$propertyType, $annotationNumber]
     */
    protected function getPropertyType($annotations) {
        $result = [-1 => false];
        foreach ($annotations as $annotationNumber => $annotation) {
            $className = get_class($annotation);
            if ($className == 'Doctrine\ORM\Mapping\Id') {
                return [-1 => false];
            } else if ($className == 'Doctrine\ORM\Mapping\Column') {
                $result = [0 => $annotationNumber];
            } else if ($className == 'Doctrine\ORM\Mapping\ManyToOne' || $className == 'Doctrine\ORM\Mapping\OneToOne') {
                $result = [1 => $annotationNumber];
            } else if ($className == 'Doctrine\ORM\Mapping\ManyToMany' || $className == 'Doctrine\ORM\Mapping\OneToMany') {
                $result = [2 => $annotationNumber];
            }
        }
        return $result;
    }
    
    /**
     * Creates an Form-Element from Column-Annotation information
     * @param string $elementName
     * @param \Doctrine\ORM\Mapping\Annotation $annotation
     */
    protected function createElementFromColumnAnnotation($elementName, \Doctrine\ORM\Mapping\Annotation $annotation) {
        $label = $elementName;
        switch ($annotation->type) {
        	case 'datetime':
        	    $element = new DateTime($elementName);
        	    break;
        	case 'date':
        	    $element = new Date($elementName);
        	    break;
        	case 'time':
        	    $element = new Time($elementName);
        	    break;
        	case 'text':
        	    $element = new Textarea($elementName);
        	    break;
        	case 'boolean':
        	    $element = new Checkbox($elementName);
        	    break;
        	default:
        	    if (in_array($elementName, $this->emailProperties)) {
        	        $element = new Email($elementName);
        	    } else if (in_array($elementName, $this->passwordProperties)) {
        	        $element = new Password($elementName);
                    $element->setLabel($elementName);
        	        $this->form->add($element);
        	        
        	        $element = new Password($elementName . '2');   //repeat password field
        	        $label = $elementName . ' (repeat)';
        	    } else {
        	        $element = new Text($elementName);
        	    }
        	    break;
        }
        
        $element->setLabel($label);
        $this->form->add($element);
    }
    
    /**
     * Creates an Form-Element from xToOne-Annotation information
     * @param string $elementName
     * @param \Doctrine\ORM\Mapping\Annotation $annotation
     */
    protected function createElementFromToOneAnnotation($elementName, \Doctrine\ORM\Mapping\Annotation $annotation) {
        $element = new Select($elementName);
        
        $element->setLabel($elementName);
        
        $options = $this->getValueOptionsFromEntity($annotation->targetEntity);
        $element->setValueOptions($options);
        
        $this->form->add($element);
    }
    
    /**
     * Creates an Form-Element from xToMany-Annotation information
     * @param string $elementName
     * @param \Doctrine\ORM\Mapping\Annotation $annotation
     */
    protected function createElementFromToManyAnnotation($elementName, \Doctrine\ORM\Mapping\Annotation $annotation) {
        $element = new Select($elementName);
        $element->setAttribute('multiple', true);
        
        $element->setLabel($elementName);
        
        $options = $this->getValueOptionsFromEntity($annotation->targetEntity);
        $element->setValueOptions($options);
        
        $this->form->add($element);
    }
    
    /**
     * Get ValueOptions for Form-Elements containing all options of $entityNamespace table
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
    
    /**
     * Handle the request and act if post is set
     */
    abstract protected function handleRequest();
}
