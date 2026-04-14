<?php

namespace Furqanamx\JitsiLaravelMeet\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class JitsiController extends Controller
{
    public function timeRemaining(Request $request, string $code): JsonResponse
    {
        $meeting = $this->getMeetingByCode($code);

        if (!$meeting) {
            return response()->json(['error' => 'Meeting not found'], 404);
        }

        if ($this->userNotAuthorized($meeting, $request->user())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $startTime = $meeting->start_time;
        $endTime = $meeting->end_time;
        $now = Carbon::now();

        if ($now->lessThan($startTime)) {
            return response()->json([
                'remaining_seconds' => 0,
                'before_start_seconds' => $now->diffInSeconds($startTime),
            ]);
        }

        if ($now->greaterThanOrEqualTo($endTime)) {
            return response()->json([
                'remaining_seconds' => 0,
                'before_start_seconds' => 0,
            ]);
        }

        return response()->json([
            'remaining_seconds' => $now->diffInSeconds($endTime),
            'before_start_seconds' => 0,
        ]);
    }

    protected function getMeetingByCode(string $code): ?object
    {
        $model = config('jitsi.meeting_model');

        if (!$model || !class_exists($model)) {
            $model = $this->getDefaultMeetingModel();
        }
        
        if (method_exists($model, 'findByCode')) {
            return forward_static_call([$model, 'findByCode'], $code);
        }

        return forward_static_call([$model, 'where'], 'code', $code)->first();
    }

    protected function getDefaultMeetingModel(): string
    {
        return class_exists(\App\Models\Meeting::class) 
            ? \App\Models\Meeting::class 
            : \App\Meeting::class;
    }

    protected function userNotAuthorized(object $meeting, mixed $user): bool
    {
        if (config('jitsi.authorize', true) === false) {
            return false;
        }

        if (method_exists($meeting, 'isUserParticipant')) {
            return !$meeting->isUserParticipant($user);
        }

        if (config('jitsi.check_user_meeting', true)) {
            return $meeting->user_id !== $user?->id;
        }

        return false;
    }
}
