<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllocationResource;
use App\Models\Allocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AllocationController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return AllocationResource::collection(
            Allocation::with('sourceAccount', 'destinationAccount')->get()
        );
    }

    public function store(Request $request): AllocationResource
    {
        $validated = $request->validate([
            'source_account_id' => ['required', 'exists:accounts,id'],
            'destination_account_id' => ['required', 'exists:accounts,id', 'different:source_account_id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $allocation = Allocation::create($validated);

        return new AllocationResource($allocation->load('sourceAccount', 'destinationAccount'));
    }

    public function show(Allocation $allocation): AllocationResource
    {
        return new AllocationResource($allocation->load('sourceAccount', 'destinationAccount'));
    }

    public function update(Request $request, Allocation $allocation): AllocationResource
    {
        $validated = $request->validate([
            'source_account_id' => ['sometimes', 'exists:accounts,id'],
            'destination_account_id' => ['sometimes', 'exists:accounts,id', 'different:source_account_id'],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $allocation->update($validated);

        return new AllocationResource($allocation->load('sourceAccount', 'destinationAccount'));
    }

    public function destroy(Allocation $allocation): JsonResponse
    {
        $allocation->delete();

        return response()->json(null, 204);
    }
}
