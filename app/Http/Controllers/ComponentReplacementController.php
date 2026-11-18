<?php

namespace App\Http\Controllers;

use App\Exports\ComponentReplacementExport;
use App\Models\Component;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ComponentReplacementController extends Controller
{
    public function create(Component $component)
    {
        $component->load(['machine.station', 'machine.latestHmLog']);

        return view('components.replacement', ['part' => $component]);
    }

    public function store(Request $request, Component $component)
    {
        $validated = $request->validate([
            'removed_at' => ['required', 'date', 'before_or_equal:today'],
            'hm_removed' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'new_name' => ['required', 'string', 'max:255'],
            'installed_at_new' => ['required', 'date', 'after_or_equal:removed_at', 'before_or_equal:today'],
            'hm_installed_new' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'target_lifetime_hm_new' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'target_lifetime_months_new' => ['required', 'integer', 'min:1', 'max:1200'],
            'reason' => ['required', Rule::in(['Perawatan Berkala', 'Kerusakan', 'Lainnya'])],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($component->installed_at && $validated['removed_at'] < $component->installed_at->toDateString()) {
            throw ValidationException::withMessages([
                'removed_at' => 'Tanggal pelepasan tidak boleh sebelum tanggal pemasangan komponen.',
            ]);
        }

        if ($component->hm_installed !== null
            && (float) $validated['hm_removed'] < (float) $component->hm_installed) {
            throw ValidationException::withMessages([
                'hm_removed' => 'HM pelepasan tidak boleh lebih kecil dari HM saat pemasangan.',
            ]);
        }

        if ((float) $validated['hm_installed_new'] < (float) $validated['hm_removed']) {
            throw ValidationException::withMessages([
                'hm_installed_new' => 'HM pemasangan komponen baru tidak boleh lebih kecil dari HM pelepasan.',
            ]);
        }

        DB::transaction(function () use ($component, $validated) {
            $currentComponent = Component::query()
                ->lockForUpdate()
                ->findOrFail($component->id);

            if ($currentComponent->installed_at
                && $validated['removed_at'] < $currentComponent->installed_at->toDateString()) {
                throw ValidationException::withMessages([
                    'removed_at' => 'Tanggal pelepasan tidak boleh sebelum tanggal pemasangan komponen.',
                ]);
            }

            if ($currentComponent->hm_installed !== null
                && (float) $validated['hm_removed'] < (float) $currentComponent->hm_installed) {
                throw ValidationException::withMessages([
                    'hm_removed' => 'HM pelepasan tidak boleh lebih kecil dari HM saat pemasangan.',
                ]);
            }

            $currentComponent->replacements()->create([
                'old_name' => $currentComponent->name,
                'new_name' => $validated['new_name'],
                'installed_at' => $currentComponent->installed_at,
                'removed_at' => $validated['removed_at'],
                'hm_installed' => $currentComponent->hm_installed,
                'hm_removed' => $validated['hm_removed'],
                'target_lifetime_hm' => $currentComponent->target_lifetime_hm,
                'target_lifetime_months' => $currentComponent->target_lifetime_months,
                'installed_at_new' => $validated['installed_at_new'],
                'hm_installed_new' => $validated['hm_installed_new'],
                'target_lifetime_hm_new' => $validated['target_lifetime_hm_new'],
                'target_lifetime_months_new' => $validated['target_lifetime_months_new'],
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $currentComponent->update([
                'name' => $validated['new_name'],
                'installed_at' => $validated['installed_at_new'],
                'hm_installed' => $validated['hm_installed_new'],
                'target_lifetime_hm' => $validated['target_lifetime_hm_new'],
                'target_lifetime_months' => $validated['target_lifetime_months_new'],
            ]);
        });

        return redirect()
            ->route('components.show', $component)
            ->with('status', 'Pergantian komponen berhasil disimpan.');
    }

    public function index(Request $request)
    {
        $validated = $this->validatedFilters($request);

        $stations = Station::with(['machines' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();
        $components = Component::with('machine')
            ->orderBy('name')
            ->get();
        $replacements = (new ComponentReplacementExport($validated))
            ->query()
            ->paginate(15)
            ->withQueryString();

        return view('components.history', compact('stations', 'components', 'replacements'));
    }

    public function export(Request $request)
    {
        $validated = $this->validatedFilters($request);

        return Excel::download(
            new ComponentReplacementExport($validated),
            'riwayat-pergantian-komponen.xlsx'
        );
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'station_id' => ['nullable', 'integer', 'exists:stations,id'],
            'machine_id' => ['nullable', 'integer', 'exists:machines,id'],
            'component_id' => ['nullable', 'integer', 'exists:components,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
