<?php 
namespace Core;

class LazyCollection extends Collection{

    protected $removedObjects = null;

	protected $associatedObject = null;
    protected $annotation = null;
    protected $statement = null;
    protected $finder = null;

    public function __construct(){

        parent::__construct();
        $this->removedObjects = new Collection();

    }

    public function getPage($page, $limit){

        $query = $this->statement[0] . " SKIP {$page} LIMIT {$limit} "; 
        $params = $this->statement[1];
        
        $collection = $this->finder->getCollection($query, $params);
        if($collection === null){
            return new Collection();
        }

        return $collection;

    }

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

    public function remove($key){
        dd('The remove function does not work yet; please use removeElement');
    }

    public function removeElement($element){

        parent::removeElement($element);
        $this->removedObjects->add($element);

    }

    public function getRemovedObjects(){

        return $this->removedObjects;

    }

}