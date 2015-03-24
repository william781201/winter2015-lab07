<?php

/**
 * This is a "CMS" model for quotes, but with bogus hard-coded data.
 * This would be considered a "mock database" model.
 *
 * @author jim
 */
class Quotes extends MY_Model {

    // Constructor
    public function __construct() {
       parent::__construct('quotes', 'id');	
    }
    
    // retrieve the most recently added quote
    function last() {
	$key = $this->highest();
	return $this->get($key);
    }
}
