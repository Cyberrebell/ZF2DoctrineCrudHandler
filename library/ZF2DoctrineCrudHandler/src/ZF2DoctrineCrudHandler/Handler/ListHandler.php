<?php

namespace ZF2DoctrineCrudHandler\Handler;

use DoctrineEntityReader\EntityReader;
use Zend\View\Model\ViewModel;

class ListHandler extends AbstractDataHandler
{
	const DEFAULT_TEMPLATE = 'zf2doctrinecrudhandler/list.phtml';
	
	protected $listIcons = [];
	protected $listHeadIcons = [];
	protected $criteria = [];
	
	/**
	 * (non-PHPdoc)
	 * @see \Portalbasics\Model\CrudList\AbstractCrudHandler::getViewModel()
	 */
	function getViewModel() {
		if ($this->useCache) {
			$viewModel = $this->recacheAgent->getViewModel('list', $this->entityNamespace, '');
			if ($viewModel) {
				return $viewModel;
			}
		}
		
		$this->viewModel = new ViewModel();
		
		$entities = $this->objectManager->getRepository($this->entityNamespace)->findBy($this->criteria);
		
		$entitiesData = [];
		foreach ($entities as $entity) {
			$entityData = $this->getEntityData($entity->getId());
			if ($entityData !== false && $entityData !== null) {
				$entitiesData[$entity->getId()] = $entityData;
			}
		}
		
		$this->viewModel->setVariable('entitiesData', $entitiesData);
		
		$this->viewModel->setVariable('entityProperties', $this->getEntityProperties());
		
		if ($this->listIcons === null) {
			$this->listIcons = ['show' => '', 'edit' => '', 'delete' => ''];
		}
		$this->viewModel->setVariable('listIcons', $this->listIcons);
		$this->viewModel->setVariable('listHeadIcons', $this->listHeadIcons);
		
		$this->setupTemplate();
		$this->setupTitle();
		
		if ($this->useCache) {
			$this->recacheAgent->storeViewModel($this->viewModel, 'list', $this->entityNamespace, '');
		}
		
		return $this->viewModel;
	}
	
	/**
	 * @param array $criteria
	 * @return null
	 */
	function setCriteria(array $criteria) {
		$this->criteria = $criteria;
	}
	
	/**
	 * Add icons like ['show' => '/showuser', 'edit' => '/edituser', 'delete' => '/deleteuser'] to list
	 * 
	 * @param array $icons
	 * @return null
	 */
	function setIcons(array $icons) {
		$this->listIcons = $icons;
	}
	
	/**
	 * select icons to display only in <thead>
	 * 
	 * @param array $icons
	 * @return null
	 */
	function setHeadIcons(array $icons) {
		$this->listHeadIcons = $icons;
	}

	protected function getEntityProperties() {
		$useBlacklist = (count($this->propertyBlacklist) > 0) ? true : false;
		$useWhitelist = (count($this->propertyWhitelist) > 0) ? true : false;
		
		$properties = EntityReader::getProperties($this->entityNamespace);
		$filteredProperties = [];
		foreach ($properties as $property) {
			$name = $property->getName();
			if (//handle black&whitelist
			($useWhitelist && !in_array($name, $this->propertyWhitelist))
			|| ($useBlacklist && in_array($name, $this->propertyBlacklist))
			) {
				continue;
			}
			$filteredProperties[] = $property->getName();
		}
		return $filteredProperties;
	}
}
