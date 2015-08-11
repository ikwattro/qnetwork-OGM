<?php 
namespace Proxy;

/**
 * The value holder acts as a proxy for all domain objects that are
 * being pulled from persistence. This is not a stable version and it should
 * be improved; 
 */
class ValueHolder implements Proxy{

    protected $class = null;
	protected $wrapped = null;

    public function __construct($wrapped) {

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

        if($this->wrapped === null){
            // TODO: logic for pulling the wrapped object
            /*$unitOfWork = \App::make('QNetwork\Infrastructure\OGM\Core\UnitOfWork');
            $mapper = $unitOfWork->getMapper('QNetwork\Domain\EmailAddress');
            $this->wrapper = $mapper->getSingle($this->statement[0], $this->statement[1]);*/
        }

        return $this->wrapped;

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