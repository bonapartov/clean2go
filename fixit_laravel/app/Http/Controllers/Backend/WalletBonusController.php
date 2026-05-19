<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Repositories\Backend\WalletBonusRepository;
use App\Http\Requests\Backend\CreateWalletBonusRequest;
use App\Http\Requests\Backend\UpdateWalletBonusRequest;
use App\DataTables\WalletBonusDatatable;
use Illuminate\Http\Request;

class WalletBonusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(WalletBonusRepository $repository)
    {
        // $this->authorizeResource(WalletBonus::class, 'wallet_bonus');
        $this->repository = $repository;
    }

    /**
     * Show the form for creating a new resource.
     */

    public function index(WalletBonusDatatable $dataTable)
    {
        return $this->repository->index($dataTable);
    }

    public function create($attributes=[])
    {
        return $this->repository->create($attributes);
    }

    public function store(CreateWalletBonusRequest $request)
    {
        return $this->repository->store($request);
    }

    public function edit($id)
    {
        return $this->repository->edit($id);
    }

    public function update(UpdateWalletBonusRequest $request, $id)
    {
        return $this->repository->update($request, $id);
    }

    public function updateStatus(Request $request)
    {
        return $this->repository->updateStatus($request);
    }

    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }

    public function deleteRows(Request $request)
    {
       return $this->repository->deleteRows($request->id);
    }

    public function export(Request $request)
    {
        return $this->repository->export($request);
    }

}
