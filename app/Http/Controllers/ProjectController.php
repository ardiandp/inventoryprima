<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Daftar proyek (search + filter status + pagination).
     */
    public function index(Request $request): View
    {
        $search = $request->input('q');
        $status = $request->input('status');

        $projects = Project::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('master.projects.index', compact('projects', 'search', 'status'));
    }

    /**
     * Form buat proyek baru.
     */
    public function create(): View
    {
        return view('master.projects.create');
    }

    /**
     * Simpan proyek baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:projects,code'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $project = Project::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log('create', Project::class, $project->id, [
            'new' => ['code' => $project->code, 'name' => $project->name],
        ]);

        return redirect()->route('master.projects.index')
            ->with('success', "Proyek {$project->name} berhasil dibuat.");
    }

    /**
     * Form edit proyek.
     */
    public function edit(Project $project): View
    {
        return view('master.projects.edit', compact('project'));
    }

    /**
     * Update proyek.
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('projects', 'code')->ignore($project->id)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldData = $project->only(['code', 'name', 'start_date', 'end_date', 'is_active']);

        $project->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log('update', Project::class, $project->id, [
            'old' => $oldData,
            'new' => $project->fresh()->only(['code', 'name', 'start_date', 'end_date', 'is_active']),
        ]);

        return redirect()->route('master.projects.index')
            ->with('success', "Proyek {$project->name} berhasil diperbarui.");
    }

    /**
     * Hapus proyek (soft delete — tidak pernah hapus fisik).
     */
    public function destroy(Project $project): RedirectResponse
    {
        $name = $project->name;
        $project->delete();

        AuditLogger::log('delete', Project::class, $project->id, [
            'old' => ['code' => $project->code, 'name' => $name],
        ]);

        return redirect()->route('master.projects.index')
            ->with('success', "Proyek {$name} berhasil dihapus.");
    }
}