<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Http\Controllers\UserController;
use Database\Seeders\TicketStatusSeeder;
use App\Http\Requests\User\UserStoreRequest;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Requests\User\UserDeleteRequest;
use App\Http\Requests\User\UserUpdateRequest;
use JMac\Testing\Traits\AdditionalAssertions;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
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
    public function index_return_users_list(): void
    {
        $usersCount = rand(1,10);
        User::factory()->count($usersCount)->create();
        $response = $this->getJson(action([UserController::class, 'index']));

        $response->assertOk();
        $response->assertJsonStructure([]);
        $response->assertJsonCount($usersCount, "body.data");
    }

    /**
     * @test
     */
    public function store_uses_form_request_validation()
    {
        $this->assertActionUsesFormRequest(
            UserController::class,
            'store',
            UserStoreRequest::class
        );
    }

    /**
     * @test
     */
    public function store_saves()
    {
        $body = [
            "name" => "cliente uno",
            "email" => "clienteone@woh.com",
            "password" => 123456,
        ];

        $response = $this->postJson(action([UserController::class, 'store']), $body);

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertDatabaseHas('users', [
            'name' => $body['name'],
            'email' => $body['email'],
        ]);
    }

    /**
     * @test
     */
    public function update_uses_form_request_validation()
    {
        $this->assertActionUsesFormRequest(
            UserController::class,
            'update',
            UserUpdateRequest::class
        );
    }

    /**
     * @test
     */
    public function update_updates()
    {
        $user = User::factory()->create();
        $body = [
            "name" => "cliente uno",
            "email" => "clienteone@woh.com",
            "password" => 123456
        ];

        $response = $this->putJson(action([UserController::class, 'update'],$user->id), $body);

        $response->assertOk();
        $response->assertJsonStructure([]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $body['name'],
            'email' => $body['email'],
        ]);

    }

    /**
     * @test
     */
    public function delete_uses_form_request_validation()
    {
        $this->assertActionUsesFormRequest(
            UserController::class,
            'destroy',
            UserDeleteRequest::class
        );
    }

    /**
     * @test
     */
    public function delete_deletes()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson(action([UserController::class, 'destroy'],$user->id));

        $response->assertOk();

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }
}
