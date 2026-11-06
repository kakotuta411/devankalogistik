<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\AuditService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['branch_code','name','city','province_code','created_at'];
        $sortCol = in_array($request->sort, $allowedSorts) ? $request->sort : 'created_at';
        $sortDir = $request->has('sort') ? ($request->dir === 'desc' ? 'desc' : 'asc') : 'desc';

        $query = Branch::query();
        $query->when($request->search, fn($q, $s) => $q->where(function($q2) use ($s) {
                    $q2->where('branch_code', 'like', "%$s%")->orWhere('name', 'like', "%$s%");
                }))
              ->when($request->province_code, fn($q, $pc) => $q->where('province_code', $pc))
              ->orderBy($sortCol, $sortDir);
        $branches = $query->paginate(10);
        $regions = \App\Models\Region::where('level', 'province')->get();
        return view('branches.index', compact('branches', 'regions'));
    }

    public function create()
    {
        $regions = \App\Models\Region::where('level', 'province')->get();
        return view('branches.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_code' => 'required|unique:branches,branch_code',
            'name' => 'required',
            'address' => 'nullable',
            'province_code' => 'nullable',
            'regency_code' => 'nullable',
            'district_code' => 'nullable',
            'village_code' => 'nullable',
        ]);
        if (!empty($data['regency_code'])) {
            $regency = \App\Models\Region::find($data['regency_code']);
            $data['city'] = $regency ? $regency->name : null;
        }
        $branch = Branch::create($data);
        AuditService::log('Create Branch', 'CREATE', $branch);
        return redirect()->route('branches.index')->with('success', 'Cabang dibuat');
    }

    public function show(Branch $branch)
    {
        return view('branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        $regions = \App\Models\Region::where('level', 'province')->get();
        return view('branches.edit', compact('branch', 'regions'));
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'branch_code' => 'required',
            'name' => 'required',
            'address' => 'nullable',
            'province_code' => 'nullable',
            'regency_code' => 'nullable',
            'district_code' => 'nullable',
            'village_code' => 'nullable',
        ]);
        if (!empty($data['regency_code'])) {
            $regency = \App\Models\Region::find($data['regency_code']);
            $data['city'] = $regency ? $regency->name : null;
        }
        $branch->update($data);
        AuditService::log('Update Branch', 'UPDATE', $branch);
        return redirect()->route('branches.index')->with('success', 'Cabang diperbarui');
    }

    public function destroy(Branch $branch)
    {
        AuditService::log('Delete Branch', 'DELETE', $branch);
        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Cabang dihapus');
    }
}
