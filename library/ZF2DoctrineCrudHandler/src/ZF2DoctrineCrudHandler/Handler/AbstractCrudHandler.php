<?php

namespace ZF2DoctrineCrudHandler\Handler;

use Zend\ServiceManager\ServiceManager;
use Doctrine\Common\Persistence\ObjectManager;
use ZF2DoctrineCrudHandler\Cache\RecacheAgent;

/**
 * Abstract Class for Crud-Handlers Offers some configuration options all Handlers need
 *
 * @author   Cyberrebell <chainsaw75@web.de>
 */
abstract class AbstractCrudHandler
{
	const DEFAULT_TEMPLATE = '';
	
	protected $serviceManager;
	protected $objectManager;
	protected $storageAdapter;
	protected $recacheAgent;
	protected $entityNamespace;
	protected $viewModel;
	protected $useCustomTemplate = false;
	protected $title;
	protected $propertyBlacklist = [];
	protected $propertyWhitelist = [];
	protected $entityFilter;
	protected $useCache;
	
	/**
	 * Constructor for AbstractCrudHandler
	 *
	 * @param \Zend\ServiceManager\ServiceManager $sm			  ServiceManager
	 * @param string $entityNamespace Namespace of Entity to do operations for
	 */
	public function __construct(ServiceManager $sm, ObjectManager $om, $entityNamespace) {
		$this->serviceManager = $sm;
		$this->objectManager = $om;
		$this->entityNamespace = $entityNamespace;
		$this->prepare();
	}
	
	/**
	 * Generates a ViewModel which is ready to render
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
	abstract public function getViewModel();
	
	/**
	 * setup viewmodel to use the action-related view template
	 * else CrudList uses its default template you can style as you need using css
	 * 
	 * @return null
	 */
	public function useCustomTemplate() {
		$this->useCustomTemplate = true;
	}
	
	/**
	 * Set a title to display in view
	 * Default title will be the entity-name
	 * 
	 * @param string $title simple string
	 * @return null
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	
	/**
	 * Blacklist Entity-Properties for view-generation
	 * 
	 * @param array $blacklist ['password', 'registrationDate']
	 * @return null
	 */
	public function setPropertyBlacklist(array $blacklist) {
		$this->propertyBlacklist = $blacklist;
	}
	
	/**
	 * Whitelist Entity-Properties for form-generation
	 * 
	 * @param array $whitelist ['name', 'age']
	 * @return null
	 */
	public function setPropertyWhitelist(array $whitelist) {
		$this->propertyWhitelist = $whitelist;
	}
	
	/**
	 * Set custom filter for special purposes (e.g. access control by entity relations)
	 * The Closure should return true if the entity should be used for view-generation
	 * 
	 * @param \Closure $filter function($entity){ ... }
	 * @return null
	 */
	public function setEntityFilter(\Closure $filter) {
		$this->entityFilter = $filter;
	}
	
	protected function prepare() {
		
	}
	
	/**
	 * 
	 * @param object $entity Doctrine-Entity
	 * 
	 * @return object|boolean
	 */
	protected function filterEntity($entity) {
		$filter = $this->entityFilter;
		if ($filter != null && $filter($entity) === false) {
			return false;
		} else {
			return $entity;
		}
	}

	/**
	 * Setup CrudList-Default-Template if custom use is not set
	 * 
	 * @return null
	 */
	protected function setupTemplate() {
		if ($this->useCustomTemplate == false) {
			$this->viewModel->setTemplate($this::DEFAULT_TEMPLATE);
		}
	}
	
	/**
	 * Set Title into ViewModel, extract Entity-Classname if no title set
	 * 
	 * @return null
	 */
	protected function setupTitle() {
		if ($this->title === null) {
			$segments = explode('\\', $this->entityNamespace);
			$this->title = end($segments);
		}
		$this->viewModel->setVariable('title', $this->title);
	}
	
	/**
	 * Switch ViewModel to render 404 template
	 * 
	 * @return null
	 */
	protected function render404() {
		
	}
	
	protected function initRecacheAgent() {
		$eventManager = $this->objectManager->getEventManager();
		$this->recacheAgent = new RecacheAgent($eventManager, $this->storageAdapter);
	}
}
