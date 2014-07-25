<?php
/**
 * File containing AbstractFormHandler class
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

use ZF2DoctrineCrudHandler\Form\FormGenerator;

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
abstract class AbstractFormHandler extends AbstractCrudHandler
{
    protected $formGenerator;
    protected $request;
    
    /**
     * Constructor for AbstractFormHandler
     * 
     * @param \Doctrine\Common\Persistence\ObjectManager  $objectManager   Doctrine-Object-Manager
     * @param string                                      $entityNamespace Namespace of Entity to do operations for
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
    
        $this->formGenerator = new FormGenerator($this->objectManager, $this->entityNamespace, $this->storageAdapter);
        
        unset($this->propertyBlacklist);
        unset($this->propertyWhitelist);
    }
    
    /**
     * Set Request which may be POST
     * needed to handle POST's
     * 
     * @param \Zend\Http\Request $request Request of Controller
     * 
     * @return \ZF2DoctrineCrudHandler\Handler\AbstractFormHandler
     */
    public function setRequest(\Zend\Http\Request $request)
    {
        $this->request = $request;
        
        return $this;
    }
    
    /**
     * Blacklist Entity-Properties for form-generation
     * 
     * @param array:string $blacklist ['password', 'registrationDate']
     * 
     * @return \ZF2DoctrineCrudHandler\Handler\AbstractFormHandler
     */
    public function setPropertyBlacklist(array $blacklist)
    {
        $this->formGenerator->setPropertyBlacklist($blacklist);
    
        return $this;
    }
    
    /**
     * Whitelist Entity-Properties for form-generation
     * 
     * @param array $whitelist ['name', 'age']
     * 
     * @return \ZF2DoctrineCrudHandler\Handler\AbstractFormHandler
     */
    public function setPropertyWhitelist(array $whitelist)
    {
        $this->formGenerator->setPropertyWhitelist($whitelist);
    
        return $this;
    }
    
    /**
     * Set Entity-Properties to be email inputs in form-generation
     * 
     * @param array:string $emailProperties ['admin@mail.com']
     * 
     * @return \ZF2DoctrineCrudHandler\Handler\AbstractFormHandler
     */
    public function setEmailProperties(array $emailProperties)
    {
        $this->formGenerator->setEmailProperties($emailProperties);
        
        return $this;
    }
    
    /**
     * Set Entity-Properties to be password inputs in form-generation
     * 
     * @param array $passwordProperties ['password']
     * 
     * @return \ZF2DoctrineCrudHandler\Handler\AbstractFormHandler
     */
    public function setPasswordProperties(array $passwordProperties)
    {
        $this->formGenerator->setPasswordProperties($passwordProperties);
        
        return $this;
    }
}
