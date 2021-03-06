<?php

namespace App\Repositories\Shop;

use App\Shop;
use Illuminate\Http\Request;
use App\Events\Shop\ShopDeleted;
use App\Repositories\BaseRepository;
use App\Repositories\EloquentRepository;

class EloquentShop extends EloquentRepository implements BaseRepository, ShopRepository
{
    protected $model;

    public function __construct(Shop $shop)
    {
        $this->model = $shop;
    }

    public function all()
    {
        return $this->model->with(
            'config:shop_id,maintenance_mode',
            'owner.image',
            'plan:name,plan_id',
            'logoImage',
            'primaryAddress'
        )->get();
    }

    public function trashOnly()
    {
        return $this->model->with('logo')->onlyTrashed()->get();
    }

    public function staffs($shop)
    {
        return $shop->staffs()->with('role', 'primaryAddress')->get();
    }

    public function staffsTrashOnly($shop)
    {
        return $shop->staffs()->onlyTrashed()->get();
    }

    public function update(Request $request, $id)
    {
        $shop = parent::update($request, $id);

        return $shop;
    }

    public function destroy($id)
    {
        $shop = parent::findTrash($id);

        $shop->flushAddresses();

        $shop->staffs()->forceDelete();

        if ($shop->hasFeedbacks()) {
            $shop->flushFeedbacks();
        }

        $shop->flushImages();

        return $shop->forceDelete();
    }

    public function saveAdrress(array $address, $shop)
    {
        $shop->addresses()->create($address);
    }


    public function massTrash($ids)
    {
        $shops = $this->model->withTrashed()->whereIn('id', $ids)->get();

        foreach ($shops as $shop) {
            $shop->owner()->delete();
            $shop->staffs()->delete();

            event(new ShopDeleted($shop->id));
        }

        return parent::massTrash($ids);
    }

    public function massDestroy($ids)
    {
        $shops = $this->model->withTrashed()->whereIn('id', $ids)->get();

        foreach ($shops as $shop) {
            $shop->flushAddresses();
            $shop->staffs()->forceDelete();
            $shop->flushFeedbacks();
            $shop->flushImages();
        }

        return parent::massDestroy($ids);
    }

    public function emptyTrash()
    {
        $shops = $this->model->onlyTrashed()->get();

        foreach ($shops as $shop) {
            $shop->flushAddresses();
            $shop->staffs()->forceDelete();
            $shop->flushFeedbacks();
            $shop->flushImages();
        }

        return parent::emptyTrash();
    }
}
