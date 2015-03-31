<?php

/**
 * Our homepage. Show the most recently added quote.
 * 
 * controllers/Welcome.php
 *
 * ------------------------------------------------------------------------
 */
class Welcome extends Application {

    function __construct()
    {
	parent::__construct();
        $this->load->helper('directory');
        $this->load->model('Order');
    }

    //-------------------------------------------------------------
    //  Homepage: show a list of the orders on file
    //-------------------------------------------------------------

    function index()
    {
	// Build a list of orders
	$dir = directory_map('../data/');
        $files = array();
        
        foreach ($dir as $file) {
            if (strpos($file, 'Order') !== false && strpos($file, '.xml') !== false) {        
                $order = new Order($file);
                $files[] = array(
                    'fileName' => substr($file, 0, strlen($file) - 4),
                    'customer' => $order->customer);
            }
        }
        
	$this->data['orderInfo'] = $files;
	// Present the list to choose from
	$this->data['pagebody'] = 'homepage';
	$this->render();
    }
    
    //-------------------------------------------------------------
    //  Show the "receipt" for a specific order
    //-------------------------------------------------------------

    function order($filename)
    {
	// Build a receipt for the chosen order
	$order = new Order($filename . '.xml');
        
	$this->data['filename'] = $filename;
        $this->data['customer'] = $order->customer;
        $this->data['type'] = $order->type;
        $this->data['burgers'] = $order->burgers;
        $this->data['total'] = $order->total;
        $this->data['instructions'] = $order->instructions;
	// Present the list to choose from
	$this->data['pagebody'] = 'justone';
	$this->render();
    }
    

}
