<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sikara:make-admin {email?} {--password=} {--name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Super Admin user for SIKARA';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating a new Super Admin...');

        $email = $this->argument('email') ?? $this->ask('Admin Email');
        $name = $this->option('name') ?? $this->ask('Admin Name', 'Super Admin SIKARA');
        $password = $this->option('password') ?? $this->secret('Admin Password');

        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ], [
            'email' => ['required', 'email', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => User::ROLE_ADMIN ?? 'admin',
        ]);

        $this->info("Admin '{$name}' ({$email}) created successfully!");
        return 0;
    }
}
