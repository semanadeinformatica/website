<?php

namespace App\Console\Commands;

use App\Mail\ParticipantNotification;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMailToParticipants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-mail-to-participants {text : Text to send}';

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing()
    {
        return [
            'text' => 'Which text should you send in the email?',
        ];
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email with the given message to each participant';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $text = $this->argument('text');

        $participants = User::where('usertype_type', Participant::class)->get()->pluck(['email']);
        $this->info('Sending mail to all participants!');

        // Mail::send(new ParticipantNotification($text));

        $this->info('Sent mail to every participant');
    }
}
