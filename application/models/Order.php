<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Order
 *
 * @author william
 */
class Order extends CI_Model {
    public $burgers = array();
    protected $xml = null;
    public $customer;
    public $type;
    public $instructions;
    public $total;
    
    // Constructor
    public function __construct($filename = null) {
        parent::__construct();
        if ($filename == null)
        {
            return;
        }
        $burgerCount = 0;
        
        $this->load->model('menu');
        $this->xml = simplexml_load_file(DATAPATH . $filename);
        
        // Retrieve name and order type
        $this->customer = (string) $this->xml->customer;
        $this->type = (string) $this->xml['type'];
        
        // Assign the order instructions
        if (isset($this->xml->instructions))
            $this->$instructions = (string) $this->xml->$instructions;
            
        foreach ($this->xml->burger as $burger) 
        {
            $burgerCount++;
            $burga['num'] = $burgerCount;
            //Initialize values
            $cheeses = "";
            $toppings = "";
            $sauces = "";
            
            $burga = array(
                'patty' => $burger->patty['type']
            );
            $burga['num'] = $burgerCount;
            
            // Assign cheeses
            if (isset($burger->cheeses['top']))
                $cheeses .= $burger->cheeses['top'] . "(top), ";
            
            if (isset($burger->cheeses['bottom']))
                $cheeses .= $burger->cheeses['bottom'] . "(bottom)";
            $burga['cheese'] = $cheeses;
            
            // Assign toppings
            if (!isset($burger->topping))
                $toppings .= "none";    
            
            foreach($burger->topping as $topping) {
                $toppings .= $topping['type'] . ", ";
            }
            $burga['toppings'] = $toppings;
            
            
            // Assign sauces
            if (!isset($burger->sauce))
                $sauces .= "none";    
            
            foreach($burger->sauce as $sauce) {
                $sauces .= $sauce['type'] . ", ";
            }
            $burga['sauces'] = $sauces;
            
            // Assign instructions
            if (isset($burger->instructions))
                $burga['instructions'] = (string) $burger->instructions;
            else
                $burga['instructions'] = "None";
            
            // Assign costs
            $cost = $this->burgerCost($burger);
            
            $burga['cost'] = $cost;
            $this->total += $cost;       
            $this->burgers[] = $burga;
        }
    }
    
    // Function used to calculate the cost of each burger
    // menu.xml file used for pricing of each component
    private function burgerCost($burger)
    {
        $burgerAmount = 0.00;
        
        // Calculate patty price
        $burgerAmount += $this->menu->getPatty((string) $burger->patty['type'])->price;
        
        // Calculate cheese price
        if (isset($burger->cheeses['top']))
            $burgerAmount += $this->menu->getCheese((string) $burger->cheeses['top'])->price; 
        
        if (isset($burger->cheeses['bottom']))
            $burgerAmount += $this->menu->getCheese((string) $burger->cheeses['bottom'])->price; 
        
        // Calculate topping price
        foreach ($burger->topping as $topping) {
            $burgerAmount += $this->menu->getTopping((string) $topping['type'])->price; 
        }
        
        // Calculate sauce price
        foreach ($burger->sauce as $sauce) {
            $burgerAmount += $this->menu->getSauce((string) $sauce['type'])->price; 
        }
        
        return $burgerAmount;
    }
}
