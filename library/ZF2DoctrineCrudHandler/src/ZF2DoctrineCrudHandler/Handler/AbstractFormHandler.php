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
     * @param \Zend\ServiceManager\ServiceManager $sm              ServiceManager
     * @param string                              $entityNamespace Namespace of Entity to do operations for
     */
    public function __construct(
            \Zend\ServiceManager\ServiceManager $sm,
            $entityNamespace
    ) {
        $this->serviceManager = $sm;
        $cfg = $this->serviceManager->get('Config');
        if (array_key_exists('crudhandler', $cfg)) {
            $crudCfg = $cfg['crudhandler'];
            if (array_key_exists('objectManager', $crudCfg) && array_key_exists('cache', $crudCfg)) {
                $this->entityNamespace = $entityNamespace;
                $this->objectManager = $this->serviceManager->get($crudCfg['objectManager']);
                $this->storageAdapter = $this->serviceManager->get($crudCfg['cache']);
                $this->initRecacheAgent();
                
                $this->formGenerator = new FormGenerator($this->objectManager, $this->entityNamespace, $this->storageAdapter);
                
                unset($this->propertyBlacklist);
                unset($this->propertyWhitelist);
            } else {
                throw new \Exception('"objectManager" and "cache" must be configurated in module.config -> "crudhandler"!');
            }
        } else {
            throw new \Exception('"crudhandler" is not configurated in module.config!');
        }
    }
    
    /**
     * Set Request which may be POST
     * needed to handle POST's
     * 
     * @param \Zend\Http\Request $request Request of Controller
     * 
     * @return null
     */
    public function setRequest(\Zend\Http\Request $request)
    {
        $this->request = $request;
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
        $this->formGenerator->setPropertyBlacklist($blacklist);
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
        $this->formGenerator->setPropertyWhitelist($whitelist);
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
        $this->formGenerator->setEmailProperties($emailProperties);
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
        $this->formGenerator->setPasswordProperties($passwordProperties);
    }
}
