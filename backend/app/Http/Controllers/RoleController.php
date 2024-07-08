<?php

namespace App\Http\Controllers;

use App\Http\Enums\UserRoleType;
use App\Models\Permissions;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Get a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $query = Role::filter($request);

		return response()->json([
			'records' => $query->get(),
			'page' => $query->getPageNumber(),
			'per_page' => $query->getPerPage(),
			'total_records' => $query->getTotal()
		]);
    }

    /**
     * Creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $body = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'type' => 'required|in:' . implode(',', UserRoleType::getAllValue()),
        ]);

        $role = Role::create($body);

        return response()->json($role);
    }

    /**
     * Get the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get($id)
    {
        $role = Role::findOrFail($id);
        return response()->json($role);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $body = $request->validate([
            'name' => 'nullable|string|unique:roles,name,'.$id,
            'permission' => 'nullable|array',

        ]);
        
        $role = Role::findOrFail($id);

        $role->update($body);

        return response()->json($role);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $userCount = UserRole::where('role_id', $id)->count();

        if ($userCount) return response()->json(['message' => 'Cannot remove this role because it is attached to existed users'], 400);

        $role = Role::findOrFail($id);

        $role->delete();

        return response()->json(['message' => 'Success'], 202);
    }
}
