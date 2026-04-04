<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DailyBudgetResource;
use App\Models\Budget;
use App\Services\DailyBudgetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyBudgetController extends Controller
{
    public function show(DailyBudgetService $service): DailyBudgetResource|JsonResponse
    {
        $result = $service->calculate();

        if (! $result) {
            return response()->json(['message' => 'No budget configured'], 404);
        }

        return new DailyBudgetResource($result);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'monthly_income' => ['required', 'numeric', 'min:0'],
        ]);

        $budget = Budget::first() ?? new Budget;
        $budget->monthly_income = $validated['monthly_income'];
        $budget->save();

        return response()->json(['monthly_income' => (float) $budget->monthly_income]);
    }
}
