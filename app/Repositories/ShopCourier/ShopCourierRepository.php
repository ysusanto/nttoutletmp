<?php

namespace App\Repositories\ShopCourier;

interface ShopCourierRepository
{
    public function allCourier();
    public function storedata($data);
    public function getShopCourier($shop_id);
    public function getShopCourierbycourier($shop_id,$courier_id);
}
