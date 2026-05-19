<?php

namespace App\Http\Controllers\Backend;

use App\Models\User;
use Illuminate\Http\Request;
use App\DataTables\ZoneManagerDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateZoneManagerRequest;
use App\Http\Requests\Backend\UpdateZoneManagerRequest;
use App\Repositories\Backend\ZoneManagerRepository;
use Illuminate\Contracts\Support\Renderable;

class ZoneManagerController extends Controller
{
    private $repository;

    public function __construct(ZoneManagerRepository $repository)
    {
        $this->authorizeResource(User::class, 'zone_manager');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(ZoneManagerDataTable $dataTable)
    {
        return $dataTable->render('backend.zone_manager.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        return $this->repository->create();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Renderable
     */
    public function store(CreateZoneManagerRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function edit(User $zone_manager)
    {
        return $this->repository->edit($zone_manager->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Renderable
     */
    public function update(UpdateZoneManagerRequest $request, User $zone_manager)
    {
        return $this->repository->update($request, $zone_manager->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function destroy(User $zone_manager)
    {
        return $this->repository->destroy($zone_manager->id);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $user = User::find($request->id[$row]);
                if ($user) {
                    $user->zonePermissions()->detach();
                    $user->forceDelete();
                }
            }
            return redirect()->back()->with('message', 'Zone Managers Deleted Successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}

