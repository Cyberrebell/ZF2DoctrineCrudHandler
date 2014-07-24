<?php

namespace ZF2DoctrineCrudHandler\Handler;

use ZF2DoctrineCrudHandler\Form\FormGenerator;

abstract class AbstractFormHandler extends AbstractCrudHandler
{
    protected $formGenerator;
    protected $request;
    
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param string $entityNamespace
     * @param \Zend\Cache\Storage\Adapter\AbstractAdapter $storageAdapter
     */
    function __construct(\Doctrine\Common\Persistence\ObjectManager $objectManager, $entityNamespace, \Zend\Cache\Storage\Adapter\AbstractAdapter $storageAdapter){
        $this->objectManager = $objectManager;
        $this->entityNamespace = $entityNamespace;
        $this->storageAdapter = $storageAdapter;
    
        $this->formGenerator = new FormGenerator($this->objectManager, $this->entityNamespace, $this->storageAdapter);
        
        unset($this->propertyBlacklist);
        unset($this->propertyWhitelist);
    }
    
    /**
     * Set Request which may be POST
     * @param \Zend\Http\Request $request
     * @return \ZF2DoctrineCrudHandler\Handler\AbstractFormHandler
     */
    function setRequest(\Zend\Http\Request $request) {
        $this->request = $request;
        
        return $this;
    }
    
    /**
     * Blacklist Entity properties like this: ['name', 'password']
     * @param array $blacklist
     * @return \ZF2DoctrineCrudHandler\Handler\AbstractCrudHandler
     */
    function setPropertyBlacklist(array $blacklist) {
        $this->formGenerator->setPropertyBlacklist($blacklist);
    
        return $this;
    }
    
    /**
     * Whitelist Entity properties like this: ['name', 'password']
     * @param array $whitelist
     * @return \ZF2DoctrineCrudHandler\Handler\AbstractCrudHandler
     */
    function setPropertyWhitelist(array $whitelist) {
        $this->formGenerator->setPropertyWhitelist($whitelist);
    
        return $this;
    }
    
    /**
     * Mark properties as email-field
     * used in form generation
     * @param array $emailProperties
     * @return \ZF2DoctrineCrudHandler\Handler\AbstractFormHandler
     */
    function setEmailProperties(array $emailProperties) {
        $this->formGenerator->setEmailProperties($emailProperties);
        
        return $this;
    }
    
    /**
     * Mark properties as password-field
     * used in form generation
     * @param array $passwordProperties
     * @return \ZF2DoctrineCrudHandler\Handler\AbstractFormHandler
     */
    function setPasswordProperties(array $passwordProperties) {
        $this->formGenerator->setPasswordProperties($passwordProperties);
        
        return $this;
    }
}
