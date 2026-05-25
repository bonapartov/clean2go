<?php
namespace Modules\YooKassa\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\YooKassa\Payment\YooKassa;
class YooKassaController extends Controller { public function webhook(Request $request) { return YooKassa::webhook($request); } }
