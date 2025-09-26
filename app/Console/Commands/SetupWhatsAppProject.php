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
    protected $description = 'Setup WhatsApp ordering project with database, ngrok, and instructions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting WhatsApp Project Setup...');

        // Run setup commands using chain
        $this->call('chain:run', ['name' => 'setup']);

        $this->info('✅ Database setup completed!');

        // Check ngrok
        $this->checkNgrokSetup();

        // Show instructions
        $this->showSetupInstructions();

        $this->info('🎉 Setup completed! Follow the instructions above to complete ngrok setup.');
    }

    private function checkNgrokSetup()
    {
        $this->info('🔍 Checking ngrok setup...');

        // Check if ngrok is installed
        $ngrokCheck = shell_exec('which ngrok 2>/dev/null');
        if (! $ngrokCheck) {
            $this->warn('⚠️  ngrok not found. Installing...');
            $this->installNgrok();

            return;
        }

        // Check if auth token is set
        $authCheck = shell_exec('ngrok config check 2>/dev/null');
        if (strpos($authCheck, 'No authtoken set') !== false) {
            $this->warn('⚠️  ngrok auth token not set.');
            $this->setupNgrokAuth();

            return;
        }

        $this->info('✅ ngrok is ready!');
    }

    private function installNgrok()
    {
        $this->info('📦 Installing ngrok...');
        $output = shell_exec('snap install ngrok 2>&1');
        if ($output && strpos($output, 'error') === false) {
            $this->info('✅ ngrok installed successfully!');
            $this->setupNgrokAuth();
        } else {
            $this->error('❌ Failed to install ngrok. Please install manually: snap install ngrok');
        }
    }

    private function setupNgrokAuth()
    {
        $token = $this->ask('Enter your ngrok auth token (get from https://dashboard.ngrok.com/get-started/your-authtoken)');
        if ($token) {
            $output = shell_exec("ngrok config add-authtoken $token 2>&1");
            if ($output && strpos($output, 'Authtoken saved') !== false) {
                $this->info('✅ ngrok auth token set!');
            } else {
                $this->error('❌ Failed to set auth token. Please set manually: ngrok config add-authtoken YOUR_TOKEN');
            }
        } else {
            $this->warn('⚠️  Skipping auth token setup. Set manually later.');
        }
    }

    private function showSetupInstructions()
    {
        $this->info("\n📋 Next Steps:");
        $this->line('1. Start Laravel: php artisan serve');
        $this->line('2. Start ngrok: ngrok http 8000');
        $this->line('3. Update .env: ./update_ngrok.sh');
        $this->line('4. Set webhook in Fonnte: https://{ngrok-domain}/webhook/whatsapp');
        $this->line('5. Test: Send "menu" to WhatsApp device');

        $this->info("\n🔑 Environment Variables:");
        $this->line('ACCOUNT_TOKEN=your_fonnte_token (set in .env)');
        $this->line('NGROK_WEBHOOK_URL=auto-updated by script');

        $this->info("\n📚 Available Commands:");
        $this->line('php artisan whatsapp:setup - Full automated setup (run once)');
        $this->line('./update_ngrok.sh - Update ngrok URL after restart');
        $this->line('php artisan tinker - Test models');
    }
}
