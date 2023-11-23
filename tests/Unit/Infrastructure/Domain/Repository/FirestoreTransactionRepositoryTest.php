<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Repository;

use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\DocumentReference;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\Query;
use Google\Cloud\Firestore\QuerySnapshot;
use GuzzleHttp\Exception\ConnectException;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Repository\Exception\FirestoreException;
use ProBillerNG\Transaction\Infrastructure\Domain\Repository\FirestoreTransactionRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\FirestoreSerializer;
use Tests\UnitTestCase;
use GuzzleHttp\Psr7\Request;

class FirestoreTransactionRepositoryTest extends UnitTestCase
{
    /**
     * @var FirestoreTransactionRepository
     */
    private $firestoreTransactionRepository;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $firestoreClientMock = $this->createMock(FirestoreClient::class);
        $firestoreClientMock->method('collection')->willReturn(
            $this->createMock(CollectionReference::class)
        );

        $this->firestoreTransactionRepository = new FirestoreTransactionRepository(
            $firestoreClientMock,
            app()->make(FirestoreSerializer::class)
        );
    }

    /**
     * @test
     * @return void
     * @throws \ReflectionException
     */
    public function it_should_return_document_if_found_on_firestore(): void
    {
        $reflection = new \ReflectionClass($this->firestoreTransactionRepository);
        $method     = $reflection->getMethod('getSnapshots');
        $method->setAccessible(true);

        $document = $this->createMock(DocumentReference::class);
        $document->method('snapshot')->willReturn($this->createMock(DocumentSnapshot::class));

        $result = $method->invokeArgs(
            $this->firestoreTransactionRepository,
            [$document, 'snapshot']
        );

        $this->assertInstanceOf(DocumentSnapshot::class, $result);
    }

    /**
     * @test
     * @return void
     * @throws \ReflectionException
     */
    public function it_should_return_null_if_service_exception_is_thrown_when_trying_to_retrieve_from_firestore(): void
    {
        $this->expectException(FirestoreException::class);

        $reflection = new \ReflectionClass($this->firestoreTransactionRepository);
        $method     = $reflection->getMethod('getSnapshots');
        $method->setAccessible(true);

        $document = $this->createMock(DocumentReference::class);
        $document->method('snapshot')->willThrowException(new ServiceException('exception'));

        $method->invokeArgs(
            $this->firestoreTransactionRepository,
            [$document, 'snapshot']
        );
    }

    /**
     * @test
     * @return void
     * @throws \ReflectionException
     */
    public function it_should_return_null_if_connect_exception_is_thrown_when_trying_to_retrieve_from_firestore(): void
    {
        $this->expectException(FirestoreException::class);

        $reflection = new \ReflectionClass($this->firestoreTransactionRepository);
        $method     = $reflection->getMethod('getSnapshots');
        $method->setAccessible(true);

        $document = $this->createMock(DocumentReference::class);
        $document->method('snapshot')->willThrowException(
            new ConnectException(
                'Connection timed out',
                $this->createMock(Request::class)
            )
        );

        $method->invokeArgs(
            $this->firestoreTransactionRepository,
            [$document, 'snapshot']
        );
    }

    /**
     * @test
     * @return void
     * @throws \ReflectionException
     * @throws \JsonException
     */
    public function it_should_throw_exception_if_transaction_could_not_be_saved_in_firestore(): void
    {
        $this->expectException(FirestoreException::class);

        $reflection = new \ReflectionClass($this->firestoreTransactionRepository);
        $method     = $reflection->getMethod('saveToFirestore');
        $method->setAccessible(true);

        $document = $this->createMock(DocumentReference::class);
        $document->method('set')->willThrowException(
            new ConnectException(
                'Connection timed out',
                $this->createMock(Request::class)
            )
        );

        $method->invokeArgs(
            $this->firestoreTransactionRepository,
            [$this->createMock(Transaction::class)->toArray(), $document]
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_a_transaction_with_correct_biller_interactions(): void
    {
        $transaction        = $this->createPendingTransactionWithRebillForNewCreditCard()->toArray();
        $billerInteractions = [
            $this->createBillerInteraction()->toArray(),
            $this->createBillerInteraction(['type' => BillerInteraction::TYPE_RESPONSE])->toArray()
        ];

        $transaction['status']              = Approved::NAME;
        $transaction['billerInteraction'][] = $billerInteractions[0];

        $transactionDocumentSnapshotMock = $this->createMock(DocumentSnapshot::class);
        $transactionDocumentSnapshotMock->method('data')->willReturn($transaction);

        $transactionDocumentReferenceMock = $this->createMock(DocumentReference::class);
        $transactionDocumentReferenceMock->method('snapshot')->willReturn($transactionDocumentSnapshotMock);

        $transactionsCollectionMock = $this->createMock(CollectionReference::class);
        $transactionsCollectionMock->method('document')->willReturn($transactionDocumentReferenceMock);

        $transactionsBillerInteractionsDocumentSnapshotMock = $this->createMock(DocumentSnapshot::class);
        $transactionsBillerInteractionsDocumentSnapshotMock->method('data')->will(
            $this->onConsecutiveCalls(
                [
                    'transactionId'       => $transaction['transactionId'],
                    'billerInteractionId' => $this->faker->uuid
                ],
                [
                    'transactionId'       => $transaction['transactionId'],
                    'billerInteractionId' => $this->faker->uuid
                ]
            )
        );

        $transactionBillerInteractionsQueryMock = $this->createMock(Query::class);
        $transactionBillerInteractionsQueryMock->method('documents')->willReturn(
            new QuerySnapshot(
                $transactionBillerInteractionsQueryMock,
                [
                    $transactionsBillerInteractionsDocumentSnapshotMock,
                    $transactionsBillerInteractionsDocumentSnapshotMock
                ]
            )
        );

        $transactionsBillerInteractionsCollectionMock = $this->createMock(CollectionReference::class);
        $transactionsBillerInteractionsCollectionMock->method('where')
            ->willReturn($transactionBillerInteractionsQueryMock);

        $billerInteractionsSnapshotMock = $this->createMock(DocumentSnapshot::class);
        $billerInteractionsSnapshotMock->method('data')->will(
            $this->onConsecutiveCalls(
                $billerInteractions[0],
                $billerInteractions[1]
            )
        );

        $billerInteractionsDocumentReferenceMock = $this->createMock(DocumentReference::class);
        $billerInteractionsDocumentReferenceMock->method('snapshot')->willReturn($billerInteractionsSnapshotMock);

        $billerInteractionsCollectionMock = $this->createMock(CollectionReference::class);
        $billerInteractionsCollectionMock->method('document')->willReturn($billerInteractionsDocumentReferenceMock);

        $firestoreClientMock = $this->createMock(FirestoreClient::class);
        $firestoreClientMock->method('collection')->will(
            $this->onConsecutiveCalls(
                $transactionsCollectionMock,
                $transactionsBillerInteractionsCollectionMock,
                $billerInteractionsCollectionMock
            )
        );

        $firestoreTransactionRepository = new FirestoreTransactionRepository(
            $firestoreClientMock,
            app()->make(FirestoreSerializer::class)
        );

        $transaction = $firestoreTransactionRepository->findById($this->faker->uuid);

        $this->assertEquals($billerInteractions, $transaction->toArray()['billerInteractions']);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_a_transaction_with_original_biller_interactions_if_none_are_found_on_fallback_collections(): void
    {
        $transactionArray   = $this->createPendingTransactionWithRebillForNewCreditCard()->toArray();
        $billerInteractions = [
            $this->createBillerInteraction()->toArray(),
            $this->createBillerInteraction(['type' => BillerInteraction::TYPE_RESPONSE])->toArray()
        ];

        $transactionArray['status']              = Approved::NAME;
        $transactionArray['billerInteraction'][] = $billerInteractions[0];

        $transactionDocumentSnapshotMock = $this->createMock(DocumentSnapshot::class);
        $transactionDocumentSnapshotMock->method('data')->willReturn($transactionArray);

        $transactionDocumentReferenceMock = $this->createMock(DocumentReference::class);
        $transactionDocumentReferenceMock->method('snapshot')->willReturn($transactionDocumentSnapshotMock);

        $transactionsCollectionMock = $this->createMock(CollectionReference::class);
        $transactionsCollectionMock->method('document')->willReturn($transactionDocumentReferenceMock);

        $transactionBillerInteractionsQueryMock = $this->createMock(Query::class);
        $transactionBillerInteractionsQueryMock->method('documents')->willReturn(
            new QuerySnapshot(
                $transactionBillerInteractionsQueryMock,
                []
            )
        );

        $transactionsBillerInteractionsCollectionMock = $this->createMock(CollectionReference::class);
        $transactionsBillerInteractionsCollectionMock->method('where')
            ->willReturn($transactionBillerInteractionsQueryMock);

        $firestoreClientMock = $this->createMock(FirestoreClient::class);
        $firestoreClientMock->method('collection')->will(
            $this->onConsecutiveCalls(
                $transactionsCollectionMock,
                $transactionsBillerInteractionsCollectionMock,
                $this->createMock(CollectionReference::class)
            )
        );

        $firestoreTransactionRepository = new FirestoreTransactionRepository(
            $firestoreClientMock,
            app()->make(FirestoreSerializer::class)
        );

        $transaction = $firestoreTransactionRepository->findById($this->faker->uuid);

        $this->assertEquals($transactionArray['billerInteractions'], $transaction->toArray()['billerInteractions']);
    }
}