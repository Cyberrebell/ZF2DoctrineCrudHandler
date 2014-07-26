<?php

namespace ZF2DoctrineCrudHandler\Cache;

use Doctrine\ORM\Events;

class RecacheAgent
{
    const STORAGE_INFORMATION_PREFIX = 'crud_recache_';
    
    protected $eventManager;
    
    protected $storageAdapter;
    
    public function __construct(\Doctrine\Common\EventManager $eventManager, \Zend\Cache\Storage\Adapter\AbstractAdapter $storageAdapter) {
        $this->eventManager = $eventManager;
        $this->storageAdapter = $storageAdapter;
        
        $this->eventManager->addEventListener([Events::onFlush], $this);
    }
    
    /**
     * 
     * @param string $handler list/show/add/edit
     * @param string $entityNamespace Entity-Namespace
     * @param string $ident Can be Entity-Id or pagination-data
     */
    public function getViewModel($handler, $entityNamespace, $ident) {
        $cacheKey = md5($handler . '|' . $entityNamespace . '|' . $ident);
        $data = $this->storageAdapter->getItem($cacheKey);
        return unserialize($data);
    }
    
    /**
     * 
     * @param \Zend\View\Model\ViewModel $viewModel
     * @param string $handler list/show/add/edit
     * @param string $entityNamespace Entity-Namespace
     * @param string $ident Can be Entity-Id or pagination-data
     */
    public function storeViewModel(\Zend\View\Model\ViewModel $viewModel, $handler, $entityNamespace, $ident) {
        $data = serialize($viewModel);
        $cacheKey = md5($handler . '|' . $entityNamespace . '|' . $ident);

        //store map of entityNamespace related cache-keys (required for recaching)
        $metaKey = md5($this::STORAGE_INFORMATION_PREFIX . $entityNamespace);
        $entityMap = $this->storageAdapter->getItem($metaKey);
        if ($entityMap) {
            $entityMap = unserialize($entityMap);
        } else {
            $entityMap = [];
        }
        $entityMap[] = $cacheKey;
        $entityMap = serialize($entityMap);
        $this->storageAdapter->setItem($metaKey, $entityMap);
        
        $this->storageAdapter->setItem($cacheKey, $data);
    }
    
    /**
     * Doctrine-Event to clear cache on changes
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $eventArgs
     */
    public function onFlush(\Doctrine\ORM\Event\OnFlushEventArgs $eventArgs) {
        $reCacheEntities = [];
        
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $reCacheEntities[get_class($entity)] = true;
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $reCacheEntities[get_class($entity)] = true;
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $reCacheEntities[get_class($entity)] = true;
        }

        foreach ($uow->getScheduledCollectionDeletions() as $col) {
            $reCacheEntities[get_class($entity)] = true;
        }

        foreach ($uow->getScheduledCollectionUpdates() as $col) {
            $reCacheEntities[get_class($entity)] = true;
        }
        
        foreach ($reCacheEntities as $entityNamespace => $flag) {
            $this->recache($entityNamespace);
        }
    }
    
    protected function recache($entityNamespace) {
        $metaKey = md5($this::STORAGE_INFORMATION_PREFIX . $entityNamespace);
        $entityMap = $this->storageAdapter->getItem($metaKey);
        if ($entityMap) {
            $entityMap = unserialize($entityMap);
            
            foreach ($entityMap as $cacheKey) {
                $this->storageAdapter->removeItem($cacheKey);
            }
            $this->storageAdapter->removeItem($metaKey);
        }
    }
}
