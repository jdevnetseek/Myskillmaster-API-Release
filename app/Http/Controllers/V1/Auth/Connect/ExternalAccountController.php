<?php

namespace App\Http\Controllers\V1\Auth\Connect;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Stripe\Exception\ApiErrorException;

class ExternalAccountController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /** @var User */
        $users = auth()->user();

        return  $users->allExternalAccounts($request->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'external_account' => ['required']
        ]);

        /** @var User */
        $users = auth()->user();

        return JsonResource::make($users->createExternalAccount($request->input('external_account')));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        /** @var User */
        $user = auth()->user();

        return JsonResource::make($user->updateExternalAccount($id, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = auth()->user();

        try {
            $user->deleteExternalAccount($id);

        } catch (ApiErrorException $e) {
            $error = $e->getMessage();

            if ($user->getExternalAccount($id)->default_for_currency) {
                $error = __('error_messages.stripe_external_account.cannot_delete_default');
            }

            abort($e->getHttpStatus(), $error);
        }

        return response()->json([], Response::HTTP_OK);
    }
}
