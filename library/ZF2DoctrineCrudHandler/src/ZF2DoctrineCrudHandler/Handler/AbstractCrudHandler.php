<?php

namespace ZF2DoctrineCrudHandler\Handler;

use Doctrine\Common\Annotations\AnnotationReader;

abstract class AbstractCrudHandler
{
    const DEFAULT_TEMPLATE = '';
    
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;
    
    /**
     * @var \Zend\Cache\Storage\Adapter\AbstractAdapter
     */
    protected $storageAdapter;
    
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
     * function($entity){ ... } //return true to pass entity
     * @var \Closure
     */
    protected $entityFilter;
    
    /**
     * 
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param string $entityNamespace
     * @param \Zend\Cache\Storage\Adapter\AbstractAdapter $storageAdapter
     */
    function __construct(\Doctrine\Common\Persistence\ObjectManager $objectManager, $entityNamespace, \Zend\Cache\Storage\Adapter\AbstractAdapter $storageAdapter){
        $this->objectManager = $objectManager;
        $this->entityNamespace = $entityNamespace;
        $this->storageAdapter = $storageAdapter;
    }
    
    /**
     * @return \Zend\View\Model\ViewModel
     */
    abstract function getViewModel();
    
    /**
     * setup viewmodel to use the action-related view template
     * else CrudList uses its default template you can style as you need using css
     * @return \Portalbasics\Model\CrudList\ListViewHandler
     */
    function useCustomTemplate(){
        $this->useCustomTemplate = true;
        
        return $this;
    }
    
    /**
     * @param string $title
     * @return \Portalbasics\Model\CrudList\ListViewHandler
     */
    function setTitle($title) {
        $this->title = $title;
        
        return $this;
    }
    
    /**
     * Blacklist Entity properties like this: ['name', 'password']
     * @param array $blacklist
     * @return \ZF2DoctrineCrudHandler\Handler\AbstractCrudHandler
     */
    function setPropertyBlacklist(array $blacklist) {
        $this->propertyBlacklist = $blacklist;
    
        return $this;
    }
    
    /**
     * Whitelist Entity properties like this: ['name', 'password']
     * @param array $whitelist
     * @return \ZF2DoctrineCrudHandler\Handler\AbstractCrudHandler
     */
    function setPropertyWhitelist(array $whitelist) {
        $this->propertyWhitelist = $whitelist;
    
        return $this;
    }
    
    /**
     * Set custom filter for special purposes (e.g. access control by entity relations)
     * function($entity){ ... } //return true to pass entity
     * @param \Closure $function
     * @return \Portalbasics\Model\CrudList\AbstractCrudHandler
     */
    function setEntityFilter(\Closure $filter) {
        $this->entityFilter = $filter;
        
        return $this;
    }
    
    /**
     * @param array $entities
     * @return array $entities filtered by filterfunction
     */
    protected function filterEntities($entities) {
        if ($this->entityFilter !== NULL) {
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
     */
    protected function setupTemplate() {
        if ($this->useCustomTemplate == false) {
            $this->viewModel->setTemplate($this::DEFAULT_TEMPLATE);
        }
    }
    
    /**
     * Set Title into ViewModel, extract Entity-Classname if no title set
     */
    protected function setupTitle() {
        if ($this->title === NULL) {
            $this->title = end(explode('\\', $this->entityNamespace));
        }
        $this->viewModel->setVariable('title', $this->title);
    }
    
    protected function render404() {
        
    }
    
//     /**
//      * 
//      * @param array $entityProperties
//      * @throws \Exception
//      */
//     protected function setupEntityProperties(array $entityProperties) {
//         $consistentEntityProperties = [];
//         foreach ($entityProperties as $label => $entityProperty) {
//             if (is_numeric($label)) {   //simple usage (th-label == property name)
//                 if ($entityProperty instanceof \Closure) {  //wrong usage message
//                     throw new \Exception('CrudList\'s entityProperties contains unlabeled Closure! Label it like this: [\'Parent Name\' => function($me, $entity){ return $entity->getParent()->getName(); }]');
//                 }
//                 $consistentEntityProperties[$entityProperty] = $this->createClosureFromPropertyName($entityProperty);
//             } else {    //advanced usage (th-label != property name) or (e.g. fk-function-chain)
//                 if ($entityProperty instanceof \Closure) {
//                     $consistentEntityProperties[$label] = $entityProperty;
//                 } else {
//                     $consistentEntityProperties[$label] = $this->createClosureFromPropertyName($entityProperty);
//                 }
//             }
//         }
//         $this->viewModel->setVariable('entityProperties', $consistentEntityProperties);
//     }
    
//     /**
//      * 
//      * @param string $propertyName
//      * @return \Closure
//      */
//     protected function createClosureFromPropertyName($propertyName) {
//         $functionName = 'get' . ucfirst($propertyName);
//         eval('$closure = function($me, $entity){ return $entity->' . $functionName . '(); };'); //eval is not avoidable in this case. But User-Input will never be executed here
//         return $closure;
//     }
}
