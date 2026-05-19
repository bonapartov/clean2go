<?php

namespace App\Http\Controllers\Backend;

use App\Models\User;
use App\Models\Document;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use App\DataTables\ProviderDocumentDataTable;
use App\Enums\RoleEnum;
use App\Http\Requests\Backend\CreateProviderDocumentRequest;
use App\Http\Requests\Backend\UpdateProviderDocumentRequest;
use App\Models\UserDocument;
use App\Repositories\Backend\ProviderDocumentRepository;

class ProviderDocumentController extends Controller
{
    private $document;

    private $repository;

    private $providers;

    public function __construct(ProviderDocumentRepository $repository, Document $document, User $providers)
    {
        $this->authorizeResource(UserDocument::class, 'provider_document');
        $this->document = $document;
        $this->providers = $providers;
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(ProviderDocumentDataTable $dataTable)
    {
        return $dataTable->render('backend.provider-document.index',[
            'documents' => Document::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        if(auth()->user()->role->name == RoleEnum::PROVIDER) {
            // For provider: exclude documents already uploaded by this provider
            $assignedDocumentIds = UserDocument::where('user_id', auth()->id())->pluck('document_id')->toArray();
            $documents = Document::whereNotIn('id', $assignedDocumentIds)->get();

        } else {
            // For admin: show all documents
            $documents = Document::all();
        }

        return view('backend.provider-document.create', [
            'providers' => $this->getProviders(), 
            'documents' => $documents
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Renderable
     */
    public function store(CreateProviderDocumentRequest $request)
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
    public function show(User $provider)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function edit(UserDocument $providerDocument)
    {
        $providerDocument = $this->repository->find($providerDocument->id);
        if(auth()->user()->role == 'provider') {
            // Documents assigned to this provider excluding current document
            $assignedDocumentIds = UserDocument::where('user_id', auth()->id())->where('id', '!=', $id)->pluck('document_id')->toArray();
            $documents = Document::whereNotIn('id', $assignedDocumentIds)->get();
            
        } else {
            // Admin sees all documents
            $documents = Document::all();
        }
            
        return view('backend.provider-document.edit', ['providerDocument' => $providerDocument, 'providers' => $this->getProviders(), 'documents' => $documents]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Renderable
     */
    public function update(UpdateProviderDocumentRequest $request,UserDocument $providerDocument)
    {
        return $this->repository->update($request, $providerDocument->id);
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

    protected function getProviders()
    {
        return $this->providers->role(RoleEnum::PROVIDER)->get();
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

    public function providerDocumentsFilterExport(Request $request)
    {
        return $this->repository->providerDocumentsFilterExport($request);
    }

}
