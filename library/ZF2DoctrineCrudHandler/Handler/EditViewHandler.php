<?php

namespace ZF2DoctrineCrudHandler\Handler;

use Zend\View\Model\ViewModel;

class EditViewHandler extends AbstractFormViewHandler
{
    const DEFAULT_TEMPLATE = 'portalbasics/crudlist/edit.phtml';
    
    protected $entityId;
    
    function setEntityId($id) {
        $this->entityId = $id;
        
        return $this;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Portalbasics\Model\CrudList\AbstractCrudHandler::getViewModel()
     */
    function getViewModel() {
        $this->viewModel = new ViewModel();
        
        $this->createForm();
        $this->viewModel->setVariable('form', $this->form);
        
        $this->handleRequest();
        
        $this->setupTemplate();
        $this->setupTitle();
        
        return $this->viewModel;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Portalbasics\Model\CrudList\AbstractFormViewHandler::handleRequest()
     */
    protected function handleRequest() {
        if ($this->entityId) {
            if ($this->request->isPost()) {
                $this->form->setData($this->request->getPost());
                if ($this->form->isValid()) {
                    $data = $this->form->getData();
                    
                    $entity = $this->objectManager->getRepository($this->entityNamespace)->find($this->entityId);
                    if ($entity === NULL) { //check if entity with requested id exists
                        $this->render404();
                    }
                    foreach ($this->form->getElements() as $element) {
                        $property = $element->getName();
                        $setter = 'set' . ucfirst($property);
                        
                        $elementType = $element->getAttribute('type');
                        if ($elementType == 'submit') {
                            continue;   //dont save submit value
                        } else if ($elementType == 'password' && $this->form->has(substr($property, 0, -1))) {
                            continue;  //avoid setPassword2()
                        } else if ($elementType == 'select') {
                            $reflectionProperty = new \ReflectionProperty($this->entityNamespace, $property);
                            $targetAnnotations = $this->getAnnotationReader()->getPropertyAnnotations($reflectionProperty);
                            $type = $this->getPropertyType($targetAnnotations);
                            $targetEntityAnnotation = $targetAnnotations[reset($type)];
                            $targetEntityNamespace = $targetEntityAnnotation->targetEntity;
                            
                            $selectedEntity = $this->objectManager->getRepository($targetEntityNamespace)->find($element->getValue());
                            $entity->$setter($selectedEntity);
                        } else {
                            $entity->$setter($data[$property]);
                        }
                    }
                    $this->objectManager->persist($entity);
                    $this->objectManager->flush();
                }
            } else {
                $this->loadOldFormData();
            }
        } else {
            $this->render404();
        }
    }
    
    /**
     * Load old data from db and put them into form
     * @return boolean
     */
    protected function loadOldFormData() {
        $entity = $this->objectManager->getRepository($this->entityNamespace)->find($this->entityId);
        if ($entity === NULL) { //check if entity with requested id exists
            return false;
        }
        
        $elements = $this->form->getElements();
        foreach ($elements as $element) {
            $property = $element->getName();
            $getter = 'get' . ucfirst($property);
            
            $elementType = $element->getAttribute('type');
            if ($elementType == 'submit') {
                continue;   //dont save submit value
            } else if ($elementType == 'password' && $this->form->has(substr($property, 0, -1))) {
                continue;  //avoid setPassword2()
            } else if ($elementType == 'select') {
                $oldValueEntity = $entity->$getter();
                $element->setValue($oldValueEntity->getId());
            } else {
                $element->setValue($entity->$getter());
            }
        }
    }
}
