<?php

namespace App\Repositories\Backend;

use Exception;
use App\Models\User;
use App\Models\Zone;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Prettus\Repository\Eloquent\BaseRepository;

class ZoneManagerRepository extends BaseRepository
{
    protected $role;

    public function model()
    {
        $this->role = new Role();
        return User::class;
    }

    public function create($attribute = [])
    {
        return view('backend.zone_manager.create', [
            'roles' => $this->role->where('system_reserve', 0)->pluck('name', 'id'),
            'zones' => Zone::all(),
            'countries' => Helpers::getCountries(),
            'countryCodes' => Helpers::getCountryCodes(),
        ]);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $user = $this->model->create([
                'name' => $request->name,
                'email' => $request->email,
                'code' => $request->code,
                'phone' => (string) $request->phone,
                'status' => $request->status ?? 1,
                'password' => Hash::make($request->password),
                'allow_all_zones' => $request->allow_all_zones ?? false,
            ]);

            if ($request->role) {
                $role = $this->role->findOrFail($request->role);
                $user->assignRole($role);
            }

            // Handle zone permissions
            if ($request->allow_all_zones) {
                // If allow all zones, don't assign specific zones
                $user->zonePermissions()->detach();
            } else {
                // Assign specific zones
                if ($request->zone_ids && is_array($request->zone_ids)) {
                    $user->zonePermissions()->sync($request->zone_ids);
                }
            }

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $user->addMediaFromRequest('image')->toMediaCollection('image');
            }

            if (Helpers::walletIsEnable()) {
                $user->wallet()->create();
                $user->wallet;
            }

            DB::commit();
            return redirect()->route('backend.zone_manager.index')->with('message', 'Zone Manager Created Successfully.');

        } catch (Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $user = $this->model->with('zonePermissions')->findOrFail($id);
        return view('backend.zone_manager.edit', [
            'user' => $user,
            'roles' => $this->role->where('system_reserve', 0)->pluck('name', 'id'),
            'zones' => Zone::all(),
            'userZoneIds' => $user->zonePermissions->pluck('id')->toArray(),
            'countries' => Helpers::getCountries(),
            'countryCodes' => Helpers::getCountryCodes(),
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $user = $this->model->findOrFail($id);
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => (string) $request->phone,
                'code' => $request->code,
                'status' => $request->status ?? 1,
                'allow_all_zones' => $request->allow_all_zones ?? false,
            ]);

            if ($request->role) {
                $role = $this->role->findOrFail($request->role);
                $user->syncRoles([$role]);
            }

            // Handle zone permissions
            if ($request->allow_all_zones) {
                // If allow all zones, remove specific zone assignments
                $user->zonePermissions()->detach();
            } else {
                // Sync specific zones
                if ($request->zone_ids && is_array($request->zone_ids)) {
                    $user->zonePermissions()->sync($request->zone_ids);
                } else {
                    // If no zones selected and allow_all_zones is false, remove all
                    $user->zonePermissions()->detach();
                }
            }

            if ($request->file('image') && $request->file('image')->isValid()) {
                $user->clearMediaCollection('image');
                $user->addMediaFromRequest('image')->toMediaCollection('image');
            }

            DB::commit();
            return redirect()->route('backend.zone_manager.index')->with('message', 'Zone Manager Updated Successfully.');

        } catch (Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $user = $this->model->findOrFail($id);
            $user->zonePermissions()->detach();
            $user->forceDelete();

            return redirect()->back()->with('message', 'Zone Manager Deleted Successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function status($id, $status)
    {
        try {
            $user = $this->model->findOrFail($id);
            $user->update(['status' => $status]);

            return json_encode(['resp' => $user]);
        } catch (Exception $e) {
            throw $e;
        }
    }
}

