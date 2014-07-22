<?php

namespace ZF2DoctrineCrudHandler\Handler;

use Doctrine\Common\Annotations\AnnotationReader;

abstract class AbstractCrudHandler
{
    const DEFAULT_TEMPLATE = '';
    
    protected $annotationReader;
    
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;
    
    /**
     * 
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
     * function($entity){ ... } //return true to pass entity
     * @var \Closure
     */
    protected $filterFunction;
    
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param string $entityNamespace
     */
    function __construct(\Doctrine\Common\Persistence\ObjectManager $objectManager, $entityNamespace){
        $this->objectManager = $objectManager;
        $this->entityNamespace = $entityNamespace;
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
     * Set custom filter for special purposes (e.g. access control by entity relations)
     * function($entity){ ... } //return true to pass entity
     * @param \Closure $function
     * @return \Portalbasics\Model\CrudList\AbstractCrudHandler
     */
    function setFilterFunction(\Closure $function) {
        $this->filterFunction = $function;
        
        return $this;
    }
    
    /**
     * @param array $entities
     * @return array $entities |filtered by filterfunction
     */
    protected function filterEntities($entities) {
        if ($this->filterFunction !== NULL) {
            $filteredEntities = [];
            foreach ($entities as $entity) {
                if ($this->filterEntity($entity)) {
                    $filteredEntities[] = $entity;
                }
            }
            return $filteredEntities;
        } else {
            return $entities;
        }
    }
    
    /**
     * @param $entity
     * @return bool
     */
    protected function filterEntity($entity) {
        if ($this->filterFunction($entity)) {
            return true;
        } else {
            return false;
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
    
    /**
     * 
     * @param array $entityProperties
     * @throws \Exception
     */
    protected function setupEntityProperties(array $entityProperties) {
        $consistentEntityProperties = [];
        foreach ($entityProperties as $label => $entityProperty) {
            if (is_numeric($label)) {   //simple usage (th-label == property name)
                if ($entityProperty instanceof \Closure) {  //wrong usage message
                    throw new \Exception('CrudList\'s entityProperties contains unlabeled Closure! Label it like this: [\'Parent Name\' => function($me, $entity){ return $entity->getParent()->getName(); }]');
                }
                $consistentEntityProperties[$entityProperty] = $this->createClosureFromPropertyName($entityProperty);
            } else {    //advanced usage (th-label != property name) or (e.g. fk-function-chain)
                if ($entityProperty instanceof \Closure) {
                    $consistentEntityProperties[$label] = $entityProperty;
                } else {
                    $consistentEntityProperties[$label] = $this->createClosureFromPropertyName($entityProperty);
                }
            }
        }
        $this->viewModel->setVariable('entityProperties', $consistentEntityProperties);
    }
    
    /**
     * 
     * @param string $propertyName
     * @return \Closure
     */
    protected function createClosureFromPropertyName($propertyName) {
        $functionName = 'get' . ucfirst($propertyName);
        eval('$closure = function($me, $entity){ return $entity->' . $functionName . '(); };'); //eval is not avoidable in this case. But User-Input will never be executed here
        return $closure;
    }
    
    /**
     * @return \Doctrine\Common\Annotations\AnnotationReader
     */
    protected function getAnnotationReader() {
        if ($this->annotationReader === NULL) {
            $this->annotationReader = new AnnotationReader();
        }
        return $this->annotationReader;
    }
    
    protected function render404() {
        
    }
}
