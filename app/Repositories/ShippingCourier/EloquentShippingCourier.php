<?php

namespace App\Repositories\ShippingCourier;

use Auth;
use App\CourierRo;

use Illuminate\Http\Request;
use App\Repositories\BaseRepository;
use App\Repositories\EloquentRepository;
use stdClass;

// add by ari 01062021
class EloquentShippingCourier extends EloquentRepository implements BaseRepository, ShippingCourierRepository
{
    protected $model;

    public function __construct(CourierRo $courier_ro)
    {
        $this->model = $courier_ro;
    }
    public function courier($id)
    {
        $courier = $this->model->findOrFail($id);
        // echo json_encode($courier);die();
        $courier->parent = $this->model->where('id', $courier->parent_id)->select('name')->first()->name;
        $courier->parentcode = $this->model->where('id', $courier->parent_id)->select('code')->first()->code;
        return $courier;
    }

    public function all()
    {
        $allcourier = array();
        $parent = $this->model->where('parent_id', '>', 0)->where("is_active","=","1")->orderBy('code', 'asc')->get();
        // var_dump($parent);
        // die();
        if ($parent) {
            foreach ($parent as $value) {
                # code...
                // $courier= new stdClass();
                // $courier->id=$value->id;
                // $courier->parent=$value->parent;
                // $courier->code=$value->code;
                // $courier->name=$value->name;
                // $courier->logo=$value->logo;
                $courier = array(
                    'id' => $value->id,
                    'parent' => $this->model->where('id', $value->parent_id)->select('name')->first()->name,
                    'code' => $value->code,
                    'name' => $value->name,
                    'logo' => $value->path_logo,
                    'is_active'=>$value->is_active

                );
                // $courier=(object)$courier;
                array_push($allcourier, $courier);
            }
        }
        return $allcourier;
    }
    public function trashOnly()
    {
        if (!Auth::user()->isFromPlatform()) {
            return $this->model->mine()->lowerPrivileged()->onlyTrashed()->get();
        }

        return $this->model->lowerPrivileged()->onlyTrashed()->get();
    }

    public function store(Request $request)
    {
        $courier = parent::store($request);

        // $this->syncPermissions($courier, $request->input('permissions', []));

        return $courier;
    }

    public function update(Request $request, $id)
    {
        $courier = parent::update($request, $id);

        // $this->syncPermissions($role, $request->input('permissions', []));

        return $courier;
    }
}
