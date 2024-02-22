<?php

namespace App\Http\Controllers\V1;

use App\Models\Device;
use App\Enums\ErrorCodes;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeviceRequest;
use App\Http\Resources\DeviceResource;

class DeviceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  DeviceRequest  $request
     * @return Response
     */
    public function store(DeviceRequest $request)
    {
        $user = auth()->user();

        $device = $user->devices()->firstOrNew(
            ['token' => $request->device_token],
            ['device_id' => $request->device_id],
            ['user_agent' => $request->userAgent()]
        );

        $device->save();

        return new DeviceResource($device);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeviceRequest $request
     * @return Response
     */
    public function destroy(DeviceRequest $request)
    {
        $device = Device::where('device_id', $request->device_id)
            ->when($request->has('device_token'), function ($query) use ($request) {
                $query->where('token', $request->device_token);
            })
            ->delete();

        return response()->json([
            'message' => 'Token Deleted',
        ], 200);
    }
}
