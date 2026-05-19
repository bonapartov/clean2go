<?php

namespace App\Http\Controllers\Backend;

use App\Models\SeoSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\SeoSettingsDataTable;
use App\Repositories\Backend\SeoSettingRepository;

class SeoSettingController extends Controller
{
    public $repository;

    public function __construct(SeoSettingRepository $repository)
    {
        $this->authorizeResource(SeoSetting::class, 'seo_setting');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(SeoSettingsDataTable $dataTable)
    {
        return $dataTable->render('backend.seo-setting.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SeoSetting $SeoSetting)
    {
        return view('backend.seo-setting.edit', ['SeoSetting' => $SeoSetting]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SeoSetting $SeoSetting)
    {
        return $this->repository->update($request, $SeoSetting?->id);
    }

    public function updateStatus(Request $request)
    {
        return $this->repository->updateStatus($request);
    }

}
