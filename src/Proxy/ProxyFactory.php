<?php 
namespace Proxy;

class ProxyFactory {

	/**
	 * The domain object that the proxy represents and an initializer(closure) that
	 * knows how to initialize the domain object when being accessed;
	 *
	 * @param DomainObject
	 * @param function
	 * @return Proxy
	 */
	public function createFromDomainObject($class, $initializer){

		$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory();
		
		/* Initializer example:
		 $initializer = 
			function (& $wrappedObject, $proxy, $method, $parameters, & $initializer) {
        		$wrappedObject = new User(new EmailAddress('test@domain.com'), new Password('123123')); // instantiation logic here
        		$initializer   = null; // turning off further lazy initialization
    		};
    	*/
    	
		$proxy = $factory->createProxy($class, $initializer);
		return $proxy;

	}
}