<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Repository;

use Exception;
use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\DocumentReference;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\Query;
use Google\Cloud\Firestore\QuerySnapshot;
use GuzzleHttp\Exception\RequestException;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Repository\Exception\FirestoreException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\FirestoreSerializer;
use Throwable;
use const Grpc\STATUS_ABORTED;
use const Grpc\STATUS_CANCELLED;
use const Grpc\STATUS_DATA_LOSS;
use const Grpc\STATUS_DEADLINE_EXCEEDED;
use const Grpc\STATUS_FAILED_PRECONDITION;
use const Grpc\STATUS_INTERNAL;
use const Grpc\STATUS_OUT_OF_RANGE;
use const Grpc\STATUS_RESOURCE_EXHAUSTED;
use const Grpc\STATUS_UNAVAILABLE;
use const Grpc\STATUS_UNKNOWN;

class FirestoreTransactionRepository implements TransactionRepository
{
    public const DOCUMENT_RETRIEVE_METHOD  = 'snapshot';
    public const QUERY_RETRIEVE_METHOD     = 'documents';
    public const RETRY_FOR_EXCEPTION_CODES = [
        STATUS_CANCELLED,
        STATUS_UNKNOWN,
        STATUS_DEADLINE_EXCEEDED,
        STATUS_RESOURCE_EXHAUSTED,
        STATUS_FAILED_PRECONDITION,
        STATUS_ABORTED,
        STATUS_OUT_OF_RANGE,
        STATUS_INTERNAL,
        STATUS_UNAVAILABLE,
        STATUS_DATA_LOSS
    ];

    /**
     * @var CollectionReference
     */
    private $transactionsCollection;

    /**
     * @var CollectionReference
     */
    private $transactionsBillerInteractionsCollection;

    /**
     * @var CollectionReference
     */
    private $billerInteractionsCollection;

    /**
     * @var FirestoreSerializer
     */
    private $serializer;

    /**
     * FirestoreTransactionRepository constructor.
     * @param FirestoreClient     $client       Client id.
     * @param FirestoreSerializer $serializer   Serializer
     */
    public function __construct(
        FirestoreClient $client,
        FirestoreSerializer $serializer
    ) {
        $this->transactionsCollection                   = $client->collection(
            env('FIRESTORE_COLLECTION', 'transactions')
        );
        $this->transactionsBillerInteractionsCollection = $client->collection(
            env('TRANSACTIONS_BILLER_INTERACTIONS_COLLECTION', 'migration_transactions_biller_interactions')
        );
        $this->billerInteractionsCollection             = $client->collection(
            env('BILLER_INTERACTION_COLLECTION', 'migration_biller_interaction')
        );

        $this->serializer = $serializer;
    }

    /**
     * @param Transaction $transaction Transaction
     * @return Transaction|void
     * @throws FirestoreException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function add(Transaction $transaction): Transaction
    {
        // write on firestore
        Log::info('Trying to write on firestore');

        $transactionData = $transaction->toArray();

        // this is done because we have no control over the structure of the billerInteractions
        // and firestore cannot store nested arrays
        if (!empty($transactionData['billerInteractions'])) {
            foreach ($transactionData['billerInteractions'] as $key => $billerInteraction) {
                $transactionData['billerInteractions'][$key]['createdAt'] = \DateTimeImmutable::createFromFormat(
                    'Y-m-d H:i:s.u',
                    $billerInteraction['createdAt']->get()->format('Y-m-d H:i:s.u')
                );
            }

            $transactionData['billerInteractions'] = json_encode($transactionData['billerInteractions'], JSON_THROW_ON_ERROR);
        }

        $document = $this->transactionsCollection->document($transaction->getEntityId());

        $this->saveToFirestore($transactionData, $document);

        return $transaction;
    }

    /**
     * @param string $transactionId Transaction id
     * @return Transaction|null
     * @throws Exception
     * @throws Throwable
     */
    public function findById(string $transactionId): ?Transaction
    {
        if (empty($transactionId)) {
            return null;
        }

        try {
            Log::info('Trying to retrieve the transaction having the id: ' . $transactionId . ' from firestore');

            $document         = $this->transactionsCollection->document($transactionId);
            $documentSnapshot = $this->getSnapshots($document, self::DOCUMENT_RETRIEVE_METHOD);

            if (empty($documentSnapshot->data())) {
                return null;
            }

            $previousTransactionId = $documentSnapshot->data()['previousTransactionId'] ?? '';
            $previousTransaction   = $this->findById($previousTransactionId);

            $transactionData = $this->checkBillerInteractions($transactionId, $documentSnapshot->data(), $document);

            return $this->serializer->hydrate($transactionData, $previousTransaction);
        } catch (Throwable $e) {
            Log::logException($e);
            throw $e;
        }
    }

