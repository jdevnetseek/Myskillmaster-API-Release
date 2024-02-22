<?php

namespace App\Http\Controllers\V1\Auth\Connect;

use App\Models\User;
use App\Models\LessonEnrollment;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\IncompleteStripeConnectPayout;
use App\Services\Payout\BalanceService;

class BalanceController extends Controller
{
    public function __invoke()
    {
        /** @var \App\Models\User */
        $user = auth()->user();

        throw_if(
            $user->hasStripeConnectId() == false,
            IncompleteStripeConnectPayout::class
        );

        $balance = resolve(BalanceService::class, ['user' => $user])->get();

        $enrollmentsThisCurrentWeek = $this->paidLessonsForTheCurrentWeek($user);

        $availableAmount = data_get($balance, 'available.amount');

        $data = [
            'current_week' => [
                'total_lessons' => $enrollmentsThisCurrentWeek->count(),
                'total_earnings' => $enrollmentsThisCurrentWeek->sum('master_earnings'),
            ],
            'available' => [
                'amount' => $availableAmount < 0 ? 0 : $availableAmount,
                'currency' => data_get($balance, 'available.currency'),
            ],
            'pending' => [
                'amount' => data_get($balance, 'pending.amount'),
                'currency' => data_get($balance, 'pending.currency'),
            ],
        ];

        return response()->json(['data' => $data]);
    }

    private function paidLessonsForTheCurrentWeek(User $user): Collection
    {
        return LessonEnrollment::asMaster($user)
            ->whereBetween('paid_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereNull('refunded_at')
            ->get();
    }
}
