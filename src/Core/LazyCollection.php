<?php 
namespace Core;

class LazyCollection extends Collection{

	protected $associatedObject = null;
    protected $annotation = null;
    protected $statement = null;
    protected $finder = null;

    public function __setFinder($finder){

        $this->finder = $finder;

    }

    public function __setAssociatedObject($object){

        $this->associatedObject = $object;

    }

    public function __setAnnotation($annotation){

        $this->annotation = $annotation;

    }

    public function __setStatementToGetValue($statement){

        $this->statement = $statement;

    }	
}