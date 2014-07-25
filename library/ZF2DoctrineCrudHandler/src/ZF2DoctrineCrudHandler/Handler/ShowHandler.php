<?php

namespace ZF2DoctrineCrudHandler\Handler;

use Zend\View\Model\ViewModel;

class ShowHandler extends AbstractDataHandler
{
    const DEFAULT_TEMPLATE = 'zf2doctrinecrudhandler/show.phtml';
    
    protected $entityId;
    
    /**
     * @param int $id
     * @return \ZF2DoctrineCrudHandler\Handler\EditHandler
     */
    public function setEntityId($id)
    {
        $this->entityId = $id;
    
        return $this;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Portalbasics\Model\CrudList\AbstractCrudHandler::getViewModel()
     */
    public function getViewModel()
    {
        $this->viewModel = new ViewModel();
        
        $dataToDisplay = $this->getEntityData($this->entityId);
        $this->viewModel->setVariable('dataToDisplay', $dataToDisplay);
        
        $this->setupTemplate();
        $this->setupTitle();
        
        return $this->viewModel;
    }
}
