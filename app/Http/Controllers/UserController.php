<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserDeleteRequest;
use App\Http\Requests\User\UserUpdateRequest;

class UserController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->jsonSuccess(
            UserResource::collection(
                User::all()
            )->response()->getData(true)
        );
    }

    /**
     * @param UserStoreRequest $request
     * @return JsonResponse
     */
    public function store(UserStoreRequest $request): JsonResponse
    {
        try {
            DB::transaction(function () use ($request,&$user){
                $user = new User();
                $user = $user->create( $request->all() );
            });

            return $this->jsonSuccess(
                UserResource::make($user),
                200,
                'User created successfully'
            );
        } catch (\Throwable $th) {
            return $this->jsonError(null, $th->getCode(), $th->getMessage());
        }


    }

    /**
     * @param UserUpdateRequest $request
     * @param integer $id
     * @return JsonResponse
     */
    public function update(UserUpdateRequest $request, $id): JsonResponse
    {
        try {
            $user = User::where('id', $id)->first();

            DB::transaction(function () use ($request, $user){
                $user = $user->update( $request->all() );
            });

            $user->refresh();

            return $this->jsonSuccess(
                UserResource::make($user),
                200,
                'User updated successfully'
            );
        } catch (\Throwable $th) {
            return $this->jsonError(null, $th->getCode(), $th->getMessage());
        }
    }

    /**
     * @param integer $id
     * @return JsonResponse
     */
    public function destroy(UserDeleteRequest $request, $id): JsonResponse
    {
        try {
            $user = User::where('id', $id)->first();

            DB::transaction(function () use ($user){
                $user->delete();
            });

            return $this->jsonSuccess();
        } catch (\Throwable $th) {
            return $this->jsonError(null, $th->getCode(), $th->getMessage());
        }
    }
}
