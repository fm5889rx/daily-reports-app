<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDailyReportRequest;
use App\Http\Requests\UpdateDailyReportRequest;
use App\Models\DailyReport;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DailyReportApiController extends Controller
{
    public function show(DailyReport $dailyReport): JsonResponse
    {
        $this->authorize('update', $dailyReport);

        return response()->json([
            'data' => $this->serialize($dailyReport->load('user:id,name')),
        ]);
    }

    public function index(): JsonResponse
    {
        $reports = DailyReport::query()
            ->with('user:id,name')
            ->orderByDesc('reported_at')
            ->orderByDesc('id')
            ->paginate(5);

        return response()->json([
            'data' => $reports->getCollection()
                ->map(fn (DailyReport $report): array => $this->serialize($report))
                ->values(),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    public function store(StoreDailyReportRequest $request): JsonResponse
    {
        $dailyReport = DailyReport::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ])->load('user:id,name');

        return response()->json([
            'data' => $this->serialize($dailyReport),
        ], Response::HTTP_CREATED);
    }

    public function update(
        UpdateDailyReportRequest $request,
        DailyReport $dailyReport
    ): JsonResponse {
        $this->authorize('update', $dailyReport);
        $dailyReport->update($request->validated());

        return response()->json([
            'data' => $this->serialize($dailyReport->load('user:id,name')),
        ]);
    }

    public function destroy(DailyReport $dailyReport): Response
    {
        $this->authorize('delete', $dailyReport);
        $dailyReport->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function serialize(DailyReport $dailyReport): array
    {
        return [
            'id' => $dailyReport->id,
            'reported_at' => $dailyReport->reported_at->format('Y-m-d'),
            'title' => $dailyReport->title,
            'content' => $dailyReport->content,
            'status' => $dailyReport->status,
            'user_id' => $dailyReport->user_id,
            'user' => $dailyReport->user?->only(['id', 'name']),
        ];
    }
}
