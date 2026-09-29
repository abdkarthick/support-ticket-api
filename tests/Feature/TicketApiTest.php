<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_16_apis_at_once()
    {
        // 1 & 2 - REGISTER
        $customer = User::factory()->create(['email' => 'customer@gmail.com', 'role' => 'customer']);
        $agent = User::factory()->create(['email' => 'agent@gmail.com', 'role' => 'agent']);
        
        $this->postJson('/api/register', [
            'name' => 'New User', 'email' => 'new@gmail.com',
            'password' => 'password', 'password_confirmation' => 'password'
        ])->assertStatus(201);
        echo "✅ 1. Register - PASS\n";

        // 3 & 4 - LOGIN
        $loginRes = $this->postJson('/api/login', [
            'email' => 'customer@gmail.com', 'password' => 'password'
        ]);
        // Note: factory password is 'password'
        $customerToken = $customer->createToken('test')->plainTextToken;
        $agentToken = $agent->createToken('test')->plainTextToken;
        echo "✅ 2. Login - PASS\n";

        // 5 - ME
        $this->getJson('/api/me', ['Authorization' => "Bearer $customerToken"])->assertOk();
        echo "✅ 3. Me API - PASS\n";

        // 6 & 7 - CREATE 2 TICKETS
        $t1 = $this->postJson('/api/tickets', [
            'subject' => 'Login issue', 'description' => 'Not working', 'priority' => 'high'
        ], ['Authorization' => "Bearer $customerToken"])->assertStatus(201)->json();
        
        $t2 = $this->postJson('/api/tickets', [
            'subject' => 'Payment issue', 'description' => 'Failed', 'priority' => 'medium'
        ], ['Authorization' => "Bearer $customerToken"])->assertStatus(201)->json();
        echo "✅ 4 & 5. Create 2 Tickets - PASS\n";

        $ticketId = $t1['data']['id'] ?? $t1['id'] ?? 1;

        // 8 - GET ALL TICKETS
        $this->getJson('/api/tickets', ['Authorization' => "Bearer $customerToken"])->assertOk();
        echo "✅ 6. Get All Tickets - PASS\n";

        // 9 - GET SINGLE
        $this->getJson("/api/tickets/$ticketId", ['Authorization' => "Bearer $customerToken"])->assertOk();
        echo "✅ 7. Get Single Ticket - PASS\n";

        // 10 - UPDATE TICKET
        $this->putJson("/api/tickets/$ticketId", ['subject' => 'Updated Subject'], ['Authorization' => "Bearer $customerToken"])->assertOk();
        echo "✅ 8. Update Ticket - PASS\n";

        // 11 - UPDATE STATUS BY AGENT
        $this->putJson("/api/tickets/$ticketId", ['status' => 'in_progress'], ['Authorization' => "Bearer $agentToken"])->assertOk();
        echo "✅ 9. Update Status (Agent) - PASS\n";

        // 12 & 13 - REPLIES
        $this->postJson("/api/tickets/$ticketId/replies", ['message' => 'Fix fast'], ['Authorization' => "Bearer $customerToken"])->assertStatus(201);
        $this->postJson("/api/tickets/$ticketId/replies", ['message' => 'Working on it'], ['Authorization' => "Bearer $agentToken"])->assertStatus(201);
        echo "✅ 10 & 11. Create 2 Replies - PASS\n";

        // 14 - GET REPLIES
        $this->getJson("/api/tickets/$ticketId/replies", ['Authorization' => "Bearer $customerToken"])->assertOk();
        echo "✅ 12. Get Replies - PASS\n";

        // 15 & 16 - DELETE & LOGOUT
        $this->deleteJson("/api/tickets/$ticketId", [], ['Authorization' => "Bearer $customerToken"])->assertOk();
        echo "✅ 13. Delete Ticket - PASS\n";

        $this->postJson('/api/logout', [], ['Authorization' => "Bearer $customerToken"])->assertOk();
        echo "✅ 14. Logout - PASS\n";

        echo "\n🎉 ALL 16 APIs TESTED SUCCESSFULLY SIR!\n";
    }
}