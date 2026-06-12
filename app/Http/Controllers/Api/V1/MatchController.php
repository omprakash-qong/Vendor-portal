<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\QsMatchRequest;
use App\Models\QsMatchResult;
use App\Services\Matching\VendorMatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MatchController extends Controller
{
    public function __construct(private readonly VendorMatchService $matchService)
    {
    }

    /**
     * POST /api/v1/match
     *
     * Request body:
     * {
     *   "job_id": "uuid",             // optional — QS job reference
     *   "project_name": "Refinery X", // optional
     *   "user_email": "...",           // optional
     *   "instruments": [
     *     {
     *       "tag": "M-101",
     *       "equipment_type": "Motor",
     *       "power_kw": 7.5,
     *       "poles": 4,
     *       "efficiency_class": "IE4",
     *       "industry_tags": ["oil_gas"]
     *     }
     *   ]
     * }
     */
    public function match(Request $request): JsonResponse
    {
        $request->validate([
            'instruments'                  => 'required|array|min:1|max:100',
            'instruments.*.equipment_type' => 'required|string',
            'instruments.*.tag'            => 'nullable|string',
        ]);

        $startTime = microtime(true);

        $jobId = $request->input('job_id', (string) Str::uuid());

        // Create request log
        $matchRequest = QsMatchRequest::create([
            'qs_job_id'       => $jobId,
            'qs_project_name' => $request->input('project_name'),
            'qs_user_email'   => $request->input('user_email'),
            'instruments'     => $request->input('instruments'),
            'status'          => 'pending',
            'ip_address'      => $request->ip(),
        ]);

        $results = [];

        foreach ($request->input('instruments') as $instrument) {
            $tag     = $instrument['tag'] ?? 'unknown';
            $matches = $this->matchService->match($instrument, topN: 5);

            // Persist match results
            foreach ($matches as $match) {
                QsMatchResult::create([
                    'request_id'      => $matchRequest->id,
                    'variant_id'      => $match['product']['variant_id'],
                    'vendor_profile_id' => $match['vendor']['id'],
                    'instrument_tag'  => $tag,
                    'instrument_type' => $instrument['equipment_type'],
                    'match_score'     => $match['match_score'],
                    'score_breakdown' => $match['score_breakdown'],
                    'rank'            => $match['rank'],
                ]);
            }

            $results[] = [
                'tag'             => $tag,
                'equipment_type'  => $instrument['equipment_type'],
                'match_count'     => count($matches),
                'matches'         => $matches,
            ];
        }

        $elapsed = (int) ((microtime(true) - $startTime) * 1000);

        $matchRequest->update([
            'status'              => 'completed',
            'processing_time_ms'  => $elapsed,
        ]);

        return response()->json([
            'job_id'              => $jobId,
            'processing_time_ms'  => $elapsed,
            'instrument_count'    => count($results),
            'results'             => $results,
        ]);
    }

    /**
     * GET /api/v1/match/{jobId}
     * Retrieve cached results for a previous job.
     */
    public function results(string $jobId): JsonResponse
    {
        $request = QsMatchRequest::where('qs_job_id', $jobId)->firstOrFail();

        $results = QsMatchResult::where('request_id', $request->id)
            ->with(['variant.series', 'variant.category', 'vendorProfile'])
            ->orderBy('instrument_tag')
            ->orderBy('rank')
            ->get()
            ->groupBy('instrument_tag')
            ->map(fn($group, $tag) => [
                'tag'     => $tag,
                'matches' => $group->map(fn($r) => [
                    'rank'        => $r->rank,
                    'match_score' => $r->match_score,
                    'breakdown'   => $r->score_breakdown,
                    'vendor_id'   => $r->vendor_profile_id,
                    'variant_id'  => $r->variant_id,
                ])->values(),
            ])->values();

        return response()->json([
            'job_id'     => $jobId,
            'status'     => $request->status,
            'created_at' => $request->created_at,
            'results'    => $results,
        ]);
    }
}
