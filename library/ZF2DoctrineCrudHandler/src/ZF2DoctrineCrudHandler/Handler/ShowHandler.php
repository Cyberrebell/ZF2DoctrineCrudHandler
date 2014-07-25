<?php

namespace ZF2DoctrineCrudHandler\Handler;

use ZF2DoctrineCrudHandler\Reader\EntityReader;
use ZF2DoctrineCrudHandler\Reader\Property;
use Zend\View\Model\ViewModel;

class ShowHandler extends AbstractCrudHandler
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
        
        $this->selectDataToDisplay();
        
        $this->setupTemplate();
        $this->setupTitle();
        
        return $this->viewModel;
    }
    
    protected function selectDataToDisplay()
    {
        if ($this->entityId) {
            $entity = $this->objectManager->getRepository($this->entityNamespace)->find($this->entityId);
            if ($entity === null) {//check if entity with requested id exists
                $this->render404();
            } else {
                $useBlacklist = (count($this->propertyBlacklist) > 0) ? true : false;
                $useWhitelist = (count($this->propertyWhitelist) > 0) ? true : false;
                
                $properties = EntityReader::getProperties($this->entityNamespace);
                
                $dataToDisplay = [];
                foreach ($properties as $property) {
                    $name = $property->getName();
                    if (//handle black&whitelist
                        ($useWhitelist && !in_array($name, $this->propertyWhitelist))
                        || ($useBlacklist && in_array($name, $this->propertyBlacklist))
                    ) {
                        continue;
                    }
                    $getter = 'get' . ucfirst($name);
                    $value = $entity->$getter();
                    switch ($property->getType()) {
                        case Property::PROPERTY_TYPE_COLUMN:
                            break;
                        case Property::PROPERTY_TYPE_TOONE:
                            $targetEntity = $property->getTargetEntity();
                            $targetPropertsGetter = 'get' . ucfirst($targetEntity::DISPLAY_NAME_PROPERTY);
                            $value = $value->$targetPropertsGetter();
                            break;
                        case Property::PROPERTY_TYPE_TOMANY:
                            $targetEntity = $property->getTargetEntity();
                            $targetPropertsGetter = 'get' . ucfirst($targetEntity::DISPLAY_NAME_PROPERTY);
                            $listString = '';
                            foreach ($value as $targetEntity) {
                                $listString .= $targetEntity->$targetPropertsGetter() . ',';
                            }
                            $value = substr($listString, 0, -1);
                            break;
                        default:
                            continue 2;
                    }
                    $dataToDisplay[$name] = $value;
                }
                $this->viewModel->setVariable('dataToDisplay', $dataToDisplay);
            }
        } else {
            $this->render404();
        }
    }
}
