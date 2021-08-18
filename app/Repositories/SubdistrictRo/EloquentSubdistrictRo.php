<?php

namespace App\Repositories\SubdistrictRo;

use Auth;
use App\SubdistrictRo;

use Illuminate\Http\Request;
use App\Repositories\BaseRepository;
use App\Repositories\EloquentRepository;
// add by ari 01062021
class EloquentSubdistrictRo extends EloquentRepository implements BaseRepository, SubdistrictRoRepository
{
    protected $model;

    public function __construct(SubdistrictRo $subdistrictRo)
    {
        $this->model = $subdistrictRo;
    }
    public function getSubdistrictbyname($name)
    {
        $subdistrict = $this->model->where('subdistrict',$name)->first();
        // echo json_encode($courier);die();
        return $subdistrict;
    }

    public function all()
    {
        $allcourier = array();
        $parent = $this->model->where('parent_id', '>', 0)->orderBy('code', 'asc')->get();
        // var_dump($parent);
        // die();
        if ($parent) {
            foreach ($parent as $value) {
                # code...

                $courier = array(
                    'id' => $value->id,
                    'parent' => $this->model->where('id', $value->parent_id)->select('name')->first()->name,
                    'code' => $value->code,
                    'name' => $value->name,
                    'logo' => $value->path_logo,

                );
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
