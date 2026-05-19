<?php

namespace App\Repositories\API;

use Exception;
use App\Models\WalletBonus;
use App\Exceptions\ExceptionHandler;
use App\Http\Resources\WalletBonusResource;
use Prettus\Repository\Eloquent\BaseRepository;

class WalletBonusRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title' => 'like',
    ];

    public function model()
    {
        return WalletBonus::class;
    }

    // public function show($id)
    // {
    //     try {
    //         $walletbonus = WalletBonus::query()
    //         ->with(['name' , 'bonus'])
    //         ->where('id' , $id)
    //         ->first();

    //         return response()->json([
    //             'success' => true,
    //             'data' =>  new WalletBonusResource($walletbonus),
    //         ]);

    //     } catch (Exception $e) {

    //         throw new ExceptionHandler($e->getMessage(), $e->getCode());
    //     }
    // }
}
