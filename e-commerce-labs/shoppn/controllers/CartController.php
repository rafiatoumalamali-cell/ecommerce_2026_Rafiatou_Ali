<?php
require_once __DIR__ . '/../classes/CartClass.php';

class CartController
{
    private $cart;

    public function __construct()
    {
        $this->cart = new CartClass();
    }

    public function isInCart($productId, $ip)
    {
        return $this->cart->isInCart($productId, $ip);
    }

    public function addToCart($productId, $quantity, $ip, $customerId = null)
    {
        return $this->cart->addToCart($productId, $quantity, $ip, $customerId);
    }

    public function getCartItems($ip)
    {
        return $this->cart->getCartItems($ip);
    }

    public function getCartCount($ip)
    {
        return $this->cart->getCartCount($ip);
    }

    public function getCartTotal($ip)
    {
        return $this->cart->getCartTotal($ip);
    }

    public function validateCheckoutDetails($shipping)
    {
        return $this->cart->validateCheckoutDetails($shipping);
    }

    public function processPayment($customerId, $ip, $reference, $amount, $shipping, $cartSnapshot, $currency = 'GHS')
    {
        return $this->cart->processPayment($customerId, $ip, $reference, $amount, $shipping, $cartSnapshot, $currency);
    }

    public function getOrderById($orderId)
    {
        return $this->cart->getOrderById($orderId);
    }

    public function removeFromCart($productId, $ip)
    {
        return $this->cart->removeFromCart($productId, $ip);
    }

    public function updateQty($productId, $quantity, $ip)
    {
        return $this->cart->updateQty($productId, $quantity, $ip);
    }
}
