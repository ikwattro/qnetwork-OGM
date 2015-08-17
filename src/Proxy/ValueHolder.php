<?php 
namespace Proxy;

/**
 * The value holder acts as a proxy for all domain objects that are
 * being pulled from persistence. This is not a stable version and it should
 * be improved; 
 */
class ValueHolder implements Proxy{

	protected $wrapped = null;

    protected $associatedObject = null;

    protected $annotation = null;

    protected $statement = null;

    protected $finder = null;

    public function __construct($wrapped = null) {

        $this->wrapped = $wrapped;

    }

    /**
     * Initializes this proxy if its not yet initialized.
     *
     * Acts as a no-op if already initialized.
     *
     * @return void
     */
    public function __load(){
        
        if( ! $this->__isInitialized() ){
            
            if($this->finder === null){
                throw new OGMException('The finder has to be set for every proxy not initialized.');
            }

            $this->wrapped = $this->finder->getSingle($this->statement[0], $this->statement[1]);
            
        }
        
        return $this->wrapped;

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

    /**
     * Returns whether this proxy is initialized or not.
     *
     * @return bool
     */
    public function __isInitialized(){

        if( $this->wrapped ){
            return true;
        }

        return false;

    }

    public function __call($method, $args) {

    	$wrapped = $this->__load();
        return call_user_func_array([$this->wrapped, $method], $args);

    }

    public function __toString(){

    	$wrapped = $this->__load();
    	return (string) $this->wrapped;

    }
    
    public function __toInt(){

    	$wrapped = $this->__load();
    	return (int) $this->wrapped;

    }

}