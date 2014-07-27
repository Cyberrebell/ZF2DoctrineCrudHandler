<?php

namespace ZF2DoctrineCrudHandler\Handler;

use Zend\View\Model\ViewModel;

class ShowHandler extends AbstractDataHandler
{
    const DEFAULT_TEMPLATE = 'zf2doctrinecrudhandler/show.phtml';
    
    protected $entityId;
    
    /**
     * @param int $id
     * 
     * @return null
     */
    public function setEntityId($id)
    {
        $this->entityId = $id;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Portalbasics\Model\CrudList\AbstractCrudHandler::getViewModel()
     */
    public function getViewModel()
    {
        if ($this->useCache) {
            $viewModel = $this->recacheAgent->getViewModel('show', $this->entityNamespace, $this->entityId);
            if ($viewModel) {
                return $viewModel;
            }
        }
        
        $this->viewModel = new ViewModel();
        
        $dataToDisplay = $this->getEntityData($this->entityId);
        if ($dataToDisplay === false) {
            $this->render404();
        }
        $this->viewModel->setVariable('dataToDisplay', $dataToDisplay);
        
        $this->setupTemplate();
        $this->setupTitle();
        
        if ($this->useCache) {
            $this->recacheAgent->storeViewModel($this->viewModel, 'show', $this->entityNamespace, $this->entityId);
        }
        
        return $this->viewModel;
    }
}
