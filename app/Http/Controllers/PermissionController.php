<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Nwidart\Modules\Facades\Module;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;

class PermissionController extends Controller
{
    public function index()
    {
        $x['title']     = 'Permission';
        $x['data']      = Permission::get();
        return view('admin.permission', $x);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => ['required'],
            'guard_name'    => ['required'],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)
                ->withInput();
        }
        try {
            $permission = Permission::create([
                'name'          => $request->name,
                'guard_name'    => $request->guard_name,
            ]);
            Alert::success('Pemberitahuan', 'Data <b>' . $permission->name . '</b> berhasil dibuat')->toToast()->toHtml();
        } catch (\Throwable $th) {
            Alert::error('Pemberitahuan', 'Data <b>' . $permission->name . '</b> gagal dibuat : ' . $th->getMessage())->toToast()->toHtml();
        }
        return back();
    }

    public function show(Request $request)
    {
        $permission = Permission::find($request->id);
        return response()->json([
            'status'    => Response::HTTP_OK,
            'message'   => 'Data permission by id',
            'data'      => $permission
        ], Response::HTTP_OK);
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => ['required'],
            'guard_name'    => ['required'],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)
                ->withInput();
        }
        try {
            $permission = Permission::find($request->id);
            $permission->update([
                'name'          => $request->name,
                'guard_name'    => $request->guard_name,
            ]);
            Alert::success('Pemberitahuan', 'Data <b>' . $permission->name . '</b> berhasil disimpan')->toToast()->toHtml();
        } catch (\Throwable $th) {
            Alert::error('Pemberitahuan', 'Data <b>' . $permission->name . '</b> gagal disimpan : ' . $th->getMessage())->toToast()->toHtml();
        }
        return back();
    }

    public function destroy(Request $request)
    {
        try {
            $permission = Permission::find($request->id);
            $permission->delete();
            Alert::success('Pemberitahuan', 'Data <b>' . $permission->name . '</b> berhasil dihapus')->toToast()->toHtml();
        } catch (\Throwable $th) {
            Alert::error('Pemberitahuan', 'Data <b>' . $permission->name . '</b> gagal dihapus : ' . $th->getMessage())->toToast()->toHtml();
        }
        return back();
    }
    public function reloadPermission()
    {
        try {
            $this->initModules();
            Alert::success('Pemberitahuan', 'Permisiion berhasil diperbarui')->toToast()->toHtml();
        } catch (\Throwable $th) {
            Alert::error('Pemberitahuan', 'Permission gagal diperbarui : ' . $th->getMessage())->toToast()->toHtml();
        }
        return back();
    }

    private function initModules()
    {
        $modules = Module::getOrdered();
        $modulemenus = [];

        if ($modules) {
            foreach ($modules as $module) {
                $this->initModulePermissions($module);
            }
        }
    }
    private function getModuleDetails($module)
    {
        $moduleJson = $module->getPath() . '/module.json';
        return json_decode(file_get_contents($moduleJson), true);
    }

    private function initModulePermissions($module)
    {
        $moduleDetails = $this->getModuleDetails($module);
        if (!empty($moduleDetails['permissions'])) {
            foreach ($moduleDetails['permissions'] as $permission) {
                $this->initPermissionActions($permission);
            }
        }
    }

    private function initPermissionActions($permission)
    {
        $permissionMappings = ['delete', 'update', 'read', 'create'];

        $permissionActions = [];
        foreach ($permissionMappings as $permissionMapping) {
            $name = $permissionMapping . ' ' . $permission;
            $permissionActions[] = Permission::create(['name' => $name]);
        }
    }
}
