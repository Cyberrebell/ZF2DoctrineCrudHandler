<?php

namespace ZF2DoctrineCrudHandler\Handler;

use Zend\View\Model\ViewModel;

class ShowViewHandler extends AbstractCrudHandler
{
    const DEFAULT_TEMPLATE = 'zf2doctrinecrudhandler/show.phtml';
    
    protected $entityId;
    protected $propertyBlacklist = [];
    
    /**
     * (non-PHPdoc)
     * @see \Portalbasics\Model\CrudList\AbstractCrudHandler::getViewModel()
     */
    function getViewModel() {
        $this->viewModel = new ViewModel();
        
        $this->selectDataToDisplay();
        
        $this->setupTemplate();
        $this->setupTitle();
        
        return $this->viewModel;
    }
    
    function setEntityId($id) {
        $this->entityId = $id;
        
        return $this;
    }
    
    function setPropertyBlacklist(array $properties) {
        $this->propertyBlacklist = $properties;
    }
    
    protected function selectDataToDisplay() {
        if ($this->entityId) {
            $entity = $this->objectManager->getRepository($this->entityNamespace)->find($this->entityId);
            if ($entity === NULL) { //check if entity with requested id exists
                $this->render404();
            } else {
                $this->propertyBlacklist[] = 'id';

                $dataToDisplay = [];    //data to display in view
                
                $reflectionClass = new \ReflectionClass($this->entityNamespace);
                $entityProperties = $reflectionClass->getProperties();
                foreach ($entityProperties as $property) {
                    if (!in_array($property->name, $this->propertyBlacklist)) {
                        $getter = 'get' . ucfirst($property->name);
                        $result = $entity->$getter();
                        if (is_object($result)) {
                            $annotations = $this->getAnnotationReader()->getPropertyAnnotations($property);
                            $targetEntityNamespace = false;
                            foreach ($annotations as $annotation) {
                                $className = get_class($annotation);
                                if ($className == 'Doctrine\ORM\Mapping\OneToOne'
                                    || $className == 'Doctrine\ORM\Mapping\ManyToOne'
                                    || $className == 'Doctrine\ORM\Mapping\ManyToMany'
                                    || $className == 'Doctrine\ORM\Mapping\OneToMany')
                                {
                                    $targetEntityNamespace = $annotation->targetEntity;
                                }
                            }
                            if ($targetEntityNamespace) {
                                $identifyer = $targetEntityNamespace::DISPLAY_NAME_PROPERTY;
                                $identifyGetter = 'get' . ucfirst($identifyer);
                                $result = $result->$identifyGetter();
                            } else {
                                throw new \Exception('Entity "' . $this->entityNamespace . '" has no targetEntity at "' . $property->name . '" property!');
                            }
                        }
                        $dataToDisplay[$property->name] = $result;
                    }
                }
                $this->viewModel->setVariable('dataToDisplay', $dataToDisplay);
            }
        } else {
            $this->render404();
        }
    }
}
