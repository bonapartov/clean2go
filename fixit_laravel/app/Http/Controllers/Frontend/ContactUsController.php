<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\ContactMail;
use App\Models\SeoSetting;
use Illuminate\Support\Facades\Mail;

class ContactUsController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $seoSetting = SeoSetting::where('page_slug', 'contact-us')->where('is_active', true)->first();
        return view('frontend.contact-us.index',[
            'seoSetting' => $seoSetting
        ]);
    }

    public function sendMail(Request $request)
    {
        Mail::to($request->email)->send(mailable: new ContactMail($request));
        return redirect()->back()->with('message','Mail Send Successfully');
    }
}