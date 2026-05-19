<?php

namespace App\Repositories\Backend;

use App\Exceptions\ExceptionHandler;
use Exception;
use App\Models\WalletBonus;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WalletBonusFilterExport;

class WalletBonusRepository extends BaseRepository
{
    protected $user;

    public function model()
    {
        return WalletBonus::class;
    }

    public function index($dataTable)
    {
        $query = $this->model->whereNotNull('bonus');
        return $dataTable->render('backend.wallet-bonus.index', [
            'minBonus' => $query->min('bonus'),
            'maxBonus' => $query->max('bonus'),
        ]);
    }

    public function create($attribute = [])
    {
        $locale = request('locale') ?? Session::get('locale', app()->getLocale());
        request()->merge(['locale' => $locale]);
        return view('backend.wallet-bonus.create');
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $payload = [
                'type'              => $request->type,
                'bonus'             => $request->type === 'fixed'  ? $request->amount  : ($request->type === 'percentage' ? $request->percentage_amount : null),
                'min_top_up_amount' => $request->min_top_up_amount,
                'max_bonus'         => $request->max_bonus,
                'status'            => $request->status,
                'is_admin_funded'   => $request->is_admin_funded,
                'usage_limit_per_user' => $request->usage_limit_per_user,
                'total_usage_limit' => $request->total_usage_limit,
                'is_unlimited' => $request->is_unlimited
            ];
            
            $walletBonus = $this->model->create($payload);
            $walletBonus->setTranslation('name', $request->locale, $request->name);
            $walletBonus->setTranslation('description', $request->locale, $request->description);
            $walletBonus->save();

            DB::commit();
            return redirect()->route('backend.walletBonus.index')->with('message', __('static.wallet.bonus_created'));
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $walletBonus = $this->model->findOrFail($id);   
        return view('backend.wallet-bonus.edit', ['walletBonus' => $walletBonus]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();

        try {
            $walletBonus = $this->model->findOrFail($id);
            
            if ($request->is_unlimited == true) {
                $usageLimit = null;
                $usagePerUser = null;
            } else {
                $usageLimit = $request->total_usage_limit;
                $usagePerUser = $request->usage_limit_per_user;
            }
            
            $payload = [
                'type'              => $request->type,
                'bonus'             => $request->type === 'fixed'  ? $request->amount  : ($request->type === 'percentage' ? $request->percentage_amount : null),
                'min_top_up_amount' => $request->min_top_up_amount,
                'max_bonus'         => $request->max_bonus,
                'status'            => $request->status,
                'is_admin_funded'   => $request->is_admin_funded,
                'usage_limit_per_user'   => $usagePerUser,
                'total_usage_limit'   => $usageLimit,
                'is_unlimited'      => $request->is_unlimited
            ];

            $walletBonus->update($payload);

            // Update translations
            $walletBonus->setTranslation('name', $request->locale, $request->name);
            $walletBonus->setTranslation('description', $request->locale, $request->description);
            $walletBonus->save();

            DB::commit();
            return redirect()->route('backend.walletBonus.index')->with('message', __('static.wallet.bonus_updated'));

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }


    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $walletBonus = $this->model->findOrFail($id);
            $walletBonus->destroy($id);

            DB::commit();
            return redirect()->back()->with(['message' => 'Wallet Bonus deleted successfully']);
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus($request)
    {
        try {
            $walletBonus = $this->model->findOrFail($request->id);
            $walletBonus->update([
                'status' => $request->status,
            ]);

            return response()->json(['message' => 'Status updated successfully']);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function deleteRows($ids)
    {
        try {
            
            $this->model->whereIn('id', $ids)->delete();
            return back()->with('message', 'Wallet Bonuses Deleted Successfully');

        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function export($request)
    {
        try {

            $format = $request->get('format', 'csv');
            switch ($format) {
                case 'excel':
                    return $this->exportExcel();
                case 'csv':
                default:
                    return $this->exportCsv();
            }

        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public  function exportExcel()
    {
        return Excel::download(new WalletBonusFilterExport, 'bookings.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new WalletBonusFilterExport, 'bookings.csv');
    }

}
