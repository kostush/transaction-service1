<?php

namespace ProBillerNG\Transaction\UI\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Transaction\Application\BI\TransactionUpdatedTemp;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;

/**
 * Class CreateTransactionUpdatedEvent
 * @package ProBillerNG\Transaction\UI\Console\Commands
 */
class CreateTransactionUpdatedEvent extends Command
{
    /**
     * @var string
     */
    protected $signature = "ng:create-dws-event:transaction_updated {file}";

    /**
     * @var string
     */
    protected $description = 'Creates DWS events based on transaction id provided in a CSV file.';

    /**
     * @var TransactionRepository
     */
    protected $transactionRepository;

    /**
     * @var BILoggerService
     */
    protected $biLoggerService;


    public function __construct(TransactionRepository $transactionRepository, BILoggerService $biLoggerService)
    {
        parent::__construct();
        $this->transactionRepository = $transactionRepository;
        $this->biLoggerService       = $biLoggerService;
    }

    public function handle(): void
    {
        echo("START:" . Carbon::now()->format('Y-m-d H:i:s') . "\n");
        $filePath = storage_path($this->argument('file'));

        if (!is_file($filePath)) {
            throw new \Exception('Cant find file');
        }

        $csvContent = array_map('str_getcsv', file($filePath, FILE_SKIP_EMPTY_LINES));
        unset($csvContent[0]); // remove headers line

        // index[0]: transactionId
        // index[1]: sessionI
        $csvChunks = array_chunk($csvContent, 100);

        foreach ($csvChunks as $chunk) {
            // get records from stored_events
            $this->createEventsFromStoredEvents($chunk);
        }
        echo("END:" . Carbon::now()->format('Y-m-d H:i:s'). "\n");
    }

    /**
     * @param array $chunk
     */
    private function createEventsFromStoredEvents(array $chunk)
    {
        try {
            $mappingTransactionSessions = [];

            // index[0]: transactionId
            // index[1]: sessionId
            foreach ($chunk as $row) {
                $mappingTransactionSessions[$row[0]] = $row[1];
            }

            $storedEvents = DB::table('stored_events')
                ->whereIn('aggregate_id', array_keys($mappingTransactionSessions))
                ->where('type_name', '<>', 'ProBillerNG\\Transaction\\Domain\\Model\\Event\\TransactionCreatedEvent')
                ->whereDate('occurred_on', '>', '2020-06-01 00:00:00')
                ->get();

            foreach ($storedEvents as $event) {
                try {
                    $event->sessionId = $mappingTransactionSessions[$event->aggregate_id];
                    echo "Record: $event->aggregate_id,$event->sessionId\n";
                    $this->writeBIEvent_Transaction_Updated($event);
                }
                catch (\Throwable $throwable) {
                    echo "Exception: $event->aggregate_id, $event->sessionId - " . $throwable->getMessage() . "\n";
                }
            }
        } catch (\Throwable $throwable) {
            echo $throwable->getMessage() . "\n";
        }
    }

    /**
     * @param \stdClass $event Event Data
     *
     * @throws \ProBillerNG\Logger\Exception
     */
    private function writeBIEvent_Transaction_Updated($event)
    {
        $this->biLoggerService->write(
            new TransactionUpdatedTemp(
                get_object_vars($event)
            )
        );
    }
}