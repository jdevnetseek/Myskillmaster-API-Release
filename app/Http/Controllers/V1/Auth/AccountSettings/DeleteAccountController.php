<?php

namespace App\Http\Controllers\V1\Auth\AccountSettings;

use App\Enums\ErrorCodes;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteAccountController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        /** @var User */
        $user = auth()->user();

        if ($user->masterProfile) {
            return $this->respondWithError(
                ErrorCodes::MASTER_PROFILE_ERROR,
                Response::HTTP_FORBIDDEN,
                'You cannot delete your account while you have master profile. Please delete your master profile first.'
            );
        }

        try {
            DB::beginTransaction();

            $user->tokens()->delete();
            $user->passwordReset()->delete();
            $user->filedReports()->delete();
            $user->submittedRatings()->delete();
            $user->enrolledLessons()->delete();
            $user->paymentHistories()->delete();

            $user->delete();

            DB::commit();

            return response()->json([], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->respondWithError(
                ErrorCodes::USER_ACCOUNT_ERROR,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'Failed to delete user account.'
            );
        }
    }
}
