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
    
    protected $xml = null;
    protected $orders = Array();
    
    // Constructor
    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('Menu');
        $rawOrders = $this->getRawOrders();
        
        foreach($rawOrders as $r)
        {
            $this->xml      = simplexml_load_file(DATAPATH . $r);
            //$this->orders[] = Array(
            //    'ordernum'  => pathinfo($r)['filename'],
            //    'customer'  => (string) $this->xml->customer
            //);
            
            // set up the order
            $orderTotal     = 0.0;
            $burgerCount    = 0;
            $receipt = Array();
            $receipt['ordernum'] = pathinfo($r)['filename'];
            $receipt['customer'] = (string) $this->xml->customer;
            $receipt['type'] = (string)$this->xml['type'];
            $receipt['burgers'] = Array();
            $receipt['special'] = '';
            
            if(isset($this->xml->special))
            {
                $receipt['special'] = 'Special Instructions: ' . (string)$this->xml->special;
            }
            
            foreach($this->xml->burger as $burger)
            {
                $burgerTotal = 0.0;
                
                $newBurger = Array();
                $newBurger['burgernum'] = ++$burgerCount;
                
                // get patty
                $patty = $this->Menu->getPatty((string)$burger->patty['type']);
                $newBurger['patty'] = (string)$patty->name;
                
                //patty total
                $burgerTotal += (float)$patty->price;
                $orderTotal     += (float)$patty->price;
                
                // get cheeses
                $cheeses = '';
                $cheeseOnTop = false;
                if(isset($burger->cheeses))
                {
                    // check for top cheese
                    if(isset($burger->cheeses['top']))
                    {
                        $cheeseOnTop = true;
                        $topCheese = $this->Menu->getCheese((string)$burger->cheeses['top']);
                        $cheeses .= (string)$topCheese->name . ' (top)';
                        
                        // total top cheese price
                        $burgerTotal += (float)$topCheese->price;
                        $orderTotal += (float)$topCheese->price;
                    }
                    
                    // check for bottom cheese
                    if(isset($burger->cheeses['bottom']))
                    {
                        if($cheeseOnTop)
                        {
                            $cheeses .= ', ';
                        }
                        
                        $bottomCheese = $this->Menu->getCheese((string)$burger->cheeses['bottom']);
                        $cheeses .= (string)$bottomCheese->name . ' (bottom)';
                        
                        // total top cheese price
                        $burgerTotal += (float)$bottomCheese->price;
                        $orderTotal += (float)$bottomCheese->price;
                    }
                }
                
                $newBurger['cheeses'] = $cheeses;
                
                // get toppings
                $toppings = '';
                $previousTopping = false;
                if(isset($burger->topping))
                {
                    foreach($burger->topping as $t)
                    {
                        if($previousTopping)
                        {
                            $toppings .= ', ';
                        }
                        
                        $previousTopping = true;
                        $topping = $this->Menu->getTopping((string)$t['type']);
                        $toppings .= $topping->name;
                        
                        // total toppings
                        $burgerTotal += (float)$topping->price;
                        $orderTotal += (float)$topping->price;
                    }
                }
                else
                {
                    $toppings = 'none';
                }
                
                $newBurger['toppings'] = $toppings;
                
                // get sauces
                $sauces = '';
                $previousSauce = false;
                if(isset($burger->sauce))
                {
                    foreach($burger->sauce as $s)
                    {
                        if($previousSauce)
                        {
                            $sauces .= ', ';
                        }
                        
                        $previousSauce = true;
                        $sauce = $this->Menu->getSauce((string)$s['type']);
                        $sauces .= (string)$sauce->name;
                    }
                }
                else
                {
                    $sauces = 'none';
                }
                
                $newBurger['sauces'] = $sauces;
                
                // check for instructions
                $instructions = 'none';
                if(isset($this->xml->burger->instructions))
                {
                    $instructions = (string)$this->xml->burger->instructions;
                }
                
                $newBurger['instructions'] = $instructions; 
                
                // final assignments
                $newBurger['total'] = $burgerTotal;
                $receipt['burgers'][] = $newBurger;
            }
            
            $receipt['ordertotal'] = $orderTotal;
            $this->orders[] = $receipt;
        }
    }
    
    public function getOrders()
    {
        return $this->orders;
    }
    
    public function getSingleOrder($orderNum)
    {
        foreach($this->orders as $order)
        {
            if($order['ordernum'] == $orderNum)
            {
                return $order;
            }
        }
        
        return NULL;
    }
    
    private function getRawOrders()
    {
        $this->load->helper('directory');
        $dir = directory_map('data', 1, TRUE);
        
        $orderFiles = Array();
        foreach($dir as $d)
        {
            if(preg_match('#^order.*.xml$#', $d) == 1)
            {
                $orderFiles[] = $d;
            }
        }
        
        return $orderFiles;
    }

}
