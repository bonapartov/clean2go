<?php

namespace App\Repositories\Frontend;

use App\Models\SeoSetting;
use App\Models\ServicePackage;
use Prettus\Repository\Eloquent\BaseRepository;

class ServicePackageRepository extends BaseRepository
{
    public function model()
    {
        return ServicePackage::class;
    }

    public function details($slug)
    {
        $package = $this->model->where('slug', $slug)->with('user')->whereNull('deleted_at')?->first();
        $seoSetting = SeoSetting::where('page_slug', 'service-package-detail')->where('is_active', true)->first();

        return view('frontend.service-package.details',[
            'package' => $package,
            'seoSetting' => $seoSetting
        ]);
    }

    public function selectServiceMen()
    {
        $package = $this->model->where('slug', "ultimate-wellness-beauty-experience")->with('user')->whereNull('deleted_at')?->first();
        return view('frontend.service-package.select-servicemen',['package'=> $package]);
    }
}
