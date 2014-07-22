<?php

namespace ZF2DoctrineCrudHandler\Handler;

use Zend\View\Model\ViewModel;

class AddViewHandler extends AbstractFormViewHandler
{
    const DEFAULT_TEMPLATE = 'portalbasics/crudlist/add.phtml';
    
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
    
    protected function handleRequest() {
        if ($this->request->isPost()) {
            $this->form->setData($this->request->getPost());
            if ($this->form->isValid()) {
                $data = $this->form->getData();
                
                $entityNamespace = $this->entityNamespace;
                $entity = new $entityNamespace();
                
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
        }
    }
}
