<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StepSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SubmissionApprovalController extends Controller
{
    public function index(): View
    {
        $pendingSubmissions = StepSubmission::query()
            ->with(['route.game', 'questStep'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $recentReviewedSubmissions = StepSubmission::query()
            ->with(['route.game', 'questStep'])
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('reviewed_at')
            ->limit(120)
            ->get();

        return view('admin.submissions.pending', [
            'pendingSubmissions' => $pendingSubmissions,
            'recentReviewedSubmissions' => $recentReviewedSubmissions,
        ]);
    }

    public function live(): JsonResponse
    {
        $items = StepSubmission::query()
            ->with(['route', 'questStep'])
            ->where('status', 'pending')
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (StepSubmission $submission): array {
                return [
                    'id' => $submission->id,
                    'group' => $submission->route?->name ?? 'Onbekende groep',
                    'quest_step' => $submission->questStep?->title ?? 'Onbekende opdracht',
                ];
            })
            ->values();

        return response()->json([
            'pending_count' => StepSubmission::where('status', 'pending')->count(),
            'items' => $items,
        ]);
    }
}
