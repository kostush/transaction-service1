<?php

namespace App\Console;

use App\Logger;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use Laravel\Lumen\Http\Request;
use ProBillerNG\Transaction\UI\Console\Commands\CreateTransactionUpdatedEvent;
use ProBillerNG\Transaction\UI\Console\Commands\MigrateCommand;
use ProBillerNG\Transaction\UI\Console\Commands\SeedCommand;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Kernel extends ConsoleKernel
{
    use Logger;

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        SeedCommand::class,
        CreateTransactionUpdatedEvent::class,
        MigrateCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule Schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface  $input  InputInterface
     * @param OutputInterface $output OutputInterface
     *
     * @return int
     * @throws \Exception
     */
    public function handle($input, $output = null)
    {
        $request = app(Request::class);
        $request->attributes->set('sessionId', Uuid::uuid4());

        $this->initLogger('APP_WORKER_LOG_FILE', $request);

        return parent::handle($input, $output);
    }
}
