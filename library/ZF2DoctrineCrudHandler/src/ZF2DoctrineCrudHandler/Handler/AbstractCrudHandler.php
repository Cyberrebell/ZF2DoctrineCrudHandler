<?php
/**
 * File containing AbstractCrudHandler class
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

use Doctrine\Common\Annotations\AnnotationReader;
use ZF2DoctrineCrudHandler\Cache\RecacheAgent;

/**
 * Abstract Class for Crud-Handlers
 * Offers some configuration options all Handlers need
 *
 * @category ZF2DoctrineCrudHandler
 * @package  ZF2DoctrineCrudHandler\Handler
 * @author   Cyberrebell <cyberrebell@web.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0 GPL-3.0
 * @link     https://github.com/Cyberrebell/ZF2DoctrineCrudHandler
 */
abstract class AbstractCrudHandler
{
    const DEFAULT_TEMPLATE = '';
    
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;
    
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;
    
    /**
     * @var \Zend\Cache\Storage\Adapter\AbstractAdapter
     */
    protected $storageAdapter;
    
    protected $recacheAgent;
    
    /**
     * @var string
     */
    protected $entityNamespace;
    
    /**
     * @var \Zend\View\Model\ViewModel
     */
    protected $viewModel;
    
    /**
     * @var bool
     */
    protected $useCustomTemplate = false;
    
    /**
     * @var string
     */
    protected $title;
    
    /**
     * @var array:string
     */
    protected $propertyBlacklist = [];
    
    /**
     * @var array:string
     */
    protected $propertyWhitelist = [];
    
    /**
     * function($entity){ ... }
     * return true to pass entity
     * @var \Closure
     */
    protected $entityFilter;
    
    /**
     * Generates a ViewModel which is ready to render
     * 
     * @return \Zend\View\Model\ViewModel
     */
    abstract public function getViewModel();
    
    /**
     * setup viewmodel to use the action-related view template
     * else CrudList uses its default template you can style as you need using css
     * 
     * @return null
     */
    public function useCustomTemplate()
    {
        $this->useCustomTemplate = true;
    }
    
    /**
     * Set a title to display in view
     * Default title will be the entity-name
     * 
     * @param string $title simple string
     * 
     * @return null
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    /**
     * Blacklist Entity-Properties for view-generation
     * 
     * @param array $blacklist ['password', 'registrationDate']
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
     * Set custom filter for special purposes (e.g. access control by entity relations)
     * 
     * The Closure should return true if the entity should be used for view-generation
     * 
     * @param \Closure $filter function($entity){ ... }
     * 
     * @return null
     */
    public function setEntityFilter(\Closure $filter)
    {
        $this->entityFilter = $filter;
    }
    
    /**
     * Use given entityFilter to get only allowed
     * 
     * @param array $entities Input Entities
     * 
     * @return array $entities filtered by filterfunction
     */
    protected function filterEntities($entities)
    {
        if ($this->entityFilter !== null) {
            $filter = $this->entityFilter;
            $filteredEntities = [];
            foreach ($entities as $entity) {
                if ($filter($entity)) {
                    $filteredEntities[] = $entity;
                }
            }
            return $filteredEntities;
        } else {
            return $entities;
        }
    }

    /**
     * Setup CrudList-Default-Template if custom use is not set
     * 
     * @return null
     */
    protected function setupTemplate()
    {
        if ($this->useCustomTemplate == false) {
            $this->viewModel->setTemplate($this::DEFAULT_TEMPLATE);
        }
    }
    
    /**
     * Set Title into ViewModel, extract Entity-Classname if no title set
     * 
     * @return null
     */
    protected function setupTitle()
    {
        if ($this->title === null) {
            $segments = explode('\\', $this->entityNamespace);
            $this->title = end($segments);
        }
        $this->viewModel->setVariable('title', $this->title);
    }
    
    /**
     * Switch ViewModel to render 404 template
     * 
     * @return null
     */
    protected function render404()
    {
        
    }
    
    protected function initRecacheAgent() {
        $eventManager = $this->objectManager->getEventManager();
        $this->recacheAgent = new RecacheAgent($eventManager, $this->storageAdapter);
    }
}
