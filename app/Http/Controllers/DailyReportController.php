<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyReportRequest;
use App\Http\Requests\UpdateDailyReportRequest;
use App\Models\DailyReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DailyReportController extends Controller
{
    public function index(): View
    {
        $dailyReports = DailyReport::query()
            ->orderByDesc('reported_at')
            ->orderByDesc('id')
            ->get();

        return view('daily-reports.index', compact('dailyReports'));
    }

    public function create(): View
    {
        return view('daily-reports.create', [
            'dailyReport' => new DailyReport,
        ]);
    }

    public function store(StoreDailyReportRequest $request): RedirectResponse
    {
        DailyReport::create($request->validated());

        return redirect()
            ->route('daily-reports.index')
            ->with('success', '日報を作成しました。');
    }

    public function edit(DailyReport $dailyReport): View
    {
        return view('daily-reports.edit', compact('dailyReport'));
    }

    public function update(
        UpdateDailyReportRequest $request,
        DailyReport $dailyReport
    ): RedirectResponse {
        $dailyReport->update($request->validated());

        return redirect()
            ->route('daily-reports.index')
            ->with('success', '日報を更新しました。');
    }

    public function destroy(DailyReport $dailyReport): RedirectResponse
    {
        $dailyReport->delete();

        return redirect()
            ->route('daily-reports.index')
            ->with('success', '日報を削除しました。');
    }
}
