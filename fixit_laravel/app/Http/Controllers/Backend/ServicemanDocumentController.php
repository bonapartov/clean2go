<?php

namespace App\Http\Controllers\Backend;

use App\Models\User;
use App\Models\Document;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use App\DataTables\ServicemanDocumentDataTable;
use App\Enums\RoleEnum;
use App\Repositories\Backend\ServicemanDocumentRepository;
use App\Http\Requests\Backend\CreateServicemanDocumentRequest;
use App\Http\Requests\Backend\UpdateServicemanDocumentRequest;

class ServicemanDocumentController extends Controller
{
    private $document;

    private $repository;

    private $servicemen;

    public function __construct(ServicemanDocumentRepository $repository, Document $document, User $servicemen)
    {
        $this->document = $document;
        $this->servicemen = $servicemen;
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(ServicemanDocumentDataTable $dataTable)
    {
        return $dataTable->render('backend.serviceman-document.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        return view('backend.serviceman-document.create', ['servicemen' => $this->getServicemen(), 'documents' => $this->getDocuments()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Renderable
     */
    public function store(CreateServicemanDocumentRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Renderable
     */
    public function isVerify(Request $request)
    {
        return $this->repository->isVerify($request);
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function show(User $serviceman)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function edit($id)
    {
        $servicemanDocument = $this->repository->find($id);
        return view('backend.serviceman-document.edit', ['servicemanDocument' => $servicemanDocument, 'servicemen' => $this->getServicemen(), 'documents' => $this->getDocuments()]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Renderable
     */
    public function update(UpdateServicemanDocumentRequest $request,$id)
    {
        return $this->repository->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }

    protected function getServicemen()
    {
        return $this->servicemen->role(RoleEnum::SERVICEMAN)->get();
    }

    protected function getDocuments()
    {
        return $this->document->pluck('title', 'id');
    }

    public function deleteRows(Request $request)
    {
        return $this->repository->deleteRows($request);
    }
    public function export(Request $request)
    {
        return $this->repository->export($request);
    }

    public function import(Request $request)
    {
        return $this->repository->import($request);
    }
}
