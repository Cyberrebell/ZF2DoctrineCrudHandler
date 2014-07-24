<?php

namespace ZF2DoctrineCrudHandler\Handler;

use Zend\View\Model\ViewModel;
use ZF2DoctrineCrudHandler\Request\RequestHandler;

class AddHandler extends AbstractFormHandler
{
    const DEFAULT_TEMPLATE = 'zf2doctrinecrudhandler/add.phtml';
    
    /**
     * (non-PHPdoc)
     * @see \ZF2DoctrineCrudHandler\Handler\AbstractCrudHandler::getViewModel()
     */
    function getViewModel() {
        $this->viewModel = new ViewModel();
        
        $form = $this->formGenerator->getForm();
        $this->viewModel->setVariable('form', $form);
        
        RequestHandler::handleAdd($this->objectManager, $this->entityNamespace, $form, $this->request);
        
        $this->setupTemplate();
        $this->setupTitle();
        
        return $this->viewModel;
    }
}
