<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

/**
 * Viewer read-only untuk activity_log.
 * Kepatuhan Permenkes 269/2008 — audit trail rekam medis wajib.
 * Akses: AUDITOR & SUPER_ADMIN (routes-level via middleware role).
 */
class AuditLogController extends Controller
{
    private const SORTABLE = ['created_at', 'event', 'subject_type'];

    public function index(): View
    {
        $subjectTypes = Activity::query()
            ->select('subject_type')->distinct()
            ->whereNotNull('subject_type')->pluck('subject_type');

        $causers = User::query()
            ->whereIn('id', Activity::query()->select('causer_id')->distinct()->whereNotNull('causer_id'))
            ->orderBy('name')->get(['id', 'name']);

        return view('audit-log.index', compact('subjectTypes', 'causers'));
    }

    public function data(Request $request): JsonResponse
    {
        $sort = $request->input('sort');
        $order = strtolower($request->input('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage = min(100, max(5, (int) $request->input('per_page', 30)));

        $query = Activity::query()->with('causer')
            ->when($request->input('causer_id'), fn($q, $v) => $q->where('causer_id', $v))
            ->when($request->input('subject_type'), fn($q, $v) => $q->where('subject_type', $v))
            ->when($request->input('event'), fn($q, $v) => $q->where('event', $v))
            ->when($request->input('dari'), fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->input('sampai'), fn($q, $v) => $q->whereDate('created_at', '<=', $v));

        if ($sort && in_array($sort, self::SORTABLE, true)) {
            $query->orderBy($sort, $order);
        } else {
            $query->latest();
        }

        $p = $query->paginate($perPage);

        return response()->json([
            'data' => $p->getCollection()->map(fn ($a) => [
                'id' => $a->id,
                'created_at' => $a->created_at?->format('d M Y H:i:s'),
                'created_date' => $a->created_at?->format('d M Y'),
                'created_time' => $a->created_at?->format('H:i:s'),
                'causer' => $a->causer?->name ?? '(sistem)',
                'event' => $a->event,
                'subject_type' => class_basename($a->subject_type ?? ''),
                'subject_id_short' => substr((string) $a->subject_id, 0, 8),
                'description' => $a->description,
                'properties' => $a->properties?->toArray() ?? [],
            ]),
            'meta' => [
                'current_page' => $p->currentPage(),
                'last_page' => $p->lastPage(),
                'per_page' => $p->perPage(),
                'total' => $p->total(),
                'from' => $p->firstItem(),
                'to' => $p->lastItem(),
            ],
        ]);
    }
}
