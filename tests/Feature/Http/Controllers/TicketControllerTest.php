<?php

namespace Tests\Feature\Http\Controllers;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use Database\Seeders\TicketStatusSeeder;
use App\Http\Controllers\TicketController;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use App\Http\Requests\Ticket\TicketStoreRequest;
use App\Http\Requests\Ticket\TicketDeleteRequest;
use App\Http\Requests\Ticket\TicketUpdateRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketControllerTest extends TestCase
{
    use RefreshDatabase, AdditionalAssertions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TicketStatusSeeder::class);
    }

    /**
     * @test
     */
    public function index_return_tickets_list(): void
    {
        $ticketsCount = rand(1,10);
        Ticket::factory()->count($ticketsCount)->create();
        $response = $this->getJson(action([TicketController::class, 'index']));

        $response->assertOk();
        $response->assertJsonStructure([]);
        $response->assertJsonCount($ticketsCount, "body.data");
    }

    /** @test  */
    public function index_paginate_tickets_list()
    {
        Ticket::factory()->count(60)->create();
        $response = $this->getJson(action([TicketController::class, 'index']));

        $response->assertOk();
        $response->assertJsonStructure([]);
        $response->assertJsonCount(15, "body.data");

        $response->assertOk();

        $response->assertSee('Next');

        $response->assertJsonFragment([
            "last_page" =>  4,
            "total" =>  60,
        ]);
    }

    /** @test  */
    public function index_filter_by_id()
    {
        $ticketOne = Ticket::factory()->create();
        $ticketTwo = Ticket::factory()->create();

        $response = $this->getJson(action([TicketController::class, 'index'], ['id' => $ticketOne->id]));

        $response->assertOk();
        $response->assertJsonStructure([]);
        $response->assertJsonCount(1, "body.data");

        $response->assertJsonFragment(["id" => $ticketOne->id]);
        $response->assertJsonMissing(["id" => $ticketTwo->id]);
    }

    /** @test  */
    public function index_filter_by_ids()
    {
        $tickets = Ticket::factory()->count(10)->create();
        $ticketsIds = implode(',', $tickets->pluck('id')->toArray());

        $ticketMissing = Ticket::factory()->create();

        $response = $this->getJson(action([TicketController::class, 'index'], ['ids' => $ticketsIds]));

        $response->assertOk();
        $response->assertJsonStructure([]);
        $response->assertJsonCount(10, "body.data");

        $response->assertJsonFragment(["id" => $tickets[0]->id]);
        $response->assertJsonMissing(["id" => $ticketMissing->id]);
    }

    /** @test  */
    public function index_filter_by_user_id()
    {
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        Ticket::factory()->count(5)->create(['user_id' => $userOne->id]);
        Ticket::factory()->count(5)->create(['user_id' => $userTwo->id]);
        $response = $this->getJson(action([TicketController::class, 'index'], ['user_id' => $userOne->id]));

        $response->assertOk();
        $response->assertJsonStructure([]);
        $response->assertJsonCount(5, "body.data");

        $response->assertJsonFragment(["user" => $userOne->name]);
        $response->assertJsonMissing(["user" => $userTwo->name]);
    }

    /** @test  */
    public function index_filter_by_status_id()
    {
        Ticket::factory()->count(5)->create(['ticket_status_id' => 1]);
        Ticket::factory()->count(5)->create(['ticket_status_id' => 2]);

        $response = $this->getJson(action([TicketController::class, 'index'], ['ticket_status_id' => 1]));

        $response->assertOk();
        $response->assertJsonStructure([]);
        $response->assertJsonCount(5, "body.data");

        $response->assertJsonFragment(["status" => 'Abierto']);
        $response->assertJsonMissing(["status" => 'Cerrado']);
    }

    /** @test  */
    public function index_filter_by_init_created()
    {
        $dateOne = Carbon::parse('2023-10-30')->format('Y-m-d h:i:s');
        $dateTwo =  Carbon::parse('2023-10-20')->format('Y-m-d h:i:s');

        Ticket::factory()->count(5)->create(['created_at' => $dateOne]);
        Ticket::factory()->count(5)->create(['created_at' => $dateTwo]);

        $response = $this->getJson(action([TicketController::class, 'index'], ['from' => $dateOne]));

        $response->assertOk();
        $response->assertJsonStructure([]);
        $response->assertJsonCount(5, "body.data");

        $response->assertOk();

        $response->assertJsonFragment(["created_at" => $dateOne]);
        $response->assertJsonMissing(["created_at" => $dateTwo]);
    }

    /** @test  */
    public function index_filter_by_end_created()
    {
        $dateOne = Carbon::parse('2023-10-30')->format('Y-m-d h:i:s');
        $dateTwo =  Carbon::parse('2023-10-20')->format('Y-m-d h:i:s');

        Ticket::factory()->count(5)->create(['created_at' => $dateOne]);
        Ticket::factory()->count(5)->create(['created_at' => $dateTwo]);

        $response = $this->getJson(action([TicketController::class, 'index'], ['to' => $dateTwo]));

        $response->assertOk();
        $response->assertJsonStructure([]);
        $response->assertJsonCount(5, "body.data");

        $response->assertOk();

        $response->assertJsonFragment(["created_at" => $dateTwo]);
        $response->assertJsonMissing(["created_at" => $dateOne]);
    }

    /**
     * @test
     */
    public function store_uses_form_request_validation()
    {
        $this->assertActionUsesFormRequest(
            TicketController::class,
            'store',
            TicketStoreRequest::class
        );
    }

    /**
     * @test
     */
    public function store_saves()
    {
        $user = User::factory()->create();

        $body = [
            "user_id" => $user->id
        ];

        $response = $this->postJson(action([TicketController::class, 'store']), $body);

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertDatabaseHas('tickets', [
            "user_id" => $user->id,
            "ticket_status_id" => 1,
        ]);
    }

    /**
     * @test
     */
    public function update_uses_form_request_validation()
    {
        $this->assertActionUsesFormRequest(
            TicketController::class,
            'update',
            TicketUpdateRequest::class
        );
    }

    /**
     * @test
     */
    public function update_updates()
    {
        $userOne = User::factory()->create();

        $ticket = Ticket::factory()->create(["user_id" => $userOne->id]);

        $userTwo = User::factory()->create();

        $ticketStatusId = rand(1,2);
        $body = [
            "user_id" => $userTwo->id,
            "ticket_status_id" => $ticketStatusId,
        ];

        $response = $this->putJson(action([TicketController::class, 'update'],$ticket->id), $body);

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            "user_id" => $userTwo->id,
            "ticket_status_id" => $ticketStatusId,
        ]);

    }

    /**
     * @test
     */
    public function delete_uses_form_request_validation()
    {
        $this->assertActionUsesFormRequest(
            TicketController::class,
            'destroy',
            TicketDeleteRequest::class
        );
    }

    /**
     * @test
     */
    public function delete_deletes()
    {
        $ticket = Ticket::factory()->create();

        $response = $this->deleteJson(action([TicketController::class, 'destroy'],$ticket->id));

        $response->assertOk();

        $this->assertDatabaseMissing('tickets', [
            'id' => $ticket->id,
            'deleted_at' => null,
        ]);
    }
}