    /**
     * @param Transaction $transaction Transaction
     * @return Transaction
     * @throws FirestoreException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function update(Transaction $transaction): Transaction
    {
        return $this->add($transaction);
    }

    /**
     * @param array      $criteria Criteria
     * @param array|null $orderBy  Order by
     * @param null       $limit    Limit
     * @param null       $offset   Offset
     * @return array
     */
    public function findAllBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        return [];
    }

    /**
     * @param DocumentReference|Query $document   Document reference or Query
     * @param string                  $methodName Name of the method which gets the snapshots
     * @return DocumentSnapshot|QuerySnapshot
     * @throws \ProBillerNG\Logger\Exception
     * @throws FirestoreException
     */
    private function getSnapshots($document, string $methodName)
    {
        for ($attempt = 1; $attempt <= env('NO_OF_FIRESTORE_READ_WRITE_ATTEMPTS'); $attempt++) {
            try {
                $documentSnapshots = $document->$methodName();

                Log::info('FirestoreConnection Document snapshot returned on attempt number: ' . $attempt);

                return $documentSnapshots;
            } catch (RequestException $exception) {
                Log::logException($exception);

                if ($attempt == env('NO_OF_FIRESTORE_READ_WRITE_ATTEMPTS')) {
                    Log::warning('FirestoreConnection Number of retries exhausted: ' . $attempt);
                }
            } catch (ServiceException $exception) {
                Log::logException($exception);

                if ($attempt == env('NO_OF_FIRESTORE_READ_WRITE_ATTEMPTS')) {
                    Log::warning('FirestoreConnection Number of retries exhausted: ' . $attempt);
                }

                if ($attempt == env('NO_OF_FIRESTORE_READ_WRITE_ATTEMPTS')
                    || !in_array($exception->getCode(), self::RETRY_FOR_EXCEPTION_CODES)
                ) {
                    break;
                }
            }
        }

        throw new FirestoreException($exception ?? null);
    }

    /**
     * @param array             $transaction Transaction
     * @param DocumentReference $document    Document
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws FirestoreException
     */
    private function saveToFirestore(array $transaction, DocumentReference $document): void
    {
        for ($attempt = 1; $attempt <= env('NO_OF_FIRESTORE_READ_WRITE_ATTEMPTS'); $attempt++) {
            try {
                $document->set($transaction);

                Log::info('FirestoreConnection Document saved on attempt number: ' . $attempt);

                return;
            } catch (RequestException $exception) {
                Log::logException($exception);

                if ($attempt == env('NO_OF_FIRESTORE_READ_WRITE_ATTEMPTS')) {
                    Log::warning('FirestoreConnection Number of retries exhausted: ' . $attempt);

                    throw new FirestoreException($exception);
                }
            } catch (ServiceException $exception) {
                Log::logException($exception);

                if ($attempt == env('NO_OF_FIRESTORE_READ_WRITE_ATTEMPTS')) {
                    Log::warning('FirestoreConnection Number of retries exhausted: ' . $attempt);
                }

                if ($attempt == env('NO_OF_FIRESTORE_READ_WRITE_ATTEMPTS')
                    || !in_array($exception->getCode(), self::RETRY_FOR_EXCEPTION_CODES)
                ) {
                    throw new FirestoreException($exception);
                }
            }
        }
    }

    /**
     * @param string            $transactionId Transaction id.
     * @param array             $data          Transaction data.
     * @param DocumentReference $document      Document reference.
     * @return array
     * @throws \ProBillerNG\Logger\Exception
     * @throws FirestoreException
     */
    private function checkBillerInteractions(string $transactionId, array $data, DocumentReference $document): array
    {
        if ($this->areRequestInteractionsEqualToReposeInteractions($data)) {
            return $data;
        }

        Log::info('Biller interactions are not even, using fallback collections');

        $billerInteractions = $this->populateBillerInteractionFromFallbackCollections($transactionId);

        if (empty($billerInteractions)) {
            Log::info('No biller interaction where found in the fallback collections, no change will be done to the transaction');

            return $data;
        }

        $data['billerInteractions'] = $billerInteractions;

        Log::info('Updating transaction with correct biller interactions');

        $this->saveToFirestore($data, $document);

        return $data;
    }

    /**
     * @param array $data Data
     * @return bool
     */
    private function areRequestInteractionsEqualToReposeInteractions(array $data): bool
    {
        if (empty($data['billerInteractions'])) {
            return false;
        }

        $requestInteractions  = 0;
        $responseInteractions = 0;

        $billerInteractions = $data['billerInteractions'];

        if (!is_array($billerInteractions)) {
            $billerInteractions = json_decode($billerInteractions, true, 512, JSON_THROW_ON_ERROR);
        }

        foreach ($billerInteractions as $billerInteraction) {
            switch ($billerInteraction['type']) {
                case BillerInteraction::TYPE_REQUEST:
                    $requestInteractions++;
                    break;
                case BillerInteraction::TYPE_RESPONSE:
                    $responseInteractions++;
                    break;
            }
        }

        return $requestInteractions === $responseInteractions;
    }

    /**
     * @param string $transactionId Transaction id
     * @return array
     * @throws \ProBillerNG\Logger\Exception
     */
    private function populateBillerInteractionFromFallbackCollections(string $transactionId): array
    {
        $billerInteractions = [];

        Log::info('FirestoreConnection Retrieving all biller interactions from pivot table for transaction: ' . $transactionId);

        $transactionsBillerInteractions = $this->transactionsBillerInteractionsCollection
            ->where('transactionId', '=', $transactionId);

        $transactionsBillerInteractions = $this->getSnapshots(
            $transactionsBillerInteractions,
            self::QUERY_RETRIEVE_METHOD
        );

        /** @var DocumentSnapshot $billerInteraction */
        foreach ($transactionsBillerInteractions as $billerInteraction) {
            $billerInteractionData = $billerInteraction->data();

            if (isset($billerInteractionData['billerInteractionId'])) {
                Log::info('FirestoreConnection Retrieving biller interaction from fallback: ' . $billerInteractionData['billerInteractionId']);

                $billerInteraction = $this->billerInteractionsCollection->document($billerInteractionData['billerInteractionId']);

                $snapshot = $this->getSnapshots($billerInteraction, self::DOCUMENT_RETRIEVE_METHOD);

                if (null !== $snapshot) {
                    $billerInteractions[] = $snapshot->data();
                }
            }
        }

        return $billerInteractions;
    }
}
