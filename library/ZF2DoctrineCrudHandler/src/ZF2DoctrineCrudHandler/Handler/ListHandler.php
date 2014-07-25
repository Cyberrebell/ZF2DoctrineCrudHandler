<?php

namespace ZF2DoctrineCrudHandler\Handler;

use ZF2DoctrineCrudHandler\Reader\EntityReader;
use ZF2DoctrineCrudHandler\Reader\Property;
use Zend\View\Model\ViewModel;

class ListHandler extends AbstractCrudHandler
{
    const DEFAULT_TEMPLATE = 'zf2doctrinecrudhandler/list.phtml';
    
    /**
     * @var array
     */
    protected $listIcons;
    
    /**
     * @var array
     */
    protected $listHeadIcons;
    
    /**
     * @var array
     */
    protected $criteria = [];
    
    /**
     * (non-PHPdoc)
     * @see \Portalbasics\Model\CrudList\AbstractCrudHandler::getViewModel()
     */
    public function getViewModel()
    {
        $this->viewModel = new ViewModel();
        
        $entities = $this->objectManager->getRepository($this->entityNamespace)->findBy($this->criteria);
        $entities = $this->filterEntities($entities);
        $this->viewModel->setVariable('entities', $entities);
        
        $this->viewModel->setVariable('entityProperties', $this->getEntityProperties());
        
        if ($this->listIcons === null) {
            $this->listIcons = ['show' => '', 'edit' => '', 'delete' => ''];
        }
        $this->viewModel->setVariable('listIcons', $this->listIcons);
        if ($this->listHeadIcons === null) {
            $this->listHeadIcons = ['add' => ''];
        }
        $this->viewModel->setVariable('listHeadIcons', $this->listHeadIcons);
        
        $this->setupTemplate();
        $this->setupTitle();
        
        return $this->viewModel;
    }
    
    /**
     * 
     * @param array $criteria
     * @return \Portalbasics\Model\CrudList\ListViewHandler
     */
    public function setCriteria(array $criteria)
    {
        $this->criteria = $criteria;
    
        return $this;
    }
    
    /**
     * Add icons like ['show' => '/showuser', 'edit' => '/edituser', 'delete' => '/deleteuser'] to list
     * @param array $icons
     * @return \Portalbasics\Model\CrudList\ListViewHandler
     */
    public function setIcons(array $icons)
    {
        $this->listIcons = $icons;
        
        return $this;
    }
    
    /**
     * select icons to display only in <thead>
     * @param array $icons
     * @return \Portalbasics\Model\CrudList\ListViewHandler
     */
    public function setHeadIcons(array $icons)
    {
        $this->listHeadIcons = $icons;
        
        return $this;
    }
    
    protected function getEntityProperties()
    {
        $useBlacklist = (count($this->propertyBlacklist) > 0) ? true : false;
        $useWhitelist = (count($this->propertyWhitelist) > 0) ? true : false;
        
        $properties = EntityReader::getProperties($this->entityNamespace);
        
        $propertiesToDisplay = [];
        foreach ($properties as $property) {
            $name = $property->getName();
            if (//handle black&whitelist
                ($useWhitelist && !in_array($name, $this->propertyWhitelist))
                || ($useBlacklist && in_array($name, $this->propertyBlacklist))
            ) {
                continue;
            }
            switch ($property->getType()) {
                case Property::PROPERTY_TYPE_COLUMN:
                    $propertiesToDisplay[$name] = $this->createClosureFromPropertyName($name);
                    break;
                case Property::PROPERTY_TYPE_TOONE:
                    $targetEntity = $property->getTargetEntity();
                    $targetPropertsGetter = 'get' . ucfirst($targetEntity::DISPLAY_NAME_PROPERTY);
                    $propertiesToDisplay[$name] = $this->createClosureFromPropertyName(
                        $name . '()->' . $targetPropertsGetter
                    );
                    break;
                case Property::PROPERTY_TYPE_TOMANY:
//             	    $targetEntity = $property->getTargetEntity();
//             	    $targetPropertsGetter = 'get' . ucfirst($targetEntity::DISPLAY_NAME_PROPERTY);
//             	    $listString = '';
//             	    foreach ($value as $targetEntity) {
//             	        $listString .= $targetEntity->$targetPropertsGetter() . ',';
//             	    }
//             	    $value = substr($listString, 0, -1);
//             	    break;
                default:
                    continue 2;
            }
            
        }
        return $propertiesToDisplay;
    }
    
    /**
     * @param string $propertyName
     * @return \Closure
     */
    protected function createClosureFromPropertyName($propertyName)
    {
        $functionName = 'get' . ucfirst($propertyName);
        //eval is not avoidable in this case. But User-Input will never be executed here
        eval('$closure = function($me, $entity){ return $entity->' . $functionName . '(); };');
        return $closure;
    }
}
