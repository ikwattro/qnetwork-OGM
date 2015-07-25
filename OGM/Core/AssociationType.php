<?php 
namespace QNetwork\Infrastructure\OGM\Core;

class AssociationType {

	const __default = self::ONE_TO_ONE;
    
    const ONE_TO_ONE = 'one-to-one';
    const ONE_TO_MANY = 'one-to-many';

}