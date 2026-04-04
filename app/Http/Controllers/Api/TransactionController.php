<?php

namespace App\Http\Controllers\Api;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rules\Enum;

class TransactionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Transaction::with('category.parent', 'account')
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('month')) {
            $query->whereRaw("strftime('%Y-%m', date) = ?", [$request->month]);
        }

        return TransactionResource::collection($query->paginate(50));
    }

    public function store(Request $request): TransactionResource
    {
        $validated = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'type' => ['required', new Enum(TransactionType::class)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
        ]);

        $transaction = Transaction::create($validated);

        return new TransactionResource($transaction->load('category.parent', 'account'));
    }

    public function show(Transaction $transaction): TransactionResource
    {
        return new TransactionResource($transaction->load('category.parent', 'account'));
    }

    public function update(Request $request, Transaction $transaction): TransactionResource
    {
        $validated = $request->validate([
            'account_id' => ['sometimes', 'exists:accounts,id'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'type' => ['sometimes', new Enum(TransactionType::class)],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
            'date' => ['sometimes', 'date'],
        ]);

        $transaction->update($validated);

        return new TransactionResource($transaction->load('category.parent', 'account'));
    }

    public function destroy(Transaction $transaction): JsonResponse
    {
        $transaction->delete();

        return response()->json(null, 204);
    }
}
