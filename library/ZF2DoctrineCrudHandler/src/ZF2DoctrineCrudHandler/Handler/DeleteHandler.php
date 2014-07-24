<?php

namespace ZF2DoctrineCrudHandler\Handler;

use Zend\View\Model\ViewModel;
use ZF2DoctrineCrudHandler\Request\RequestHandler;

class DeleteHandler extends AbstractCrudHandler
{
    protected $entityId;
    
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param string $entityNamespace
     */
    function __construct(\Doctrine\Common\Persistence\ObjectManager $objectManager, $entityNamespace){
        $this->objectManager = $objectManager;
        $this->entityNamespace = $entityNamespace;
    }
    
    /**
     * @param int $id
     * @return \ZF2DoctrineCrudHandler\Handler\EditHandler
     */
    function setEntityId($id) {
        $this->entityId = $id;
    
        return $this;
    }
    
    function getViewModel() {
        RequestHandler::handleDelete($this->objectManager, $this->entityNamespace, $this->entityId);
    }
}
