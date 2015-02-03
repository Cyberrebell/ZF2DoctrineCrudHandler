<?php

namespace ZF2DoctrineCrudHandler;

/**
 * Module class for ZF2 Project
 * 
 * @author   Cyberrebell <chainsaw75@web.de>
 */
class Module
{
	/**
	 * Setup crud templates
	 * 
	 * @return multitype:multitype:multitype:string
	 */
	function getConfig() {
		return [
			'view_manager' => [
				'template_path_stack' => [
					__DIR__ . '/view'
				]
			]
		];
	}

	/**
	 * Setup autoloading
	 * 
	 * @return multitype:multitype:multitype:string
	 */
	function getAutoloaderConfig() {
		return [
			'Zend\Loader\StandardAutoloader' => [
				'namespaces' => [
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				]
			]
		];
	}
}
