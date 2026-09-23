<?php
require_once __DIR__ . '/../classes/CartClass.php';

class CartController
{
    private $cart;

    public function __construct()
    {
        $this->cart = new CartClass();
    }

    public function addToCart($productId = 0, $quantity = 1)
    {
        return $this->cart->addToCart($productId, $quantity);
    }

    public function removeFromCart($productId = 0)
    {
        return $this->cart->removeFromCart($productId);
    }

    public function updateQty($productId = 0, $quantity = 1)
    {
        return $this->cart->updateQty($productId, $quantity);
    }

    public function getTotal()
    {
        return $this->cart->getTotal();
    }
}
