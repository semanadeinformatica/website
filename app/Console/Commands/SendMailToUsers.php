<?php

namespace App\Console\Commands;

use App\Mail\UserNotification;
use App\Models\Admin;
use App\Models\Company;
use App\Models\Participant;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\text;

class SendMailToUsers extends Command implements PromptsForMissingInput
{
    /**
     * The default receiver types.
     */
    const DEFAULT_RECEIVER_TYPES = [Participant::class];

    /**
     * Default configuration of whether emails should be sent to admins first or not.
     */
    const DEFAULT_TEST_FIRST = false;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-mail-to-users {subject : Subject of the mail to send} {text : Text of the mail to send} {--types=*} {--test}';

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing()
    {
        return [
            'subject' => fn () => text('Which subject should you send in the email?'),
            'text' => fn () => text('Which text should you send in the email?'),
        ];
    }

    /**
     * Perform actions after the user was prompted for missing arguments.
     *
     * @return void
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output)
    {
        $input->setOption('types', multiselect(
            'Which user types should receive the email?',
            options: [
                Participant::class => 'Participants',
                Company::class => 'Companies',
                Speaker::class => 'Speakers',
            ],
            default: [
                Participant::class,
            ],
            required: true,
        ));
        $input->setOption('test', confirm(
            'Should the email be sent to admins before being sent to other users?',
            default: false,
        ));
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
        $subject = $this->argument('subject');

        $types = $this->option('types');
        if ($types === []) {
            $types = self::DEFAULT_RECEIVER_TYPES;
        }

        $test = $this->option('test') ?? self::DEFAULT_TEST_FIRST;

        if ($test) {
            $admins = User::where('usertype_type', Admin::class)->get();

            $this->info('Sending mail to all admins before sending to participants!');

            Mail::to($admins)->queue(new UserNotification($subject, $text));

            if (! confirm('Send email to all participants?', default: false, yes: 'Yes', no: 'No')) {
                return;
            }
        }

        /** @var Collection */
        $users = User::whereIn('usertype_type', $types)->get();

        $this->info("Sending mail to {$users->count()} users!");

        $users->each(fn ($user, $i) => Mail::to($user)->later($this->delayFactor($i), new UserNotification($subject, $text)));

        $this->info('Sent mail to users!');
    }

    /**
     * Calculate the delay factor for the given index.
     *
     * YAY rate limits
     */
    private function delayFactor(int $delay)
    {
        return $delay * 2;
    }
}
