<?php

namespace App\Http\Controllers;

use App\Domains\Clients\Company;
use App\Domains\Clients\Actions\CreateCompanyAction;
use App\Domains\Clients\Actions\UpdateCompanyAction;
use App\Domains\Clients\Actions\DeleteCompanyAction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:companies.read')->only('index', 'show');
        $this->middleware('permission:companies.create')->only('create', 'store');
        $this->middleware('permission:companies.update')->only('edit', 'update');
        $this->middleware('permission:companies.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $perPage = in_array($request->input('perPage'), [10, 20, 50, 100]) ? (int) $request->input('perPage') : 10;

        $companies = Company::latest()->paginate($perPage)->appends(['perPage' => $perPage]);

        return Inertia::render('Companies/Index', [
            'companies' => $companies,
        ]);
    }

    public function show(Company $company)
    {
        return Inertia::render('Companies/Show', [
            'company' => $company
        ]);
    }

    public function create()
    {
        return Inertia::render('Companies/Create');
    }

    public function store(Request $request, CreateCompanyAction $createCompany)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:50|unique:companies,tax_id',
            'legal_representative' => 'nullable|string|max:255',
            'legal_representative_phone' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255|url',
            'description' => 'nullable|string|max:1000',
        ]);

        $createCompany->execute($validated);

        return redirect()->route('companies.index');
    }

    public function edit(Company $company)
    {
        return Inertia::render('Companies/Edit', [
            'company' => $company
        ]);
    }

    public function update(Request $request, Company $company, UpdateCompanyAction $updateCompany)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:50|unique:companies,tax_id,' . $company->id,
            'legal_representative' => 'nullable|string|max:255',
            'legal_representative_phone' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255|url',
            'description' => 'nullable|string|max:1000',
        ]);

        $updateCompany->execute($company, $validated);

        return redirect()->route('companies.index');
    }

    public function destroy(Company $company, DeleteCompanyAction $deleteCompany)
    {
        $deleteCompany->execute($company);

        return redirect()->route('companies.index');
    }
}
