<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\FixedAsset;
use App\Models\FixedAssetAssignment;
use App\Models\FixedAssetBrand;
use App\Models\FixedAssetCategory;
use App\Models\FixedAssetEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FixedAssetController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $status = (string) $request->get('status', '');

        $query = FixedAsset::query()
            ->with(['currentAssignment.staff', 'categoryEntity', 'brandEntity'])
            ->withCount('assignments');

        if ($search !== '') {
            $normalized = mb_strtolower($search);
            $query->where(function ($q) use ($normalized) {
                $q->whereRaw('LOWER(asset_code) LIKE ?', ['%' . $normalized . '%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $normalized . '%'])
                    ->orWhereRaw('LOWER(category) LIKE ?', ['%' . $normalized . '%'])
                    ->orWhereRaw('LOWER(brand) LIKE ?', ['%' . $normalized . '%'])
                    ->orWhereRaw('LOWER(model) LIKE ?', ['%' . $normalized . '%'])
                    ->orWhereRaw('LOWER(serial_number) LIKE ?', ['%' . $normalized . '%'])
                    ->orWhereHas('categoryEntity', fn ($categoryQuery) => $categoryQuery->whereRaw('LOWER(name) LIKE ?', ['%' . $normalized . '%']))
                    ->orWhereHas('brandEntity', fn ($brandQuery) => $brandQuery->whereRaw('LOWER(name) LIKE ?', ['%' . $normalized . '%']));
            });
        }

        if ($status !== '' && in_array($status, FixedAsset::statusOptions(), true)) {
            $query->where('status', $status);
        }

        $assets = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('fixed-assets.index', [
            'assets' => $assets,
            'search' => $search,
            'status' => $status,
            'statusOptions' => FixedAsset::statusOptions(),
        ]);
    }

    public function create()
    {
        return view('fixed-assets.create', $this->formViewData(new FixedAsset()));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedAsset($request);
        $validated['asset_code'] = $this->generateAssetCode((int) $validated['fixed_asset_category_id'], (int) $validated['fixed_asset_brand_id']);
        $validated['category'] = FixedAssetCategory::find($validated['fixed_asset_category_id'])?->name;
        $validated['brand'] = FixedAssetBrand::find($validated['fixed_asset_brand_id'])?->name;

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('fixed-assets', 'public');
        }

        $asset = FixedAsset::create($validated);
        $this->recordEvent($asset, FixedAssetEvent::TYPE_CREATED, [
            'new_status' => $asset->status,
            'new_condition' => $asset->condition,
            'notes' => $asset->notes ? 'Asset created. ' . $asset->notes : 'Asset created.',
        ]);

        ActivityLog::record('fixed_asset.created', "Fixed asset {$asset->asset_code} created", $asset);

        return redirect()->route('fixed-assets.index')->with('success', 'Asset added successfully.');
    }

    public function edit(FixedAsset $fixedAsset)
    {
        return view('fixed-assets.edit', $this->formViewData($fixedAsset));
    }

    public function update(Request $request, FixedAsset $fixedAsset)
    {
        $originalStatus = $fixedAsset->status;
        $originalCondition = $fixedAsset->condition;
        $originalSnapshot = [
            'asset_code' => $fixedAsset->asset_code,
            'name' => $fixedAsset->name,
            'category' => $fixedAsset->category,
            'brand' => $fixedAsset->brand,
            'fixed_asset_category_id' => $fixedAsset->fixed_asset_category_id,
            'fixed_asset_brand_id' => $fixedAsset->fixed_asset_brand_id,
            'model' => $fixedAsset->model,
            'serial_number' => $fixedAsset->serial_number,
            'photo_path' => $fixedAsset->photo_path,
            'notes' => $fixedAsset->notes,
        ];
        $validated = $this->validatedAsset($request, $fixedAsset);

        if ($fixedAsset->currentAssignment && ($validated['status'] ?? $fixedAsset->status) !== FixedAsset::STATUS_ASSIGNED) {
            return back()->withErrors([
                'status' => 'This asset is currently assigned. Return it first before changing the status.',
            ])->withInput();
        }

        $categoryChanged = (int) $validated['fixed_asset_category_id'] !== (int) $fixedAsset->fixed_asset_category_id;
        $brandChanged = (int) $validated['fixed_asset_brand_id'] !== (int) $fixedAsset->fixed_asset_brand_id;
        $validated['asset_code'] = ($categoryChanged || $brandChanged)
            ? $this->generateAssetCode((int) $validated['fixed_asset_category_id'], (int) $validated['fixed_asset_brand_id'])
            : $fixedAsset->asset_code;
        $validated['category'] = FixedAssetCategory::find($validated['fixed_asset_category_id'])?->name;
        $validated['brand'] = FixedAssetBrand::find($validated['fixed_asset_brand_id'])?->name;

        if ($request->hasFile('photo')) {
            if ($fixedAsset->photo_path) {
                Storage::disk('public')->delete($fixedAsset->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('fixed-assets', 'public');
        }

        $fixedAsset->update($validated);

        $updatedSnapshot = [
            'asset_code' => $fixedAsset->asset_code,
            'name' => $fixedAsset->name,
            'category' => $fixedAsset->category,
            'brand' => $fixedAsset->brand,
            'fixed_asset_category_id' => $fixedAsset->fixed_asset_category_id,
            'fixed_asset_brand_id' => $fixedAsset->fixed_asset_brand_id,
            'model' => $fixedAsset->model,
            'serial_number' => $fixedAsset->serial_number,
            'photo_path' => $fixedAsset->photo_path,
            'notes' => $fixedAsset->notes,
        ];

        if ($originalStatus !== $fixedAsset->status) {
            $this->recordEvent($fixedAsset, FixedAssetEvent::TYPE_STATUS_CHANGED, [
                'old_status' => $originalStatus,
                'new_status' => $fixedAsset->status,
                'old_condition' => $originalCondition,
                'new_condition' => $fixedAsset->condition,
                'notes' => "Status changed from {$originalStatus} to {$fixedAsset->status}.",
            ]);
        }

        if ($originalCondition !== $fixedAsset->condition) {
            $this->recordEvent($fixedAsset, FixedAssetEvent::TYPE_CONDITION_CHANGED, [
                'old_status' => $originalStatus,
                'new_status' => $fixedAsset->status,
                'old_condition' => $originalCondition,
                'new_condition' => $fixedAsset->condition,
                'notes' => "Condition changed from {$originalCondition} to {$fixedAsset->condition}.",
            ]);
        }

        if ($originalSnapshot !== $updatedSnapshot) {
            $this->recordEvent($fixedAsset, FixedAssetEvent::TYPE_UPDATED, [
                'old_status' => $originalStatus,
                'new_status' => $fixedAsset->status,
                'old_condition' => $originalCondition,
                'new_condition' => $fixedAsset->condition,
                'notes' => 'Asset details were updated.',
            ]);
        }

        ActivityLog::record('fixed_asset.updated', "Fixed asset {$fixedAsset->asset_code} updated", $fixedAsset);

        return redirect()->route('fixed-assets.index')->with('success', 'Asset updated successfully.');
    }

    public function assignForm(FixedAsset $fixedAsset)
    {
        if ($fixedAsset->status === FixedAsset::STATUS_ASSIGNED || $fixedAsset->currentAssignment) {
            return redirect()->route('fixed-assets.index')->with('error', 'This asset is already assigned.');
        }

        if ($fixedAsset->status !== FixedAsset::STATUS_AVAILABLE) {
            return redirect()->route('fixed-assets.index')->with('error', 'Only available assets can be assigned.');
        }

        return view('fixed-assets.assign', [
            'asset' => $fixedAsset,
            'staffOptions' => $this->staffOptions(),
            'conditionOptions' => FixedAsset::conditionOptions(),
        ]);
    }

    public function assign(Request $request, FixedAsset $fixedAsset)
    {
        if ($fixedAsset->status === FixedAsset::STATUS_ASSIGNED || $fixedAsset->currentAssignment) {
            return back()->with('error', 'This asset is already assigned.');
        }

        if ($fixedAsset->status !== FixedAsset::STATUS_AVAILABLE) {
            return back()->with('error', 'Only available assets can be assigned.');
        }

        $validated = $request->validate([
            'staff_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role', [
                        User::ROLE_ADMIN,
                        User::ROLE_MANAGER,
                        User::ROLE_MOTO_MECHANIC,
                        User::ROLE_AC_MECHANIC,
                        User::ROLE_CASHIER,
                    ]);
                }),
            ],
            'assigned_at' => ['required', 'date'],
            'condition_on_assign' => ['required', Rule::in(FixedAsset::conditionOptions())],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($fixedAsset, $validated) {
            FixedAssetAssignment::create([
                'fixed_asset_id' => $fixedAsset->id,
                'staff_id' => $validated['staff_id'],
                'assigned_by' => Auth::id(),
                'assigned_at' => $validated['assigned_at'],
                'condition_on_assign' => $validated['condition_on_assign'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $fixedAsset->update([
                'status' => FixedAsset::STATUS_ASSIGNED,
                'condition' => $validated['condition_on_assign'],
            ]);

            $this->recordEvent($fixedAsset, FixedAssetEvent::TYPE_ASSIGNED, [
                'old_status' => FixedAsset::STATUS_AVAILABLE,
                'new_status' => FixedAsset::STATUS_ASSIGNED,
                'old_condition' => $fixedAsset->getOriginal('condition'),
                'new_condition' => $validated['condition_on_assign'],
                'event_at' => $validated['assigned_at'],
                'notes' => 'Assigned to ' . (User::find($validated['staff_id'])?->name ?? 'staff')
                    . (!blank($validated['notes']) ? '. ' . $validated['notes'] : '.'),
            ]);
        });

        $fixedAsset->refresh();
        $staff = User::find($validated['staff_id']);
        ActivityLog::record('fixed_asset.assigned', "Fixed asset {$fixedAsset->asset_code} assigned to {$staff?->name}", $fixedAsset);

        return redirect()->route('fixed-assets.index')->with('success', 'Asset assigned successfully.');
    }

    public function returnForm(FixedAsset $fixedAsset)
    {
        $assignment = $fixedAsset->currentAssignment()->with(['staff', 'assignedBy'])->first();

        if (!$assignment) {
            return redirect()->route('fixed-assets.index')->with('error', 'This asset is not currently assigned.');
        }

        return view('fixed-assets.return', [
            'asset' => $fixedAsset,
            'assignment' => $assignment,
            'conditionOptions' => FixedAsset::conditionOptions(),
        ]);
    }

    public function markReturned(Request $request, FixedAsset $fixedAsset)
    {
        $assignment = $fixedAsset->currentAssignment()->first();

        if (!$assignment) {
            return back()->with('error', 'This asset is not currently assigned.');
        }

        $validated = $request->validate([
            'returned_at' => ['required', 'date', 'after_or_equal:' . $assignment->assigned_at->format('Y-m-d H:i:s')],
            'condition_on_return' => ['required', Rule::in(FixedAsset::conditionOptions())],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($fixedAsset, $assignment, $validated) {
            $combinedNotes = $assignment->notes;
            if (!blank($validated['notes'])) {
                $combinedNotes = trim(($combinedNotes ? $combinedNotes . "\n\n" : '') . 'Return: ' . $validated['notes']);
            }

            $assignment->update([
                'returned_at' => $validated['returned_at'],
                'condition_on_return' => $validated['condition_on_return'],
                'notes' => $combinedNotes,
            ]);

            $fixedAsset->update([
                'status' => FixedAsset::STATUS_AVAILABLE,
                'condition' => $validated['condition_on_return'],
            ]);

            $this->recordEvent($fixedAsset, FixedAssetEvent::TYPE_RETURNED, [
                'old_status' => FixedAsset::STATUS_ASSIGNED,
                'new_status' => FixedAsset::STATUS_AVAILABLE,
                'old_condition' => $assignment->condition_on_assign,
                'new_condition' => $validated['condition_on_return'],
                'event_at' => $validated['returned_at'],
                'notes' => 'Returned by ' . ($assignment->staff?->name ?? 'staff')
                    . (!blank($validated['notes']) ? '. ' . $validated['notes'] : '.'),
            ]);
        });

        ActivityLog::record('fixed_asset.returned', "Fixed asset {$fixedAsset->asset_code} returned", $fixedAsset);

        return redirect()->route('fixed-assets.index')->with('success', 'Asset marked as returned.');
    }

    public function history(FixedAsset $fixedAsset)
    {
        $fixedAsset->load([
            'assignments.staff',
            'assignments.assignedBy',
            'events.performedBy',
        ]);

        return view('fixed-assets.history', [
            'asset' => $fixedAsset,
            'assignments' => $fixedAsset->assignments,
            'events' => $fixedAsset->events,
        ]);
    }

    public function currentCustody(Request $request)
    {
        $staffId = $request->integer('staff_id') ?: null;

        $assignmentQuery = FixedAssetAssignment::query()
            ->open()
            ->with(['asset', 'staff', 'assignedBy'])
            ->orderBy('assigned_at');

        if ($staffId) {
            $assignmentQuery->where('staff_id', $staffId);
        }

        $assignments = $assignmentQuery
            ->get()
            ->groupBy(fn (FixedAssetAssignment $assignment) => $assignment->staff?->name ?? 'Unassigned');

        $staffOptions = User::query()
            ->whereIn('id', FixedAssetAssignment::query()->open()->select('staff_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('fixed-assets.current-custody', [
            'groupedAssignments' => $assignments,
            'staffOptions' => $staffOptions,
            'staffId' => $staffId,
        ]);
    }

    private function validatedAsset(Request $request, ?FixedAsset $fixedAsset = null): array
    {
        return $request->validate([
            'asset_code' => [
                'nullable',
            ],
            'name' => ['required', 'string', 'max:255'],
            'fixed_asset_category_id' => ['required', Rule::exists('fixed_asset_categories', 'id')],
            'fixed_asset_brand_id' => ['required', Rule::exists('fixed_asset_brands', 'id')],
            'category' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'photo_path' => ['nullable', 'string', 'max:255'],
            'condition' => ['required', Rule::in(FixedAsset::conditionOptions())],
            'status' => ['required', Rule::in(FixedAsset::statusOptions())],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function formViewData(FixedAsset $asset): array
    {
        return [
            'asset' => $asset,
            'conditionOptions' => FixedAsset::conditionOptions(),
            'statusOptions' => FixedAsset::statusOptions(),
            'categories' => FixedAssetCategory::orderBy('name')->get(),
            'brands' => FixedAssetBrand::orderBy('name')->get(),
        ];
    }

    private function staffOptions()
    {
        return User::query()
            ->whereIn('role', [
                User::ROLE_ADMIN,
                User::ROLE_MANAGER,
                User::ROLE_MOTO_MECHANIC,
                User::ROLE_AC_MECHANIC,
                User::ROLE_CASHIER,
            ])
            ->orderBy('name')
            ->get();
    }

    private function recordEvent(FixedAsset $asset, string $type, array $attributes = []): void
    {
        FixedAssetEvent::create(array_merge([
            'fixed_asset_id' => $asset->id,
            'event_type' => $type,
            'old_status' => null,
            'new_status' => $asset->status,
            'old_condition' => null,
            'new_condition' => $asset->condition,
            'performed_by' => Auth::id(),
            'event_at' => now(),
            'notes' => null,
        ], $attributes));
    }

    private function generateAssetCode(int $categoryId, int $brandId): string
    {
        $category = FixedAssetCategory::findOrFail($categoryId);
        $brand = FixedAssetBrand::findOrFail($brandId);

        $prefix = strtoupper($category->code) . '-' . strtoupper($brand->code);
        $lastCode = FixedAsset::query()
            ->where('asset_code', 'like', $prefix . '-%')
            ->orderByDesc('asset_code')
            ->value('asset_code');

        $nextNumber = 1;
        if ($lastCode && preg_match('/(\d+)$/', $lastCode, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return sprintf('%s-%04d', $prefix, $nextNumber);
    }
}
