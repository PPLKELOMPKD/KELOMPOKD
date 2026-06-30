<?php

namespace Tests\Browser\Sikara;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Schema;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SKR30RealTimeDirectMessagingTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected static bool $initialized = false;

    protected function setUp(): void
    {
        parent::setUp();

        
        if (! self::$initialized) {
            sleep(8);
            self::$initialized = true;
        } else {
            sleep(1);
        }
    }

    protected function tearDown(): void
    {
        
        try {
            $this->browse(function (Browser $browser) {
                $browser->blank();
            });
        } catch (\Throwable $e) {
            
        }

        parent::tearDown();
    }

    
    protected function openMessageWidget(Browser $browser): Browser
    {
        return $browser->waitFor('button[aria-label="Buka pesan"]', 15)
            ->click('button[aria-label="Buka pesan"]')
            ->waitFor('section[aria-label="Pesan SIKARA"]', 10);
    }

    
    protected function clickConversation(Browser $browser, string $recipientName): Browser
    {
        $recipientJson = json_encode($recipientName);
        $browser->script("
            var buttons = document.querySelectorAll('section[aria-label=\"Pesan SIKARA\"] button');
            var clicked = false;
            var targetName = {$recipientJson};
            for (var i = 0; i < buttons.length; i++) {
                if (buttons[i].textContent.indexOf(targetName) !== -1) {
                    buttons[i].click();
                    clicked = true;
                    break;
                }
            }
            if (!clicked) {
                throw new Error('Conversation button for ' + targetName + ' not found');
            }
        ");

        return $browser->pause(1000);
    }

    
    public function test_tc01_menampilkan_halaman_pesan(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard');

            $this->openMessageWidget($browser);

            $browser->assertSee('Pesan SIKARA');
        });
    }

    
    public function test_tc02_menampilkan_daftar_percakapan(): void
    {
        $user1 = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user2 = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        
        Conversation::createBetween($user1, $user2, $user1);

        $this->browse(function (Browser $browser) use ($user1, $user2) {
            $browser->loginAs($user1)
                ->visit('/dashboard');

            $this->openMessageWidget($browser);

            $browser->waitForText($user2->name, 15)
                ->assertSee($user2->name);
        });
    }

    
    public function test_tc03_menampilkan_riwayat_pesan(): void
    {
        $user1 = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user2 = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $conversation = Conversation::createBetween($user1, $user2, $user1);

        
        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user2->id,
            'body' => 'Hello student, how are you?',
        ]);

        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user1->id,
            'body' => 'I am fine, thank you!',
        ]);

        $this->browse(function (Browser $browser) use ($user1, $user2) {
            $browser->loginAs($user1)
                ->visit('/dashboard');

            $this->openMessageWidget($browser);
            $browser->waitForText($user2->name, 15);
            $this->clickConversation($browser, $user2->name);

            $browser->waitForText('Hello student, how are you?', 15)
                ->assertSee('Hello student, how are you?')
                ->assertSee('I am fine, thank you!');
        });
    }

    
    public function test_tc04_mengirim_pesan_berhasil(): void
    {
        $user1 = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user2 = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $conversation = Conversation::createBetween($user1, $user2, $user1);

        $testMessage = 'Ini pesan tes otomatis Dusk: ' . uniqid();

        $this->browse(function (Browser $browser) use ($user1, $user2, $testMessage) {
            $browser->loginAs($user1)
                ->visit('/dashboard');

            $this->openMessageWidget($browser);
            $browser->waitForText($user2->name, 15);
            $this->clickConversation($browser, $user2->name);

            $browser->waitFor('textarea[placeholder="Tulis pesan..."]', 15)
                ->type('textarea[placeholder="Tulis pesan..."]', $testMessage)
                ->click('button[title="Kirim"]')
                ->waitForText($testMessage, 15)
                ->assertSee($testMessage);
        });

        $this->assertDatabaseHas('conversation_messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $user1->id,
            'body' => $testMessage,
        ]);
    }

    
    public function test_tc05_menerima_pesan_secara_real_time(): void
    {
        $user1 = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user2 = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $conversation = Conversation::createBetween($user1, $user2, $user1);

        $realTimeMessage = 'Pesan real-time simulasi: ' . uniqid();

        $this->browse(function (Browser $browser) use ($user1, $user2, $conversation, $realTimeMessage) {
            $browser->loginAs($user1)
                ->visit('/dashboard');

            
            $browser->script('
                window.mockEchoListeners = window.mockEchoListeners || {};
                window.Echo = {
                    private: function(channel) {
                        return {
                            listen: function(event, callback) {
                                window.mockEchoListeners[channel] = window.mockEchoListeners[channel] || {};
                                window.mockEchoListeners[channel][event] = callback;
                                return this;
                            }
                        };
                    },
                    leave: function(channel) {}
                };
            ');

            $this->openMessageWidget($browser);
            $browser->waitForText($user2->name, 15);
            $this->clickConversation($browser, $user2->name);

            
            $browser->waitFor('textarea[placeholder="Tulis pesan..."]', 15);

            $payloadJson = json_encode([
                'message' => [
                    'id' => 99999,
                    'conversation_id' => $conversation->id,
                    'sender_id' => $user2->id,
                    'body' => $realTimeMessage,
                    'created_at' => now()->toISOString(),
                    'created_at_human' => now()->format('H:i'),
                    'sender' => [
                        'id' => $user2->id,
                        'name' => $user2->name,
                        'role' => $user2->role,
                    ]
                ]
            ]);

            
            $browser->script("
                var callback = window.mockEchoListeners['conversations.{$conversation->id}'] &&
                               window.mockEchoListeners['conversations.{$conversation->id}']['.direct-message.sent'];
                if (callback) {
                    callback({$payloadJson});
                } else {
                    console.error('Echo callback not found for conversations.{$conversation->id}');
                }
            ");

            $browser->waitForText($realTimeMessage, 15)
                ->assertSee($realTimeMessage);
        });
    }

    
    public function test_tc06_validasi_pesan_kosong(): void
    {
        $user1 = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user2 = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        Conversation::createBetween($user1, $user2, $user1);

        $this->browse(function (Browser $browser) use ($user1, $user2) {
            $browser->loginAs($user1)
                ->visit('/dashboard');

            $this->openMessageWidget($browser);
            $browser->waitForText($user2->name, 15);
            $this->clickConversation($browser, $user2->name);

            $browser->waitFor('textarea[placeholder="Tulis pesan..."]', 15)
                ->assertAttribute('button[title="Kirim"]', 'disabled', 'true')
                ->type('textarea[placeholder="Tulis pesan..."]', '   ') 
                ->assertAttribute('button[title="Kirim"]', 'disabled', 'true');
        });
    }

    
    public function test_tc07_penerima_tidak_ditemukan(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard');

            $this->openMessageWidget($browser)
                ->click('button[title="Pesan Baru"]')
                ->waitFor('input[placeholder="Cari penerima baru..."]', 15)
                ->type('input[placeholder="Cari penerima baru..."]', 'user-tidak-ada-sama-sekali-1234')
                ->waitForText('User tidak ditemukan.', 15)
                ->assertSee('User tidak ditemukan.');
        });
    }

    
    public function test_tc08_user_tidak_berhak_mengakses_percakapan(): void
    {
        $user1 = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user2 = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user3 = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        
        $conversation = Conversation::createBetween($user2, $user3, $user2);

        $this->browse(function (Browser $browser) use ($user1, $conversation) {
            $browser->loginAs($user1)
                ->visit('/dm/conversations/' . $conversation->id . '/messages')
                ->waitForText('403', 15);
        });
    }

    
    public function test_tc09_koneksi_real_time_terputus(): void
    {
        $user1 = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user2 = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $conversation = Conversation::createBetween($user1, $user2, $user1);

        $testMessage = 'Pesan tanpa koneksi real-time: ' . uniqid();

        $this->browse(function (Browser $browser) use ($user1, $user2, $testMessage) {
            $browser->loginAs($user1)
                ->visit('/dashboard');

            
            $browser->script('window.Echo = undefined;');

            $this->openMessageWidget($browser);
            $browser->waitForText($user2->name, 15);
            $this->clickConversation($browser, $user2->name);

            $browser->waitFor('textarea[placeholder="Tulis pesan..."]', 15)
                ->type('textarea[placeholder="Tulis pesan..."]', $testMessage)
                ->click('button[title="Kirim"]')
                ->waitForText($testMessage, 15)
                ->assertSee($testMessage);
        });

        $this->assertDatabaseHas('conversation_messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $user1->id,
            'body' => $testMessage,
        ]);
    }

    
    public function test_tc10_pesan_gagal_dikirim_karena_server_error(): void
    {
        $user1 = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $user2 = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $conversation = Conversation::createBetween($user1, $user2, $user1);

        $errorMessage = 'Pesan gagal dikirim db error: ' . uniqid();

        try {
            $this->browse(function (Browser $browser) use ($user1, $user2, $errorMessage) {
                $browser->loginAs($user1)
                    ->visit('/dashboard');

                $this->openMessageWidget($browser);
                $browser->waitForText($user2->name, 15);
                $this->clickConversation($browser, $user2->name);

                $browser->waitFor('textarea[placeholder="Tulis pesan..."]', 15)
                    ->type('textarea[placeholder="Tulis pesan..."]', $errorMessage);

                
                Schema::rename('conversation_messages', 'conversation_messages_sabotaged');

                $browser->click('button[title="Kirim"]')
                    ->pause(3000); 
            });
        } finally {
            
            if (Schema::hasTable('conversation_messages_sabotaged')) {
                Schema::rename('conversation_messages_sabotaged', 'conversation_messages');
            }
        }

        $this->assertDatabaseMissing('conversation_messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $user1->id,
            'body' => $errorMessage,
        ]);
    }
}
