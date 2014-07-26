<?php
/**
 * File containing DeleteHandler class
 *
 * PHP version 5
 *
 * @category  ZF2DoctrineCrudHandler
 * @package   ZF2DoctrineCrudHandler\Handler
 * @author    Cyberrebell <cyberrebell@web.de>
 * @copyright 2014 - 2014 Cyberrebell
 * @license   http://www.gnu.org/licenses/gpl-3.0 GPL-3.0
 * @version   GIT: <git_id>
 * @link      https://github.com/Cyberrebell/ZF2DoctrineCrudHandler
 */

namespace ZF2DoctrineCrudHandler\Handler;

use Zend\View\Model\ViewModel;
use ZF2DoctrineCrudHandler\Request\RequestHandler;

/**
 * Crud-Handler to delete entity on request
 *
 * @category ZF2DoctrineCrudHandler
 * @package  ZF2DoctrineCrudHandler\Handler
 * @author   Cyberrebell <cyberrebell@web.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0 GPL-3.0
 * @link     https://github.com/Cyberrebell/ZF2DoctrineCrudHandler
 */
class DeleteHandler extends AbstractCrudHandler
{
    protected $entityId;
    
    /**
     * Constructor for AbstractDataHandler
     * 
     * @param \Zend\ServiceManager\ServiceManager $sm              ServiceManager
     * @param string                              $entityNamespace Namespace of Entity to do operations for
     */
    public function __construct(
        \Zend\ServiceManager\ServiceManager $sm,
        $entityNamespace
    ) {
        $this->serviceManager = $sm;
        $cfg = $this->serviceManager->get('Config');
        if (array_key_exists('crudhandler', $cfg)) {
            $crudCfg = $cfg['crudhandler'];
            if (array_key_exists('objectManager', $crudCfg)) {
                $this->entityNamespace = $entityNamespace;
                $this->objectManager = $this->serviceManager->get($crudCfg['objectManager']);
            } else {
                throw new \Exception('"objectManager" must be configurated in module.config -> "crudhandler"!');
            }
        } else {
            throw new \Exception('"crudhandler" is not configurated in module.config!');
        }
    }
    
    /**
     * Set the id of entity to delete
     * 
     * @param int $id Entity-Id
     * 
     * @return null
     */
    public function setEntityId($id)
    {
        $this->entityId = $id;
    }
    
    /**
     * Generates a ViewModel which is ready to render
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function getViewModel()
    {
        return RequestHandler::handleDelete($this->objectManager, $this->entityNamespace, $this->entityId);
    }
}
