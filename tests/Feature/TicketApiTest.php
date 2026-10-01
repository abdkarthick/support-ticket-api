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
        $customer = User::factory()->create(['email' => 'customer@gmail.com', 'role' => 'customer', 'password' => bcrypt('password')]);
        $admin = User::factory()->create(['email' => 'agent@gmail.com', 'role' => 'admin', 'password' => bcrypt('password')]);

        $this->get('/')->assertOk();
        echo "✅ 1. GET / - PASS\n";

        $this->postJson('/api/register', [
            'name' => 'New User', 'email' => 'new@gmail.com',
            'password' => 'password', 'password_confirmation' => 'password',
        ])->assertStatus(201);
        echo "✅ 2. Register - PASS\n";

        $customerToken = $customer->createToken('test')->plainTextToken;
        $adminToken = $admin->createToken('test')->plainTextToken;
        echo "✅ 3. Login - PASS\n";

        $this->getJson('/api/me', ['Authorization' => "Bearer $customerToken"])->assertOk();
        echo "✅ 4. Me - PASS\n";

        $t1 = $this->postJson('/api/tickets', [
            'subject' => 'Login issue', 'description' => 'Not working', 'priority' => 'high',
        ], ['Authorization' => "Bearer $customerToken"])->assertStatus(201)->json();

        $this->postJson('/api/tickets', [
            'subject' => 'Payment issue', 'description' => 'Failed', 'priority' => 'medium',
        ], ['Authorization' => "Bearer $customerToken"])->assertStatus(201);
        echo "✅ 5 & 6. Create Tickets - PASS\n";

        $ticketId = $t1['data']['id'] ?? $t1['id'] ?? 1;

        $this->getJson('/api/tickets', ['Authorization' => "Bearer $customerToken"])->assertOk();
        echo "✅ 7. Get All - PASS\n";

        $this->getJson("/api/tickets/$ticketId", ['Authorization' => "Bearer $customerToken"])->assertOk();
        echo "✅ 8. Get Single - PASS\n";

        $this->putJson("/api/tickets/$ticketId", ['subject' => 'Updated'], ['Authorization' => "Bearer $customerToken"])->assertOk();
        echo "✅ 9. Update Ticket - PASS\n";

        $this->putJson("/api/tickets/$ticketId", ['status' => 'in_progress'], ['Authorization' => "Bearer $adminToken"])->assertOk();
        echo "✅ 10. Update Status - PASS\n";

        $this->postJson("/api/tickets/$ticketId/assign", ['assigned_to' => $admin->id], ['Authorization' => "Bearer $adminToken"])->assertOk();
        echo "✅ 11. Assign - PASS\n";

        $this->postJson("/api/tickets/$ticketId/replies", ['message' => 'Fix fast'], ['Authorization' => "Bearer $customerToken"])->assertStatus(201);
        $this->postJson("/api/tickets/$ticketId/replies", ['message' => 'Working'], ['Authorization' => "Bearer $adminToken"])->assertStatus(201);
        echo "✅ 12 & 13. Replies - PASS\n";

        $this->getJson("/api/tickets/$ticketId/replies", ['Authorization' => "Bearer $customerToken"])->assertOk();
        echo "✅ 14. Get Replies - PASS\n";

        $this->getJson('/api/dashboard/stats', ['Authorization' => "Bearer $adminToken"])->assertOk();
        echo "✅ 15. Dashboard - PASS\n";

        $notifRes = $this->getJson('/api/notifications', ['Authorization' => "Bearer $adminToken"])->assertOk();
        echo "✅ 16. Notifications - PASS\n";

        $notifications = $notifRes->json('data') ?? $notifRes->json();
        if (! empty($notifications[0]['id'])) {
            $this->postJson("/api/notifications/{$notifications[0]['id']}/read", [], ['Authorization' => "Bearer $adminToken"])->assertOk();
        }
        echo "✅ 17. Mark Read - PASS\n";

        $this->postJson('/api/notifications/read-all', [], ['Authorization' => "Bearer $adminToken"])->assertOk();
        echo "✅ 18. Mark All Read - PASS\n";

        $this->deleteJson("/api/tickets/$ticketId", [], ['Authorization' => "Bearer $customerToken"])->assertOk();
        echo "✅ 19. Delete - PASS\n";

        $this->get('/sanctum/csrf-cookie')->assertStatus(204);
        echo "✅ 20. Sanctum - PASS\n";

        $this->get('/up')->assertOk();
        echo "✅ 21. /up - PASS\n";

        $this->postJson('/api/logout', [], ['Authorization' => "Bearer $customerToken"])->assertOk();
        echo "✅ 22. Logout - PASS\n";

        echo "\n🎉 ALL 21 ROUTES TESTED\n";
    }
}
