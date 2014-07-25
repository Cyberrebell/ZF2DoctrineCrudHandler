<?php
/**
 * File containing Module class
 * 
 * PHP version 5
 * 
 * @category  ZF2DoctrineCrudHandler
 * @package   ZF2DoctrineCrudHandler
 * @author    Cyberrebell <cyberrebell@web.de>
 * @copyright 2014 - 2014 Cyberrebell
 * @license   http://www.gnu.org/licenses/gpl-3.0 GPL-3.0
 * @version   GIT: <git_id>
 * @link      https://github.com/Cyberrebell/ZF2DoctrineCrudHandler
 */

namespace ZF2DoctrineCrudHandler;

/**
 * Module class for ZF2 Project
 * 
 * @category ZF2DoctrineCrudHandler
 * @package  ZF2DoctrineCrudHandler
 * @author   Cyberrebell <cyberrebell@web.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0 GPL-3.0
 * @link     https://github.com/Cyberrebell/ZF2DoctrineCrudHandler
 */
class Module
{
    /**
     * Setup crud templates
     * 
     * @return multitype:multitype:multitype:string
     */
    public function getConfig()
    {
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
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ]
            ]
        ];
    }
}
