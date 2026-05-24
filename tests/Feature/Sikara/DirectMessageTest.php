<?php

namespace Tests\Feature\Sikara;

use App\Events\DirectMessageSent;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DirectMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipient_search_follows_role_rules(): void
    {
        $student = User::factory()->mahasiswa()->create(['name' => 'Budi Mahasiswa']);
        $company = User::factory()->perusahaan()->create(['name' => 'Mitra Perusahaan']);
        $admin = User::factory()->admin()->create(['name' => 'Admin Kampus']);
        User::factory()->mahasiswa()->create(['name' => 'Sari Mahasiswa']);
        User::factory()->perusahaan()->create(['name' => 'Perusahaan Nonaktif', 'status' => User::STATUS_INACTIVE]);

        $this->actingAs($student)
            ->getJson('/dm/users?search=perusahaan')
            ->assertOk()
            ->assertJsonCount(1, 'users')
            ->assertJsonPath('users.0.id', $company->id);

        $this->actingAs($company)
            ->getJson('/dm/users?search=mahasiswa')
            ->assertOk()
            ->assertJsonCount(2, 'users')
            ->assertJsonMissing(['id' => $company->id]);

        $this->actingAs($admin)
            ->getJson('/dm/users?search=mahasiswa')
            ->assertOk()
            ->assertJsonCount(2, 'users');
    }

    public function test_conversation_creation_reuses_existing_pair_and_blocks_invalid_recipients(): void
    {
        $student = User::factory()->mahasiswa()->create();
        $company = User::factory()->perusahaan()->create();
        $otherStudent = User::factory()->mahasiswa()->create();
        $inactiveCompany = User::factory()->perusahaan()->create(['status' => User::STATUS_INACTIVE]);

        $first = $this->actingAs($student)
            ->postJson('/dm/conversations', ['recipient_id' => $company->id])
            ->assertCreated()
            ->json('conversation');

        $second = $this->actingAs($company)
            ->postJson('/dm/conversations', ['recipient_id' => $student->id])
            ->assertOk()
            ->json('conversation');

        $this->assertSame($first['id'], $second['id']);
        $this->assertDatabaseCount('conversations', 1);

        $this->actingAs($student)
            ->postJson('/dm/conversations', ['recipient_id' => $student->id])
            ->assertUnprocessable();

        $this->actingAs($student)
            ->postJson('/dm/conversations', ['recipient_id' => $otherStudent->id])
            ->assertForbidden();

        $this->actingAs($student)
            ->postJson('/dm/conversations', ['recipient_id' => $inactiveCompany->id])
            ->assertForbidden();
    }

    public function test_non_participants_cannot_access_send_or_read_conversation(): void
    {
        $student = User::factory()->mahasiswa()->create();
        $company = User::factory()->perusahaan()->create();
        $outsider = User::factory()->admin()->create();

        $conversation = Conversation::createBetween($student, $company, $student);

        $this->actingAs($outsider)
            ->getJson("/dm/conversations/{$conversation->id}/messages")
            ->assertForbidden();

        $this->actingAs($outsider)
            ->postJson("/dm/conversations/{$conversation->id}/messages", ['body' => 'Halo'])
            ->assertForbidden();

        $this->actingAs($outsider)
            ->postJson("/dm/conversations/{$conversation->id}/read")
            ->assertForbidden();
    }

    public function test_sending_message_updates_conversation_broadcasts_event_and_marks_read_state(): void
    {
        Event::fake([DirectMessageSent::class]);

        $student = User::factory()->mahasiswa()->create();
        $company = User::factory()->perusahaan()->create();

        $conversation = Conversation::createBetween($student, $company, $student);

        $this->actingAs($student)
            ->postJson("/dm/conversations/{$conversation->id}/messages", [
                'body' => 'Halo, saya tertarik dengan lowongan magang.',
            ])
            ->assertCreated()
            ->assertJsonPath('message.body', 'Halo, saya tertarik dengan lowongan magang.');

        $message = ConversationMessage::query()->firstOrFail();
        $conversation->refresh();

        $this->assertSame($message->id, $conversation->last_message_id);
        $this->assertNotNull($conversation->last_message_at);

        Event::assertDispatched(DirectMessageSent::class, function (DirectMessageSent $event) use ($message, $company) {
            return $event->message->is($message)
                && $event->recipient->is($company);
        });

        $this->actingAs($company)
            ->getJson('/dm/conversations')
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('conversations.0.unread_count', 1);

        $this->actingAs($company)
            ->postJson("/dm/conversations/{$conversation->id}/read")
            ->assertOk()
            ->assertJsonPath('unread_count', 0);

        $this->actingAs($company)
            ->getJson('/dm/conversations')
            ->assertJsonPath('unread_count', 0)
            ->assertJsonPath('conversations.0.unread_count', 0);
    }
}
