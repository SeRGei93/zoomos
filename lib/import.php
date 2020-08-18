<?php

namespace ZMS;



class Import
{
    private $categories;
    private $properties;
    private $products;


    public function __construct()
    {

        $this->categories = new Bx\Section();
        $this->properties = new Bx\Property();

        $this->products = new Bx\Product();
    }

    public function start()
    {
        $this->categories->startUpdate();
        $this->products->startUpdate($this->categories->allBXVendor);
    }


}