<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\Ticket\TicketResource;
use App\Http\Requests\Ticket\TicketStoreRequest;
use App\Http\Requests\Ticket\TicketDeleteRequest;
use App\Http\Requests\Ticket\TicketUpdateRequest;

class TicketController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index()
    {
        $tickets = Ticket::filter(request())->paginate();

        return $this->jsonSuccess(
            TicketResource::collection(
                $tickets
            )->response()->getData(true)
        );
    }

    /**
     * @param TicketStoreRequest $request
     * @return JsonResponse
     */
    public function store(TicketStoreRequest $request): JsonResponse
    {
        try {
            $ticket = new Ticket();
            DB::transaction(function () use ($request,&$ticket){
                $ticket = $ticket->create( $request->all() );
            });
            $ticket->refresh();

            return $this->jsonSuccess(
                TicketResource::make($ticket),
                200,
                'Ticket created successfully'
            );
        } catch (\Throwable $th) {
            return $this->jsonError(null, $th->getCode(), $th->getMessage());
        }


    }

    /**
     * @param TicketUpdateRequest $request
     * @param integer $id
     * @return JsonResponse
     */
    public function update(TicketUpdateRequest $request, $id): JsonResponse
    {
        try {
            $ticket = Ticket::where('id', $id)->first();

            DB::transaction(function () use ($request, $ticket){
                $ticket = $ticket->update( $request->all() );
            });

            $ticket->refresh();

            return $this->jsonSuccess(
                TicketResource::make($ticket),
                200,
                'Ticket updated successfully'
            );
        } catch (\Throwable $th) {
            return $this->jsonError(null, $th->getCode(), $th->getMessage());
        }
    }

    /**
     * @param integer $id
     * @return JsonResponse
     */
    public function destroy(TicketDeleteRequest $request, $id): JsonResponse
    {
        try {
            $ticket = Ticket::where('id', $id)->first();

            DB::transaction(function () use ($ticket){
                $ticket->delete();
            });

            return $this->jsonSuccess();
        } catch (\Throwable $th) {
            return $this->jsonError(null, $th->getCode(), $th->getMessage());
        }
    }
}
