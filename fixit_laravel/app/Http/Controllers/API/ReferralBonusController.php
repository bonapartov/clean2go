<?php

namespace App\Http\Controllers\API;

use App\Enums\RoleEnum;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Resources\ReferralBonusResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Models\ReferralBonus;
use App\Repositories\API\ReferralBonusRepository;

class ReferralBonusController extends Controller
{
  public $repository;

  public function __construct(ReferralBonusRepository $repository)
  {
    $this->authorizeResource(ReferralBonus::class, 'referralBonus', [
      'except' => ['index', 'show'],
    ]);
    $this->repository = $repository;
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request): AnonymousResourceCollection
  {
    try {

      $referralBonus = $this->filter($this->repository, $request);
      $referralBonus = $referralBonus->latest('created_at')->simplePaginate($request->paginate ?? $referralBonus->count() ?: null);
      return ReferralBonusResource::collection($referralBonus ?? []);

    } catch (Exception $e) {

      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(ReferralBonus $referralBonus)
  {
    return $this->repository->show($referralBonus?->id);
  }


  /**
   * Show the form for editing the specified resource.
   */
  public function edit(ReferralBonus $referralBonus)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, ReferralBonus $referralBonus)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(ReferralBonus $referralBonus)
  {
    //
  }

  public function filter($referralBonus, $request)
  {
    $roleName = Helpers::getCurrentRoleName();
    if ($roleName == RoleEnum::CONSUMER || $roleName == RoleEnum::PROVIDER) {
      $referralBonus = $referralBonus->where('referrer_id', Helpers::getCurrentUserId());
    }

    if ($request->field && $request->sort) {
      $referralBonus = $referralBonus->orderBy($request->field, $request->sort);
    }

    if (isset($request->status)) {
      $referralBonus = $referralBonus->where('status', $request->status);
    }

    return $referralBonus;
  }
}
