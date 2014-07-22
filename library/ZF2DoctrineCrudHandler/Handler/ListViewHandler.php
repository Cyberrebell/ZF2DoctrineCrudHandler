<?php

namespace ZF2DoctrineCrudHandler\Handler;

use Zend\View\Model\ViewModel;

class ListViewHandler extends AbstractCrudHandler
{
    const DEFAULT_TEMPLATE = 'portalbasics/crudlist/list.phtml';
    
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
    protected $tableColumns = ['id'];
    
    /**
     * @var array
     */
    protected $criteria = [];
    
    /**
     * (non-PHPdoc)
     * @see \Portalbasics\Model\CrudList\AbstractCrudHandler::getViewModel()
     */
    function getViewModel() {
        $this->viewModel = new ViewModel();
        
        $entities = $this->objectManager->getRepository($this->entityNamespace)->findBy($this->criteria);
        $entities = $this->filterEntities($entities);
        $this->viewModel->setVariable('entities', $entities);
        
        $this->setupEntityProperties($this->tableColumns);
        
        if ($this->listIcons === NULL) {
            $this->listIcons = ['show' => '', 'edit' => '', 'delete' => ''];
        }
        $this->viewModel->setVariable('listIcons', $this->listIcons);
        if ($this->listHeadIcons === NULL) {
            $this->listHeadIcons = ['add' => ''];
        }
        $this->viewModel->setVariable('listHeadIcons', $this->listHeadIcons);
        
        $this->setupTemplate();
        $this->setupTitle();
        
        return $this->viewModel;
    }
    
    function setTableColumns(array $tableColumns) {
        $this->tableColumns = $tableColumns;
    
        return $this;
    }
    
    /**
     * 
     * @param array $criteria
     * @return \Portalbasics\Model\CrudList\ListViewHandler
     */
    function setCriteria(array $criteria) {
        $this->criteria = $criteria;
    
        return $this;
    }
    
    /**
     * Add icons like ['show' => '/showuser', 'edit' => '/edituser', 'delete' => '/deleteuser'] to list
     * @param array $icons
     * @return \Portalbasics\Model\CrudList\ListViewHandler
     */
    function setIcons(array $icons) {
        $this->listIcons = $icons;
        
        return $this;
    }
    
    /**
     * select icons to display only in <thead>
     * @param array $icons
     * @return \Portalbasics\Model\CrudList\ListViewHandler
     */
    function setHeadIcons(array $icons) {
        $this->listHeadIcons = $icons;
        
        return $this;
    }
}
