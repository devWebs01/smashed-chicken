<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupWhatsAppProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup WhatsApp ordering project with database and instructions';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('🚀 Starting WhatsApp Project Setup...');

        // Check database setup for SQLite
        $this->setupDatabase();

        // Run setup commands using chain
        $this->call('chain:run', ['name' => 'setup']);

        $this->info('✅ Database setup completed!');

        // Show instructions
        $this->showSetupInstructions();

        $this->info('🎉 Setup completed! Follow the instructions above to complete setup.');
    }

    private function setupDatabase(): void
    {
        $dbConnection = env('DB_CONNECTION');
        if ($dbConnection === 'sqlite') {
            $dbPath = database_path('database.sqlite');
            if (! file_exists($dbPath)) {
                $this->info('📁 Creating SQLite database file...');
                touch($dbPath);
                $this->info('✅ SQLite database file created!');
            } else {
                $this->info('✅ SQLite database file already exists.');
            }
        }
    }

    private function showSetupInstructions(): void
    {
        $this->info("\n📋 Next Steps:");
        $this->line('1. Start Laravel: php artisan serve');
        $this->line('2. Set your public URL in .env (APP_URL)');
        $this->line('3. Set webhook in Fonnte: ${APP_URL}/webhook/whatsapp');
        $this->line('4. Test: Send "menu" to WhatsApp device');

        $this->info("\n🔑 Environment Variables:");
        $this->line('ACCOUNT_TOKEN=your_fonnte_token (set in .env)');
        $this->line('APP_URL=your_public_url (e.g., https://yourdomain.com)');

        $this->info("\n📚 Available Commands:");
        $this->line('php artisan whatsapp:setup - Full automated setup (run once)');
        $this->line('php artisan tinker - Test models');
    }
}
