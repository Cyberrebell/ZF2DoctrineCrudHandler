<?php

namespace ZF2DoctrineCrudHandler\Handler;

use Zend\View\Model\ViewModel;

class DeleteHandler extends AbstractCrudHandler
{
    protected $entityId;
    
    function getViewModel() {
        if ($this->entityId) {
            $entity = $this->objectManager->getRepository($this->entityNamespace)->find($this->entityId);
            if ($entity === NULL) { //check if entity with requested id exists
                $this->render404();
            } else {
                $this->objectManager->remove($entity);
                $this->objectManager->flush();
                return true;
            }
        } else {
            $this->render404();
        }
    }
    
    function setEntityId($id) {
        $this->entityId = $id;
        
        return $this;
    }
}
