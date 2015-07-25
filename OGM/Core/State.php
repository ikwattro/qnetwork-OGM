<?php 
namespace QNetwork\Infrastructure\OGM\Core;

class State {

	const __default = self::_NEW;
    
    const _NEW = 'NEW';
    const CLEAN = 'CLEAN';
    const DIRTY = 'DIRTY';
    const REMOVED = 'REMOVED';

}