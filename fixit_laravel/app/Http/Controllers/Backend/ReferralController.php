<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\DataTables\ReferralDataTable;
class ReferralController extends Controller
{
    public function index(ReferralDataTable $dataTable)
    {
        return $dataTable->render('backend.referral.index');
    }
}
