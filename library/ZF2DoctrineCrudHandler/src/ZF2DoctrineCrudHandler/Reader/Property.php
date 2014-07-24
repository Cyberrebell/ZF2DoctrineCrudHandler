<?php

namespace ZF2DoctrineCrudHandler\Reader;

class Property
{
    const PROPERTY_TYPE_COLUMN = 0;
    const PROPERTY_TYPE_TOONE = 1;
    const PROPERTY_TYPE_TOMANY = 2;
    
    protected $name;
    protected $annotation;
    protected $type = -1;
    protected $targetEntity;
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setAnnotation($annotation) {
        $this->annotation = $annotation;
    }
    
    public function getAnnotation() {
        return $this->annotation;
    }
    
    public function setType($type) {
        $this->type = $type;
    }
    
    public function getType() {
        return $this->type;
    }
    
    public function setTargetEntity($targetEntity) {
        $this->targetEntity = $targetEntity;
    }
    
    public function getTargetEntity() {
        return $this->targetEntity;
    }
}
