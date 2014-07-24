<?php

namespace ZF2DoctrineCrudHandler\Handler;

use Zend\View\Model\ViewModel;
use ZF2DoctrineCrudHandler\Request\RequestHandler;

class EditHandler extends AbstractFormHandler
{
    const DEFAULT_TEMPLATE = 'zf2doctrinecrudhandler/edit.phtml';
    
    protected $entityId;
    
    /**
     * @param int $id
     * @return \ZF2DoctrineCrudHandler\Handler\EditHandler
     */
    function setEntityId($id) {
        $this->entityId = $id;
    
        return $this;
    }
    
    /**
     * (non-PHPdoc)
     * @see \ZF2DoctrineCrudHandler\Handler\AbstractCrudHandler::getViewModel()
     */
    function getViewModel() {
        $this->viewModel = new ViewModel();
        
        $form = $this->formGenerator->getForm();
        $this->viewModel->setVariable('form', $form);
        
        RequestHandler::handleEdit($this->objectManager, $this->entityNamespace, $form, $this->request, $this->entityId);
        
        $this->setupTemplate();
        $this->setupTitle();
        
        return $this->viewModel;
    }
}
