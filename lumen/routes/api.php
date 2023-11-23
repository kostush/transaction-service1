<?php

/** @var Router $router */

use Laravel\Lumen\Routing\Router;

$router->group(
    ['prefix' => 'api/v1/'],
    function () use ($router) {

        $router->group(
            ['middleware' => ['GenerateSessionId', 'NGLogger']],
            function () use ($router) {
                $router->get(
                    '/healthCheck',
                    ['uses' => 'TransactionHealthCheckController@retrieve']
                );

                $router->post(
                    'pumapay/qrCode',
                    ['uses' => 'RetrievePumapayQrCodeController@retrieve', 'middleware' => ['XApiKeyAuth']]
                );

                $router->put(
                    'transaction/{transactionId}/pumapay/billerInteraction',
                    ['uses' => 'AddBillerInteractionForJoinOnPumapayController@add', 'middleware' => ['XApiKeyAuth']]
                );

                $router->post(
                    'pumapay/rebill/billerInteraction',
                    ['uses' => 'AddBillerInteractionForRebillOnPumapayController@add', 'middleware' => ['XApiKeyAuth']]
                );

                $router->post(
                    'pumapay/cancelRebill',
                    ['uses' => 'PumapayCancelRebillController@cancel', 'middleware' => ['XApiKeyAuth']]
                );

                $router->put(
                    '/transaction/{transactionId}/rocketgate/completeThreeD/session/{sessionId}',
                    ['uses' => 'CompleteThreeDController@completeTransaction']
                );

                $router->put(
                    '/transaction/{transactionId}/epoch/billerInteraction',
                    ['uses' => 'AddBillerInteractionForJoinPostbackOnEpochController@add']
                );

                $router->put(
                    '/transaction/{transactionId}/qysso/billerInteraction',
                    ['uses' => 'AddBillerInteractionForJoinPostbackOnQyssoController@add']
                );

                $router->post(
                    'qysso/rebill/billerInteraction',
                    ['uses' => 'AddBillerInteractionForRebillOnQyssoController@add']
                );

                $router->put(
                    '/transaction/{transactionId}/abort/',
                    ['uses' => 'AbortTransactionController@abort']
                );
            }
        );

        $router->group(
            ['middleware' => ['ValidateSessionId','GenerateCorrelationId', 'NGLogger']],
            function () use ($router) {

                /**RocketGate route definition Start*/
                $router->post(
                    '/sale/rocketgate/session/{sessionId}',
                    ['uses' => 'RocketgateNewCreditCardSaleController@create']
                );

                $router->post(
                    '/sale/newCard/rocketgate/',
                    ['uses' => 'RocketgateNewCreditCardSaleController@create']
                );

                $router->post(
                    '/sale/otherPaymentType/rocketgate/session/{sessionId}',
                    ['uses' => 'RocketgateOtherPaymentTypeSaleController@create']
                );

                $router->post(
                    '/sale/rocketgate/session/{sessionId}',
                    ['uses' => 'RocketgateNewCreditCardSaleController@create']
                );

                $router->post(
                    '/sale/newCard/rocketgate/session/{sessionId}',
                    ['uses' => 'RocketgateNewCreditCardSaleController@create']
                );

                $router->post(
                    '/sale/existingCard/rocketgate',
                    ['uses' => 'RocketGateExistingCreditCardSaleController@create']
                );

                $router->post(
                    '/sale/existingCard/rocketgate/session/{sessionId}',
                    ['uses' => 'RocketGateExistingCreditCardSaleController@create']
                );

                $router->get(
                    '/transaction/{transactionId}',
                    ['uses' => 'RetrieveTransactionController@retrieve']
                );

                $router->get(
                    '/transaction/{transactionId}/session/{sessionId}',
                    ['uses' => 'RetrieveTransactionController@retrieve']
                );

                $router->post(
                    '/cancelRebill/rocketgate/session/{sessionId}',
                    ['uses' => 'RocketgateCancelRebillController@create']
                );

                $router->post(
                    '/updateRebill/rocketgate/session/{sessionId}',
                    ['uses' => 'RocketgateUpdateRebillController@create']
                );

                $router->put(
                    '/threeds-lookup/{billerName}/session/{sessionId}',
                    ['uses' => 'LookupThreeDsTwoController@create']
                );

                /**RocketGate route definition End*/

                /**Netbilling route definition Start*/
                $router->post(
                    '/sale/newCard/netbilling/session/{sessionId}',
                    ['uses' => 'NetbillingNewCreditCardSaleController@create']
                );

                $router->post(
                    '/sale/existingCard/netbilling/session/{sessionId}',
                    ['uses' => 'NetbillingExistingCreditCardSaleController@create']
                );

                $router->post(
                    '/cancelRebill/netbilling/session/{sessionId}',
                    ['uses' => 'NetbillingCancelRebillController@create']
                );

                $router->post(
                    '/updateRebill/netbilling/session/{sessionId}',
                    ['uses' => 'NetbillingUpdateRebillController@create']
                );

                /**Netbilling route definition End*/

                $router->post(
                    '/sale/biller/{billerName}/session/{sessionId}',
                    ['uses' => 'LegacyNewSaleController@create']
                );

                $router->put(
                    '/legacy/billerInteraction/session/{sessionId}',
                    ['uses' => 'AddBillerInteractionForJoinPostbackOnLegacyController@create']
                );
            }
        );
    }
);

$router->group(
    ['prefix' => 'api/v1/'],
    function () use ($router) {
        $router->group(
            ['middleware' => ['GenerateCorrelationId', 'NGLogger']],
            function () use ($router) {
                $router->post(
                    '/updateRebill/netbilling',
                    ['uses' => 'NetbillingUpdateRebillController@create']
                );

                $router->post(
                    '/cancelRebill/netbilling',
                    ['uses' => 'NetbillingCancelRebillController@create']
                );

                $router->post(
                    '/sale/epoch',
                    ['uses' => 'EpochNewSaleController@create']
                );

                $router->post(
                    '/sale/qysso',
                    ['uses' => 'QyssoNewSaleController@create']
                );
            }
        );
    }
);

$router->group(
    ['prefix' => 'api/v2/'],
    static function () use ($router) {
        $router->group(
            ['middleware' => ['ValidateSessionId','GenerateCorrelationId', 'NGLogger']],
            static function () use ($router) {
                $router->put(
                    '/transaction/{transactionId}/rocketgate/completeThreeD/session/{sessionId}',
                    ['uses' => 'SimplifiedCompleteThreeDController@completeTransaction']
                );
            }
        );
    }
);
