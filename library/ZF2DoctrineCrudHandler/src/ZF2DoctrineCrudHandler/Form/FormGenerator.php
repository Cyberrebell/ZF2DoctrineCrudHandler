<?php
/**
 * File containing FormGenerator class
 *
 * PHP version 5
 *
 * @category  ZF2DoctrineCrudHandler
 * @package   ZF2DoctrineCrudHandler\Form
 * @author    Cyberrebell <cyberrebell@web.de>
 * @copyright 2014 - 2014 Cyberrebell
 * @license   http://www.gnu.org/licenses/gpl-3.0 GPL-3.0
 * @version   GIT: <git_id>
 * @link      https://github.com/Cyberrebell/ZF2DoctrineCrudHandler
 */

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
use Zend\Form\Element\Radio;
use Zend\Form\Element\MultiCheckbox;

/**
 * Generates Zend Form Object from Entity-Annotations
 * 
 * @category ZF2DoctrineCrudHandler
 * @package  ZF2DoctrineCrudHandler\Form
 * @author   Cyberrebell <cyberrebell@web.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0 GPL-3.0
 * @link     https://github.com/Cyberrebell/ZF2DoctrineCrudHandler
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
    
    protected $toOneElement = self::TO_ONE_ELEMENT_SELECT;
    protected $toManyElement = self::TO_MANY_ELEMENT_MULTISELECT;
    
    /**
     * Constructor for FormGenerator
     * 
     * @param \Doctrine\Common\Persistence\ObjectManager  $objectManager   Doctrine-Object-Manager
     * @param string                                      $entityNamespace Namespace of Entity to generate the form for
     * @param \Zend\Cache\Storage\Adapter\AbstractAdapter $storageAdapter  Cache Adapter
     */
    public function __construct(
        \Doctrine\Common\Persistence\ObjectManager $objectManager,
        $entityNamespace,
        \Zend\Cache\Storage\Adapter\AbstractAdapter $storageAdapter
    ) {
        $this->objectManager = $objectManager;
        $this->entityNamespace = $entityNamespace;
        $this->storageAdapter = $storageAdapter;
    }
    
    /**
     * Blacklist Entity-Properties for form-generation
     * 
     * @param array:string $blacklist ['password', 'registrationDate']
     * 
     * @return null
     */
    public function setPropertyBlacklist(array $blacklist)
    {
        $this->propertyBlacklist = $blacklist;
    }
    
    /**
     * Whitelist Entity-Properties for form-generation
     * 
     * @param array $whitelist ['name', 'age']
     * 
     * @return null
     */
    public function setPropertyWhitelist(array $whitelist)
    {
        $this->propertyWhitelist = $whitelist;
    }
    
    /**
     * Set Entity-Properties to be email inputs in form-generation
     * 
     * @param array:string $emailProperties ['admin@mail.com']
     * 
     * @return null
     */
    public function setEmailProperties(array $emailProperties)
    {
        $this->emailProperties = $emailProperties;
    }
    
    /**
     * Set Entity-Properties to be password inputs in form-generation
     * 
     * @param array $passwordProperties ['password']
     * 
     * @return null
     */
    public function setPasswordProperties(array $passwordProperties)
    {
        $this->passwordProperties = $passwordProperties;
    }
    
    /**
     * Generates the form
     * 
     * @return \Zend\Form\Form
     */
    public function getForm()
    {
        $form = new Form();
        
        $useBlacklist = (count($this->propertyBlacklist) > 0) ? true : false;
        $useWhitelist = (count($this->propertyWhitelist) > 0) ? true : false;
        
        $properties = EntityReader::getProperties($this->entityNamespace);
        foreach ($properties as $property) {
            $name = $property->getName();
            if (($useWhitelist && !in_array($name, $this->propertyWhitelist))
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
        
        $submit = new Submit('save');
        $submit->setValue('save');
        $form->add($submit);
        
        return $form;
    }
    
    /**
     * Adds a property depending column-element to the form
     * 
     * @param \Zend\Form\Form                         $form     Form object to add the element to
     * @param \ZF2DoctrineCrudHandler\Reader\Property $property Property to generate the element from
     * 
     * @return null
     */
    protected function addColumnElementToForm(
        \Zend\Form\Form $form,
        \ZF2DoctrineCrudHandler\Reader\Property $property
    ) {
        $annotationType = $property->getAnnotation()->type;
        $label = $property->getName();
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
                } elseif (in_array($property->getName(), $this->passwordProperties)) {
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
        
        $element->setLabel($label);
        $form->add($element);
    }
    
    /**
     * Adds a property depending single-selecter-element to the form
     * 
     * @param \Zend\Form\Form                         $form     Form object to add the element to
     * @param \ZF2DoctrineCrudHandler\Reader\Property $property Property to generate the element from
     * 
     * @return null
     */
    protected function addSingleSelecterElementToForm(
        \Zend\Form\Form $form,
        \ZF2DoctrineCrudHandler\Reader\Property $property
    ) {
        if ($this->toOneElement == $this::TO_ONE_ELEMENT_SELECT) {
            $element = new Select($property->getName());
        } else {
            $element = new Radio($property->getName());
        }
        
        $options = $this->getValueOptionsFromEntity($property->getTargetEntity());
        $element->setValueOptions($options);
        
        $element->setLabel($property->getName());
        $form->add($element);
    }
    
    /**
     * Adds a property depending multi-selecter-element to the form
     * 
     * @param \Zend\Form\Form                         $form     Form object to add the element to
     * @param \ZF2DoctrineCrudHandler\Reader\Property $property Property to generate the element from
     * 
     * @return null
     */
    protected function addMultiSelecterElementToForm(
        \Zend\Form\Form $form,
        \ZF2DoctrineCrudHandler\Reader\Property $property
    ) {
        if ($this->toManyElement == $this::TO_MANY_ELEMENT_MULTISELECT) {
            $element = new Select($property->getName());
            $element->setAttribute('multiple', true);
        } else {
            $element = new MultiCheckbox($property->getName());
        }
        
        $options = $this->getValueOptionsFromEntity($property->getTargetEntity());
        $element->setValueOptions($options);
        
        $element->setLabel($property->getName());
        $form->add($element);
    }
    
    /**
     * Get ValueOptions for Form-Elements by entity
     * 
     * @param string $entityNamespace Namespace of Entity to get the ValueOptions from Entity
     * 
     * @return array:string [$id => $displayName]
     */
    protected function getValueOptionsFromEntity($entityNamespace)
    {
        $targets = $this->objectManager->getRepository($entityNamespace)->findBy([], ['id' => 'ASC']);
        $displayNameGetter = 'get' . ucfirst($entityNamespace::DISPLAY_NAME_PROPERTY);
        $options = [];
        foreach ($targets as $target) {
            $options[$target->getId()] = $target->$displayNameGetter();
        }
    
        return $options;
    }
}
