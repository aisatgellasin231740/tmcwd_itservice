<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmail extends Command
{
    protected $signature   = 'mail:test {email? : Recipient email address}';
    protected $description = 'Send a test email to verify SMTP configuration is working';

    public function handle(): int
    {
        $recipient = $this->argument('email')
            ?? $this->ask('Send test email to', 'admin@tmcwd.gov.ph');

        $this->info("Sending test email to: {$recipient}");
        $this->info('Mailer: ' . config('mail.default'));
        $this->info('Host:   ' . config('mail.mailers.smtp.host'));

        try {
            Mail::raw(
                "This is a test email from the TMCWD IT Request Service System.\n\n"
                . "If you received this, your SMTP configuration is working correctly.\n\n"
                . "Sent at: " . now()->format('Y-m-d H:i:s') . "\n"
                . "Environment: " . config('app.env'),
                function ($message) use ($recipient) {
                    $message->to($recipient)
                            ->subject('✅ TMCWD IT Service — SMTP Test Email');
                }
            );

            $this->newLine();
            $this->components->success("Test email sent successfully to [{$recipient}].");
            $this->line('  Check your Mailtrap inbox (or mail server) to confirm delivery.');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->components->error('Failed to send email: ' . $e->getMessage());
            $this->newLine();
            $this->line('  <fg=yellow>Troubleshooting tips:</>');
            $this->line('  1. Check MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD in .env');
            $this->line('  2. Confirm your Mailtrap inbox credentials are correct');
            $this->line('  3. Make sure port 2525 is not blocked by your firewall');
            $this->line('  4. Run: php artisan config:clear  then try again');

            return self::FAILURE;
        }
    }
}
