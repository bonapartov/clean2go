<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateBankDetailRequest;
use App\Http\Requests\API\UpdateBankDetailRequest;
use App\Http\Resources\WalletBonusResource;
use App\Models\BankDetail;
use App\Models\WalletBonus;
use App\Repositories\API\BankDetailRepository;
use App\Repositories\API\WalletBonusRepository;
use Illuminate\Http\Request;

class WalletBonusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $repository;
    public $walletBonus;

    public function __construct(WalletBonusRepository $repository, WalletBonus $walletBonus)
    {
        // $this->authorizeResource(WalletBonus::class, 'walletBonus');
        $this->walletBonus = $walletBonus;
        $this->repository = $repository;
    }

    public function index()
    {
        $walletbonus = $this->walletBonus::select('id', 'name', 'description')->get(); 
        
        return response()->json([
            'success' => true,
            'data' => WalletBonusResource::collection($walletbonus),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(CreateBankDetailRequest $request)
    {
        return $this->repository->store($request);
    }


    public function show(BankDetail $bankDetail)
    {
        //
    }


    public function edit(BankDetail $bankDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBankDetailRequest $request, $user_id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, BankDetail $bankDetail)
    {
        //
    }
}
