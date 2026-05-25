<?php
namespace Modules\CloudPayments\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CloudPayments\Payment\CloudPayments;
class CloudPaymentsController extends Controller
{
    public function webhook(Request $request) { return CloudPayments::webhook($request); }
}
