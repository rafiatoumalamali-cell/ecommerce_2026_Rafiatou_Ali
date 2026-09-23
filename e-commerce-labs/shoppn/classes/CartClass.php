<?php
require_once __DIR__ . '/../core/db_class.php';

class CartClass extends DbClass
{
    public function addToCart($productId = 0, $quantity = 1)
    {
        return ['status' => 'not_implemented'];
    }

    public function removeFromCart($productId = 0)
    {
        return ['status' => 'not_implemented'];
    }

    public function updateQty($productId = 0, $quantity = 1)
    {
        return ['status' => 'not_implemented'];
    }

    public function getTotal()
    {
        return 0;
    }
}
