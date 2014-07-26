<?php

namespace ZF2DoctrineCrudHandler\Handler;

use ZF2DoctrineCrudHandler\Reader\EntityReader;
use Zend\View\Model\ViewModel;

class ListHandler extends AbstractDataHandler
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
        $viewModel = $this->recacheAgent->getViewModel('list', $this->entityNamespace, '');
        if ($viewModel) {
            return $viewModel;
        }
        
        $this->viewModel = new ViewModel();
        
        $entities = $this->objectManager->getRepository($this->entityNamespace)->findBy($this->criteria);
        $entities = $this->filterEntities($entities);
        
        $entitiesData = [];
        foreach ($entities as $entity) {
            $entitiesData[$entity->getId()] = $this->getEntityData($entity->getId());
        }
        
        $this->viewModel->setVariable('entitiesData', $entitiesData);
        
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
        
        $this->recacheAgent->storeViewModel($this->viewModel, 'list', $this->entityNamespace, '');
        
        return $this->viewModel;
    }
    
    /**
     * 
     * @param array $criteria
     * 
     * @return null
     */
    public function setCriteria(array $criteria)
    {
        $this->criteria = $criteria;
    }
    
    /**
     * Add icons like ['show' => '/showuser', 'edit' => '/edituser', 'delete' => '/deleteuser'] to list
     * 
     * @param array $icons
     * 
     * @return null
     */
    public function setIcons(array $icons)
    {
        $this->listIcons = $icons;
    }
    
    /**
     * select icons to display only in <thead>
     * 
     * @param array $icons
     * 
     * @return null
     */
    public function setHeadIcons(array $icons)
    {
        $this->listHeadIcons = $icons;
    }

    protected function getEntityProperties() {
        $useBlacklist = (count($this->propertyBlacklist) > 0) ? true : false;
        $useWhitelist = (count($this->propertyWhitelist) > 0) ? true : false;
        
        $properties = EntityReader::getProperties($this->entityNamespace);
        $filteredProperties = [];
        foreach ($properties as $property) {
            $name = $property->getName();
            if (//handle black&whitelist
            ($useWhitelist && !in_array($name, $this->propertyWhitelist))
            || ($useBlacklist && in_array($name, $this->propertyBlacklist))
            ) {
                continue;
            }
            $filteredProperties[] = $property->getName();
        }
        return $filteredProperties;
    }
}
