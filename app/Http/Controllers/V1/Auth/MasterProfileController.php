<?php

namespace App\Http\Controllers\V1\Auth;

use App\Actions\AddFreeTrial;
use App\Exceptions\MasterProfile\MasterProfileException as HttpMasterProfileException;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\MasterProfileRequest;
use App\Http\Resources\MasterProfileResource;
use App\Services\MasterProfile\Exceptions\MasterProfileException;
use App\Services\Subscription\MasterProfileService;
use Stripe\Exception\ApiErrorException;

class MasterProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->only(['destroy']);
    }

    public function show()
    {
        $masterProfile = request()->user()->masterProfile()->firstOrCreate();

        return MasterProfileResource::make(
            $masterProfile->load('portfolio', 'languages')
        );
    }

    public function update(MasterProfileRequest $request, AddFreeTrial $addFreeTrial)
    {
        $user = $request->user();

        /** @var App\Models\MasterProfile */
        $masterProfile = DB::transaction(function () use ($request, $user, $addFreeTrial) {
            $masterProfile = $user->setMasterProfile($request->validated());

            return $masterProfile;
        });

        return MasterProfileResource::make(
            $masterProfile->load('portfolio', 'languages')
        );
    }

    public function checkProfileAvailability()
    {
        $masterProfile = request()->user()->masterProfile()->exists();

        return response()->json(['data' => ['is_already_setup' => $masterProfile]]);
    }

    public function destroy()
    {
        $user = request()->user();
        try {
            resolve(MasterProfileService::class, ['user' => $user])
                ->removeMasterProfile();

            return response()->json(['message' => 'Master profile deleted successfully']);
        } catch (MasterProfileException $e) {
            throw new HttpMasterProfileException($e->getMessage());
        } catch (\Exception $e) {
            throw new HttpMasterProfileException($e->getMessage());
        }
    }
}
