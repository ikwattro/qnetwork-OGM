<?php 
namespace Core;

class ObjectState {

	const __default = self::STATE_NEW;
    
    const STATE_NEW = 'NEW';
    const STATE_CLEAN = 'CLEAN';
    const STATE_DIRTY = 'DIRTY';
    const STATE_REMOVED = 'REMOVED';

}