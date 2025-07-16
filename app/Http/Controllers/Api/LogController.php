<?php
// app/Http/Controllers/Api/LogController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Barryvdh\DomPDF\Facade\Pdf;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')
            ->when($request->search, function ($q) use ($request) {
                $q->where('module', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            })
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                $q->whereBetween('created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            })
            ->orderBy($request->sortBy ?? 'created_at', $request->sortDesc === 'true' ? 'desc' : 'asc');

        return response()->json([
            'data' => $query->paginate($request->itemsPerPage ?? 10),
        ]);
    }

    public function export(Request $request)
    {
        $filename = 'activity_logs_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(
            new \App\Exports\ActivityLogExport($request->start_date, $request->end_date, $request->search),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                $q->whereBetween('created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            })
            ->when($request->search, function ($q) use ($request) {
                $q->where('module', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->get();

        $pdf = Pdf::loadView('exports.logs-pdf', compact('logs'));

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="activity_logs.pdf"',
        ]);
    }

    public function print(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                $q->whereBetween('created_at', [
                    now()->parse($request->start_date)->startOfDay(),
                    now()->parse($request->end_date)->endOfDay()
                ]);
            })
            ->when($request->search, function ($q) use ($request) {
                $q->where('module', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->get();

        return response()->view('exports.logs-print', ['logs' => $logs]);
    }
}
