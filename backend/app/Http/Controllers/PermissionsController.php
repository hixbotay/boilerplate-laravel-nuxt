<?php

namespace App\Http\Controllers;

use App\Models\Permissions;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class PermissionsController extends Controller
{
    public function index(Request $request)
    {
        $query = Permissions::filter($request);
        $res = [
            'page' => $query->getPageNumber(),
            'per_page' =>  $query->getPerPage(),
        ];
        if($request->is_paginate){
            $res['total_records'] = $query->getTotal();
        }else{
            $res['records'] = $query->get();
        }
        return response()->json($res);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'resource' => 'required|string',
            'action' => 'string',
            'owner' => 'string',
        ]);
        try {
            $permission = Permissions::create($data);
        } catch (\Exception $e) {
            return response()->json(array('message' => $e->getMessage()), 400);
        }
        return response()->json($permission);
    }

    public function update(Request $request, Permissions $permission)
    {

        $data = $request->validate([
            'name' => 'required|string',
            'resource' => 'required|string',
            'action' => 'string',
            'owner' => 'string',
        ]);
        try {
            $permission->update($data);
        } catch (\Exception $e) {
            return response()->json(array('message' => $e->getMessage()), 400);
        }
        return response()->json($permission);
    }

    public function show(Request $request, Permissions $permission)
    {
        return response()->json($permission);
    }

    public function destroy(Request $request, Permissions $permission)
    {
        $result = $permission->delete();
        if ($result) {
            return response()->json(['message' => 'Delete success']);
        }
        return response()->json(['message' => 'Delete error'], 400);
    }

    public function updateRouteNamePermission(Request $request, Router $route) {
        $r = $route->getRoutes();
        $permissions = Permissions::all();
        $listRouterName = [];
        $newRoles = [];
        $updateRoles = [];
        foreach ($r as $value) {
            // Chỉ lấy những route_name có đầu admin để lưu vào table permission
            if (strpos($value->getName(), 'admin.') === false)  continue;
            $listRouterName[] = $value->getName();
            // Kiểm tra route_name đã tồn tại trong table permission không
            $check = $permissions->contains(function ($per, $key) use ($value) {
                return $per->name == $value->getName();
            });
            // Nếu có thì loại khỏi danh sách thêm vào trong table permission
            if ($check) {
                // Kiểm tra xem route_name có thay đổi gì không, có thì cập nhật lại theo route_name
                $first = $permissions->first(function ($per, $key) use ($value) {
                    return $per->name == $value->getName();
                });
                if ($first->action != $value->uri() || $first->resource != $value->methods[0]) {
                    $dataUpdate = [
                        'action' => $value->uri(),
                        'resource' => $value->methods[0]
                    ];
                    $first->update($dataUpdate);
                    $updateRoles[] = $first;
                }
                continue;
            } 

            $newRoles[] = array(
                'name' => $value->getName(),
                'action' => $value->uri(),
                'resource' => $value->methods[0],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            );
        }
        $insertStatus = Permissions::insert($newRoles);
        // Thực hiện xóa những router_name ở table không còn tồn tại trong hệ thống
        $deleteRoles = [];
        $permissions->each(function ($per, $key) use($listRouterName, &$deleteRoles) {
            if (!in_array($per->name, $listRouterName)) {
                $deleteRoles[] = $per;
                $per->delete();
            }
        });
        return response()->json([
            'roles_new' => $newRoles,
            'roles_update' => $updateRoles,
            'roles_delete' => $deleteRoles,
            'roles_total' => $permissions->count()
        ]);

    }

    public function getRouterName(Request $request, Router $route) {
        $r = $route->getRoutes();
        $listRouterName = [];
        foreach ($r as $value) {
            // Chỉ lấy những route_name có đầu admin để lưu vào table permission
            if (strpos($value->getName(), 'admin.') === false)  continue;
            $listRouterName[] =  [
                'name' => $value->getName(),
                'method' => $value->methods()[0]
            ];
           
        }
        return response()->json([
            'records' => $listRouterName
        ]);
    }
}
