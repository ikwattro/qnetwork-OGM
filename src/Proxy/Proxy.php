<?php 
namespace Proxy;
use Doctrine\Common\Proxy\Proxy as DoctrineProxy;

interface Proxy {

	/**
     * Initializes this proxy if its not yet initialized.
     *
     * Acts as a no-op if already initialized.
     *
     * @return void
     */
    public function __load();

    /**
     * Returns whether this proxy is initialized or not.
     *
     * @return bool
     */
    public function __isInitialized();
    
}