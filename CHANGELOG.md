# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.40.9] - 2022-01-06
### Fixed
- [BG-56524](https://jira.mgcorp.co/browse/BG-56524) - On sec rev, if simplified flow is enabled, do not send the MIT flag on RG transactions.
- [BG-56461](https://jira.mgcorp.co/browse/BG-56461) - Retrieve correct invoice id and customer id from biller interactions for RG.
- [BG-55921](https://jira.mgcorp.co/browse/BG-55921) - Throw exception for missing NB biller member id.

## [1.40.8] - 2021-12-20
### Fixed
- [BG-55949](https://jira.mgcorp.co/browse/BG-55949) - Old 3DS flow is triggered on the last4 flow, if Rocketgate responds with code 228.

## [1.40.7] - 2021-12-14
### Updated
- [BG-56274](https://jira.mgcorp.co/browse/BG-56274) - Tests using sensitive data from secret manager.

## [1.40.6] - 2021-12-09
### Updated
- [BG-55871](https://jira.mgcorp.co/browse/BG-55871) - Retrieve endpoint to return merchanSiteId for Rocketgate as empty string even if it does not exist in BillerSettings.
- [BG-56239](https://jira.mgcorp.co/browse/BG-56239) - Ability to read from billerInteractionsHistory when billerInteractions is not set in Firestore.

## [1.40.5] - 2021-12-07
### Fixed
- [BG-55687](https://jira.mgcorp.co/browse/BG-55687) - Two ‘sale’ biller transactions when we retry with 3DS for 228 RG code.
- [BG-55851](https://jira.mgcorp.co/browse/BG-55851) - Add handling for retrieval of AUTH transactions.

## [1.40.4] - 2021-11-22
### Updated
- [BG-55857](https://jira.mgcorp.co/browse/BG-55857) - AAD secret key.
### Added
- [BG-55860](https://jira.mgcorp.co/browse/BG-55860) - Fallback to retrieve biller account config for RG account 700.

## [1.40.3] - 2021-11-17
### Added
- [BG-55188](https://jira.mgcorp.co/browse/BG-55188) - Error handling and fix for when we don`t have merchant id/password, or we have corrupted expiration year on transaction.

## [1.40.2] - 2021-11-16
### Fixed
- [BG-55587](https://jira.mgcorp.co/browse/BG-55587) - Transaction should be reattempted with 3DS, when code 228 is returned from RG on the last4 flow.

## [1.40.1] - 2021-11-11
### Updated
- [BG-55621](https://jira.mgcorp.co/browse/BG-55621) - Rocketgate service to handle redundant cancel operation as success. 

## [1.40.0] - 2021-11-11
### Added
- [BG-54354](https://jira.mgcorp.co/browse/BG-54354) - 3DS simplified flow.

## [1.39.3] - 2021-11-08
### Fixed
- [BG-55180](https://jira.mgcorp.co/browse/BG-55180) - Mir for creditcard validation.

## [1.39.2] - 2021-11-03
### Fixed  
- [BG-55211](https://jira.mgcorp.co/browse/BG-55211) - Lookup returning 400 when previous transaction is not pending.

## [1.39.1] - 2021-10-28
### Fixed
- [BG-54604](https://jira.mgcorp.co/browse/BG-54604) - Biller unified group error logging.

## [1.39.0] - 2021-10-28
### Changed
- [BG-54604](https://jira.mgcorp.co/browse/BG-54604) - Update biller unified group error implementation.

## [1.38.7] - 2021-10-27
### Fixed  
- [BG-50369](https://jira.mgcorp.co/browse/BG-50369) - Abort of pending 3ds transactions.

## [1.38.6] - 2021-10-21
### Updated
- [BG-54554](https://jira.mgcorp.co/browse/BG-54554) - Rocketgate service to add flag resume rebill to update subscription status.

## [1.38.5] - 2021-10-14
### Changed
- [BG-54615](https://jira.mgcorp.co/browse/BG-54615) - On the retrieve endpoint, CardUpload biller transactions will determined from the request not the response

## [1.38.4] - 2021-10-11
### Removed
- [BG-54679](https://jira.mgcorp.co/browse/BG-54679) - AMEX card restrictions.

## [1.38.3] - 2021-10-07
### Fixed
- [BG-54609](https://jira.mgcorp.co/browse/BG-54609) - Undefined billerMemberId for charge only transactions.

## [1.38.2] - 2021-10-07
### Fixed
- [BG-54609](https://jira.mgcorp.co/browse/BG-54609) - Use subsequentOperationFields as fallback if BillerInteractions are incomplete

## [1.38.1] - 2021-10-04
### Fixed
- [BG-54304](https://jira.mgcorp.co/browse/BG-54304) - Set rebill expiration date based on initial days for rebill update

## [1.38.0] - 2021-09-28
### Added
- [BG-54303](https://jira.mgcorp.co/browse/BG-54303) - IsMerchantInitiated parameter for Rocketgate biller fields request for existing card sale, update and start rebill.

## [1.37.2] - 2021-09-21
### Fixed
- [BG-54272](https://jira.mgcorp.co/browse/BG-54272) - Pumapay transactions rebill postback handling.

## [1.37.1] - 2021-09-21
### Updated
- [BG-52841](https://jira.mgcorp.co/browse/BG-52841) - Rocketgate service version to add customer details to card upload request.

## [1.37.0] - 2021-09-14
### Added
- [BG-53816](https://jira.mgcorp.co/browse/BG-53816) - Support to Pumapay transactions over Firestore.

## [1.36.5] - 2021-08-26
### Added
- [BG-53706](https://jira.mgcorp.co/browse/BG-53706) - Retrieve transaction failing due to undefined reasonCode - user sync edge case.

## [1.36.4] - 2021-08-24
### Added
- [BG-53700](https://jira.mgcorp.co/browse/BG-53700) - Backward compatibility on Firestore to handle TSv1 and TSv2 document structure.

## [1.36.3] - 2021-08-23
### Fixed
- [BG-53635](https://jira.mgcorp.co/browse/BG-53635) - Biller interactions serialized as JSON while saving to Firestore.

## [1.36.2] - 2021-08-23
### Updated
- [BG-53337](https://jira.mgcorp.co/browse/BG-53337) - Support referringMerchantId to be sent to Rocktgate biller during createTransactionWithExistingCard process.

## [1.36.1] - 2021-07-19
### Updated
- [BG-52462](https://jira.mgcorp.co/browse/BG-52462) - Path for BI events to write them in the same location as the other logs, to avoid loosing data during the releases.

## [1.36.0] - 2021-07-08
### Added
- [BG-51207](https://jira.mgcorp.co/browse/BG-51207) - Add errorClassification on update rebill endpoint response.

## [1.35.0] - 2021-06-29
- [BG-51606](https://jira.mgcorp.co/browse/BG-51606) - Remove Mysql usage.

## [1.34.8] - 2021-06-23
### Fixed
- [BG-52294](https://jira.mgcorp.co/browse/BG-52294) - Retrieval of transaction with missing biller interactions and added more Firestore retries scenarios based on GRPC error codes.

## [1.34.7] - 2021-06-08
### Fixed
- [BG-52059](https://jira.mgcorp.co/browse/BG-52059) - Retrieve rebill update aborted transactions from Firestore.

## [1.34.6] - 2021-06-07
### Fixed
- [BG-51941](https://jira.mgcorp.co/browse/BG-51941) - Retrieval of transactions with expired card data, increase connection attempts & improve logs - Firestore.
### Added
- [BG-51883](https://jira.mgcorp.co/browse/BG-51883) - Script to populate error classifications from MySQL old tables to the new Firestore collection.

## [1.34.5] - 2021-06-01
### Added
- [BG-51252](https://jira.mgcorp.co/browse/BG-51252) - Card upload support on 3ds lookup with retry of bypassing 3ds and get response NFS.

## [1.34.4] - 2021-05-27
### Added
- [BG-51555](https://jira.mgcorp.co/browse/BG-51555) - Storage of the CVV on 3DS authentication step so that it can be used on the complete purchase.

## [1.34.3] - 2021-05-27
### Updated
- [BG-51788](https://jira.mgcorp.co/browse/BG-51788) - Retry firestore connection up to 5 times in case of time out or unavailable/aborted grpc exceptions.

## [1.34.2] - 2021-05-26
### Fixed
- [BG-51787](https://jira.mgcorp.co/browse/BG-51787) - Fixed camel case rocketgate card hash issue for Firestore retrieval.

## [1.34.1] - 2021-05-25
### Fixed
- [BG-51600](https://jira.mgcorp.co/browse/BG-51600) - Env variable for the gcp file.

## [1.34.0] - 2021-05-25
### Added
- [BG-49327](https://jira.mgcorp.co/browse/BG-49327) - Switch MySql repositories with Firestore ones.

## [1.33.2] - 2021-05-17
### Updated
- [BG-49928](https://jira.mgcorp.co/browse/BG-49928) - Operations for Rocketgate to stop recurring instead of cancelling the subscription.

## [1.33.1] - 2021-04-13
### Added
- [BG-49942](https://jira.mgcorp.co/browse/BG-49942) - SiteId in the transaction created for DWS.

## [1.33.0] - 2021-04-05
### Added
- [BG-48708](https://jira.mgcorp.co/browse/BG-48708) - Changes needed in order to sync TS v1 with v2.

## [1.32.9] - 2021-03-31
### Added
- [BG-50144](https://jira.mgcorp.co/browse/BG-50144) - CVV storing and retrieving from Redis.

## [1.32.8] - 2021-03-31
### Added
- [BG-50170](https://jira.mgcorp.co/browse/BG-50170) - Rocketgate code for 3DS SCA Required.

## [1.32.7] - 2021-03-25
### Updated
- [BG-50435](https://jira.mgcorp.co/browse/BG-50435) - Netbilling service that consider DUP+RE-APPROVED from NB as DECLINED not as APPROVED

## [1.32.6] - 2021-03-22
### Added
- [BG-49771](https://jira.mgcorp.co/browse/BG-49771) - Script to create missing DWS "Transaction Updated" events for pending transactions. 

## [1.32.5] - 2021-03-17
### Added
- [BG-50172](https://jira.mgcorp.co/browse/BG-50172) - Additional logs to have visibility on QYSSO biller response. 

## [1.32.4] - 2021-03-15
### Added
- [BG-50172](https://jira.mgcorp.co/browse/BG-50172) - Additional logs for QYSSO to have more visibility on the process.

## [1.32.3] - 2021-03-15
### Fixed
- [BG-50070](https://jira.mgcorp.co/browse/BG-50070) - DWS Event "Transaction Created" on complete process for 3DS flows by replacing it with "Transaction Updated".

## [1.32.2] - 2021-03-15
### Updated
- [BG-49833](https://jira.mgcorp.co/browse/BG-49833) - Value for billerTransactionId when the transaction is approved. 

## [1.32.1] - 2021-03-11
### Added
- [BG-47073](https://jira.mgcorp.co/browse/BG-47073) - Flag for isNSFSupported per site to do the card upload only if enabled. 

## [1.32.0] - 2021-03-11
### Updated
- [BG-48708](https://jira.mgcorp.co/browse/BG-48708) - Remove biller id usage. Transition to biller name.

## [1.31.15] - 2021-03-10
### Updated
- [BG-49377](https://jira.mgcorp.co/browse/BG-49377) - Netbilling service that changes the execution timeout.

## [1.31.14] - 2021-03-08
### Updated
- [BG-49776](https://jira.mgcorp.co/browse/BG-49776) - Netbilling service that contains more logs and data for investigations.

## [1.31.13] - 2021-03-01
### Fixed
- [BG-49771](https://jira.mgcorp.co/browse/BG-49771) - Missing DWS event "transaction updated" for 3DS2 frictionless flow, for the main transactions.

## [1.31.12] - 2021-02-25
### Updated
- [BG-49788](https://jira.mgcorp.co/browse/BG-49788) - 3ds info retrieval to originate from transaction entity instead of biller interactions.

## [1.31.11] - 2021-02-24
### Updated
- [BG-49776](https://jira.mgcorp.co/browse/BG-49776) - Netbilling service to add headers to the log and a new log for timeout when calling "Direct" endpoint.

## [1.31.10] - 2021-02-23
### Added
- [BG-49341](https://jira.mgcorp.co/browse/BG-49341) - Qysso to base event and biller transaction id creation.

## [1.31.9] - 2021-02-22
### Fixed
- [BG-48910](https://jira.mgcorp.co/browse/BG-48910) - Exposed 3ds information on transaction retrieve.

## [1.31.8] - 2021-02-18
### Updated
- [BG-49578](https://jira.mgcorp.co/browse/BG-49578) - The way we determine the 3DS version based on new input from Rocketgate.

## [1.31.7] - 2021-02-11
### Added
- [BG-48693](https://jira.mgcorp.co/browse/BG-48693) - Update the Rocketgate and Netbilling Service for Charge-only & fix for non-initial days zero join purchase for netbilling.

## [1.31.6] - 2021-02-10
### Updated
- [BG-48693](https://jira.mgcorp.co/browse/BG-48693) - Reverted: Update the Rocketgate and Netbilling Service for Charge-only.

## [1.31.5] - 2021-02-10
### Updated
- [BG-49183](https://jira.mgcorp.co/browse/BG-49183) - Enabled NSF flow.

## [1.31.4] - 2021-02-10
### Added
- [BG-48693](https://jira.mgcorp.co/browse/BG-48693) - Update the Rocketgate and Netbilling Service for Charge-only.

## [1.31.3] - 2021-02-08
### Fixed
- [BG-49233](https://jira.mgcorp.co/browse/BG-49233) - Missing ThreeD version on the BI events.

## [1.31.2] - 2021-02-03
### Fixed
- [BG-49178](https://jira.mgcorp.co/browse/BG-49178) - Flow of 3DS2 transactions when switched to 3DS so that transactions can be completed successfully.

## [1.31.1] - 2021-01-19
### Updated
- [BG-48611](https://jira.mgcorp.co/browse/BG-48611) - Qysso package which adds CPM - Custom Payment Method (required field on production for Debit transactions).

## [1.31.0] - 2021-01-14
### Added
- [BG-48195](https://jira.mgcorp.co/browse/BG-48195) - Support for Rocketgate ACH.

## [1.30.0] - 2021-01-11
### Added
- [BG-48015](https://jira.mgcorp.co/browse/BG-48015) - Support for Qysso (new Biller).

## [1.29.6] - 2020-12-10
### Fixed
- [BG-47475](https://jira.mgcorp.co/browse/BG-47475) - Namespace for middleware to allow composer v2 usage.

## [1.29.5] - 2020-12-03
### Added
- [BG-47244](https://jira.mgcorp.co/browse/BG-47244) - Support for RG merchant initiated 3DS transactions.

## [1.29.4] - 2020-12-02
### Added
- [BG-45092](https://jira.mgcorp.co/browse/BG-45092) - Flag in environment to disable NSF flow easily on request.

## [1.29.3] - 2020-11-19
### Fixed
- [BG-47335](https://jira.mgcorp.co/browse/BG-47335) - Update the cancel rebill operation to handle 500 error for netbilling

## [1.29.2] - 2020-11-05
### Updated
- [BG-44838](https://jira.mgcorp.co/browse/BG-44838) - Transaction retrieval endpoint to retrieve legacy transaction.
- [BG-46575](https://jira.mgcorp.co/browse/BG-46575) - Add postback legacy * id's to transaction

## [1.29.1] - 2020-11-04
### Fixed
- [BG-46633](https://jira.mgcorp.co/browse/BG-46633) - Hard coded values of C4S site used as temporary solution to exclude it when an NSF transaction occurred, as it does not support NSF feature. 

## [1.29.0] - 2020-11-02
### Added
- [BG-44660](https://jira.mgcorp.co/browse/BG-44660) - Epoch support for secondary purchases.

## [1.28.5] - 2020-10-21
### Updated
- [BG-42308](https://jira.mgcorp.co/browse/BG-42308) - Validation of email to accept special characters

## [1.28.4] - 2020-10-20
### Fixed
- [BG-46606](https://jira.mgcorp.co/browse/BG-46606) - Internal server error when RG triggers 3DS on their side.
### Updated
- [BG-46617](https://jira.mgcorp.co/browse/BG-46617) - Paypal purchase handling for Epoch.

## [1.28.3] - 2020-10-18
### Fixed
- [BG-46714](https://jira.mgcorp.co/browse/BG-46714) - Cast to string the responses from billers before creating the mapping criteria object. 

## [1.28.2] - 2020-10-16
### Fixed
- [BG-46714](https://jira.mgcorp.co/browse/BG-46714) - Cast to string the responses from billers before creating the mapping criteria object. 

## [1.28.1] - 2020-10-15
### Added
- [BG-43531](https://jira.mgcorp.co/browse/BG-43531) - Error classification in the purchase process response for declined transactions when using Rocketgate and Netbilling. 

## [1.28.0] - 2020-10-15
### Added
- [BG-45678](https://jira.mgcorp.co/browse/BG-45678) - Support Pumapay Purchases with MGPG.
### Fixed
- [BG-45686](https://jira.mgcorp.co/browse/BG-45686) - Add Custom Fields on legacy service request.

## [1.27.0] - 2020-10-13
### Added
- [BG-44660](https://jira.mgcorp.co/browse/BG-44660) - Secondary revenue support for Epoch.

### Added
- [BG-43900](https://jira.mgcorp.co/browse/BG-43900) - 3DS2.0 support on the sale endpoint.
- [BG-44362](https://jira.mgcorp.co/browse/BG-44362) - New lookup endpoint to perform 3DS2.0 transactions.
- [BG-44447](https://jira.mgcorp.co/browse/BG-44447) - 3DS2.0 support on the retrieve endpoint for version and 3DS new params.
- [BG-44323](https://jira.mgcorp.co/browse/BG-44323) - 3DS2.0 support on the complete endpoint for MD param.

## [1.26.3] - 2020-10-08
### Fixed
- [BG-46396](https://jira.mgcorp.co/browse/BG-46396) - Updated epoch library with epoch prod environment information.

## [1.26.2] - 2020-10-07
## Fixed
- [BG-45127](https://jira.mgcorp.co/browse/BG-45127) - Update netbilling-service version that handle netbilling exceptions from netbilling and extract card info from tests to env file.
- [BG-46105](https://jira.mgcorp.co/browse/BG-46105) - Update netbilling-service version that increase CURL connectionTime and executionTimeOut according to Legacy and pass member_id as like as trans_id

## [1.26.1] - 2020-09-08
### Changed
- [BG-44856](https://jira.mgcorp.co/browse/BG-44856) - Pumapay response handle to cover more exceptional cases.

## [1.26.0] - 2020-09-02
## Added
- [BG-44752](https://jira.mgcorp.co/browse/BG-44752) - Cross sale biller interaction when the user is redirected back from Legacy completing the transaction.
- [BG-43702](https://jira.mgcorp.co/browse/BG-43702) - Main purchase biller interaction when the user is redirected back from Legacy completing the transaction.

## [1.25.4] - 2020-08-30
## Fixed
- [BG-45123](https://jira.mgcorp.co/browse/BG-45123) - Update netbilling-service version that better handles Netbilling timed-out for Poll requests.

## [1.25.3] - 2020-08-30
## Fixed
- [BG-45123](https://jira.mgcorp.co/browse/BG-45123) - Update netbilling-service version that better handles Netbilling timed-out.

## [1.25.2] - 2020-08-24
## Fixed
- [BG-43558](https://jira.mgcorp.co/browse/BG-43558) - Change the sequence of charge and update request during Netbilling updateRebill operation and also add member info for update rebill with new CC.

## [1.25.1] - 2020-08-13
### Fixed
- [BG-44269](https://jira.mgcorp.co/browse/BG-44269) - Implemented command update for Rocketgate NSF & remove auth transaction for NB NSF.

## [1.25.0] - 2020-08-17
### Added
- [BG-43000](https://jira.mgcorp.co/browse/BG-43000) - Create pending transactions and return generated encrypted legacy redirect Purchase URL.

## [1.24.1] - 2020-08-13
### Fixed
- [BG-41981](https://jira.mgcorp.co/browse/BG-41981) - Pass disable Fraud Check flag to netbilling.

## [1.24.0] - 2020-08-13
### Added
- [BG-41637](https://jira.mgcorp.co/browse/BG-41637) - Epoch support for other payment types.

## [1.23.2] - 2020-08-06
### Fixed
- [BG-43013](https://jira.mgcorp.co/browse/BG-43013) - Undefined property approved amount.
- [BG-43013](https://jira.mgcorp.co/browse/BG-43013) - Cast code to int for setCode method.
- [BG-43013](https://jira.mgcorp.co/browse/BG-43013) - Return empty biller interaction when retrieving an aborted Netbilling transaction.

## [1.23.1] - 2020-08-03
### Fixed
- [BG-44271](https://jira.mgcorp.co/browse/BG-44271) - Error when AUTH Rocketgate transaction is returned as NSF.

## [1.23.0] - 2020-08-03
### Added
- [BG-43399](https://jira.mgcorp.co/browse/BG-43399) - Support for NSF transaction for Rocketgate and Netbilling.

## [1.22.2] - 2020-07-21
### Fixed
- [BG-41981](https://jira.mgcorp.co/browse/BG-41981) - Add more logs on netbilling package.

## [1.22.1] - 2020-07-14
### Fixed
- [BG-43657](https://jira.mgcorp.co/browse/BG-43657) - Retrieving an aborted Epoch transaction without a biller response.

## [1.22.0] - 2020-07-13
### Added
- [BG-42480](https://jira.mgcorp.co/browse/BG-42480) - Update HTTP Response code in case of route-not-found from 400 to 404 
- [BG-41191](https://jira.mgcorp.co/browse/BG-41152) - Biller interaction for join postback Epoch.
- [BG-41132](https://jira.mgcorp.co/browse/BG-41132) - New sale endpoint for Epoch.
- [BG-41191](https://jira.mgcorp.co/browse/BG-41191) - Abort transaction endpoint.

## [1.21.6] - 2020-07-06
### Fixed
- [BG-43505](https://jira.mgcorp.co/browse/BG-43505) - Fix expiration date sent to netbilling.

## [1.21.5] - 2020-07-05
### Updated
- [BG-43505](https://jira.mgcorp.co/browse/BG-43505) - Netbilling Service package.

## [1.21.4] - 2020-06-29
### Updated
- [BG-43431](https://jira.mgcorp.co/browse/BG-43431) - BI Logger version to exclude logger new obfuscation methods.

## [1.21.3] - 2020-06-29
### Updated
- [BG-42201](https://jira.mgcorp.co/browse/BG-42201) - Logger version to include new obfuscation methods.

## [1.21.2] - 2020-05-20
### Fixed
- [BG-42063](https://jira.mgcorp.co/browse/BG-42063) - Corrected RG biller transaction types.

## [1.21.1] - 2020-05-20
### Fixed
- [BG-42087](https://jira.mgcorp.co/browse/BG-42087) - Proper handling for guidNo when Rocketgate fails transaction.

## [1.21.0] - 2020-05-19
### Added
- [BG-40877](https://jira.mgcorp.co/browse/BG-40877) - Accept control Keyword as a biller field for netbilling.

## [1.20.0] - 2020-05-14
### Added
- [BG-41239](https://jira.mgcorp.co/browse/BG-41239) - Mocked responses for Epoch integration.

## [1.19.3] - 2020-05-04
### Fixed
- [BG-41415](https://jira.mgcorp.co/browse/BG-41415) - Handle missing "charge information" on Retrieve for Rebill Update Return Type.

## [1.19.2] - 2020-04-28
### Fixed
- [BG-40510](https://jira.mgcorp.co/browse/BG-40510) - Handle the way we log response got from Pumapay.

## [1.19.1] - 2020-04-28
### Fixed
- [BG-40505](https://jira.mgcorp.co/browse/BG-40505) - Exception handling when retrieving Pumapay biller response (failed) and using that response to generate transaction info.
- [BG-40510](https://jira.mgcorp.co/browse/BG-40510) - Exception handling for Pumapay when trying to purchase with unsupported currencies.

## [1.19.0] - 2020-04-27
### Added
- [BG-40947](https://jira.mgcorp.co/browse/BG-40947) - Allow member login to be passed on existing card purchase for Netbilling.
- [BG-40901](https://jira.mgcorp.co/browse/BG-40901) - Update Rebill Endpoint with support for new credit card.
- [BG-40018](https://jira.mgcorp.co/browse/BG-40018) - Cancel a rebilling Netbilling membership.

### Fixed
- [BG-40901](https://jira.mgcorp.co/browse/BG-40901) - Update Rebill Endpoint for existing payment template.

## [1.18.4] - 2020-04-09
### Fixed
- [BG-40766](https://jira.mgcorp.co/browse/BG-40766) - Invalid transaction status when 3DS is not required but Rocketgate answers with 3DS error code.

## [1.18.3] - 2020-04-07
### Fixed
- [BG-40599](https://jira.mgcorp.co/browse/BG-40599) - Invalid transaction id causing a 500 response code to be returned instead of 400.

## [1.18.2] - 2020-04-06
### Added 
- [BG-37589](https://jira.mgcorp.co/browse/BG-37589) - Rebill info for rebill update transaction and previous transaction id on transaction retrieve endpoint.
### Fixed
- [BG-40438](https://jira.mgcorp.co/browse/BG-40438) - Added session id on complete 3ds endpoint.

## [1.18.1] - 2020-03-31
### Fixed
- [BG-39770](https://jira.mgcorp.co/browse/BG-39770) - Removed old api keys from config.

## [1.18.0] - 2020-03-26
### Added
- [BG-38662](https://jira.mgcorp.co/browse/BG-38662) - Update the full membership with the correct billing cycle.

## [1.17.2] - 2020-03-17
### Fixed
- [BG-39922](https://jira.mgcorp.co/browse/BG-39922) - Wrong biller transaction type being displayed on the retrieve endpoint.

## [1.17.1] - 2020-03-12
### Fixed
- [BG-39906](https://jira.mgcorp.co/browse/BG-39906) - Wrong success answer in completeThreeD endpoint open api.

## [1.17.0] - 2020-03-10
### Added
- [BG-38922](https://jira.mgcorp.co/browse/BG-38922) - Complete ThreeD endpoint for Rocketgate.
- [BG-38795](https://jira.mgcorp.co/browse/BG-38795) - Real ThreeD data to the retrieve endpoint.

## [1.16.2] - 2020-03-05
### Updated
- [BG-39222](https://jira.mgcorp.co/browse/BG-39222) - RG errors codes to our mapped errors.
### Changed
- [BG-39685](https://jira.mgcorp.co/browse/BG-39685) - Handle Pumapay transactions based on typeID instead of index.

## [1.16.1] - 2020-03-05
### Fixed
- [BG-38803](https://jira.mgcorp.co/browse/BG-38803) - Removed the biller member id form the biller transactions array.

## [1.16.0] - 2020-03-03
### Added
- [BG-38055](https://jira.mgcorp.co/browse/BG-38055) - Support for 3DS auth for a new credit card.
- [BG-38056](https://jira.mgcorp.co/browse/BG-38056) - Support for retry transaction for a non eligible card for 3D.

## [1.15.1] - 2020-03-02
### Added
- [BG-39579](https://jira.mgcorp.co/browse/BG-39579) - EncryptText parameter to the qr retrieve endpoint.

## [1.15.0] - 2020-02-26
### Changed
- [BG-38796](https://jira.mgcorp.co/browse/BG-38796) - Renamed 3DS usage flag.
- [BG-38797](https://jira.mgcorp.co/browse/BG-38797) - 3DS usage flag to false.
### Updated
- [BG-37819](https://jira.mgcorp.co/browse/BG-37819) - Use new Rocketgate library version supporting the 3DS flow.

## [1.14.1] - 2020-02-25
### Added
- [BG-38651](https://jira.mgcorp.co/browse/BG-38651) - Dynamic information received from the biller to the BI event for new and rebill transactions.

## [1.14.0] - 2020-02-18
### Added
- [BG-38936](https://jira.mgcorp.co/browse/BG-38936) - Mock response for completeThreeD endpoint.
- [BG-39116](https://jira.mgcorp.co/browse/BG-39116) - Biller transactions & 3DSUsage flag to OpenApi docs.
- [BG-39108](https://jira.mgcorp.co/browse/BG-39108) - Mock biller transaction data on the retrieve transaction endpoint.
- [BG-38935](https://jira.mgcorp.co/browse/BG-38935) - Complete 3D endpoint to OpenAPI docs.
- [BG-39174](https://jira.mgcorp.co/browse/BG-39174) - Mock ACS and pareq on the transaction new sale rocketgate endpoint.
### Updated
- [BG-38744](https://jira.mgcorp.co/browse/BG-38744) - New credit card sale RG endpoint on OpenAPI docs.

## [1.13.1] - 2020-01-23
### Fixed
- [BG-38341](https://jira.mgcorp.co/browse/BG-38341) - Pumapay url for retrieve qr code.

## [1.13.0] - 2020-01-20
- [BG-35071](https://jira.mgcorp.co/browse/BG-35071) - Return Netbilling biller member id on transaction response.
- [BG-38241](https://jira.mgcorp.co/browse/BG-38241) - For Netbilling accept biller member id for cross-sale purchase.

## [1.12.0] - 2020-01-15 
### Added
- [BG-38052](https://jira.mgcorp.co/browse/BG-38052) - Payment Template creation for reactivate expired operation.

## [1.11.0] - 2020-01-13
### Added
- [BG-36509](https://jira.mgcorp.co/browse/BG-36509) - Support Netbilling secondary purchases.
- [BG-37864](https://jira.mgcorp.co/browse/BG-37864) - Support bin routing on Netbilling for initial and secondary purchase.

## [1.10.1] - 2019-12-17
### Updated
- [BG-37443](https://jira.mgcorp.co/browse/BG-37443) - Add new property to the BI Transaction Created event called reasonCodeDecline to expose the decline reason.

## [1.10.0] - 2019-12-16

### Added
- [BG-37276](https://jira.mgcorp.co/browse/BG-37276) - Status parameter for retrieve transaction endpoint.
- [BG-36987](https://jira.mgcorp.co/browse/BG-36987) - Circuit breaker for update rebill at Rocketgate.
- [BG-37585](https://jira.mgcorp.co/browse/BG-37585) - Add merchant account on update rebill endpoint.

## [1.9.1] - 2019-12-13
### Fixed
- [BG-37692](https://jira.mgcorp.co/browse/BG-37692) - Fix card number and cvv2 obfuscation - Netbilling payload.

## [1.9.0] - 2019-12-12
### Added
- [BG-36658](https://jira.mgcorp.co/browse/BG-36658) - Start update and stop rebill on Rocketgate.
- [BG-37175](https://jira.mgcorp.co/browse/BG-37175) - Rebill on Pumapay.
- [BG-37177](https://jira.mgcorp.co/browse/BG-37177) - Cancel rebill on Pumapay.

## [1.8.1] - 2019-12-10
### Fixed
- [BG-37423](https://jira.mgcorp.co/browse/BG-37423) - Update the netbilling package for cross-sale fix.

## [1.8.0] - 2019-12-05
### Added
- [BG-36658](https://jira.mgcorp.co/browse/BG-36658) - Update rebill endpoint with mocked response.
- [BG-36290](https://jira.mgcorp.co/browse/BG-36290) - PumaPay retrieve QR code.
- [BG-36263](https://jira.mgcorp.co/browse/BG-36263) - PumaPay biller interaction.

## [1.7.3] - 2019-11-19
### Updated
- [BG-37063](https://jira.mgcorp.co/browse/BG-37063) - Add more log info for invalid credit card exception message.

## [1.7.2] - 2019-11-19
### Fixed
- [BG-36886](https://jira.mgcorp.co/browse/BG-36886) - Reverse code and message on NG transaction service error message.

## [1.7.1] - 2019-11-14 
### Added
- [BG-36035](https://jira.mgcorp.co/browse/BG-36035) - Expose field "card description" when retrieving transaction.

## [1.7.0] - 2019-11-07
### Added
- [BG-35560](https://jira.mgcorp.co/browse/BG-35560) - Cancel rebill endpoint.

## [1.6.0] - 2019-10-22
### Added
- [BG-35597](https://jira.mgcorp.co/browse/BG-35597) - Cancel rebill endpoint with mocked response.

## [1.5.2] - 2019-09-12
### Fixed
- [BG-34924](https://jira.mgcorp.co/browse/BG-34924) - Return a 400 error instead of 500 when an invalid sessionId is given.

## [1.5.1] - 2019-08-29
### Added
- [BG-31780](https://jira.mgcorp.co/browse/BG-31780) - sessionId validation on the url so that it does not break the filebeat config.

## [1.5.0] - 2019-08-14
### Added
- [BG-34118](https://jira.mgcorp.co/browse/BG-34118) - Prepaid card balance information added to Purchase Response and to TransactionCreated BI Event.

## [1.4.2] - 2019-08-12
### Updated
- [BG-33741](https://jira.mgcorp.co/browse/BG-33741) - Update Composer dependencies.

## [1.4.1] - 2019-07-24
### Added
- [BG-33402](https://jira.mgcorp.co/browse/BG-33402) - Added routingCode to TransactionCreated BI Event.

## [1.4.0] - 2019-07-09
### Added
- [BG-31406](https://jira.mgcorp.co/browse/BG-31406) - Added expiration month & expiration year on retrieve transaction.

## [1.3.0] - 2019-06-19
### Updated
- [BG-31348](https://jira.mgcorp.co/browse/BG-31348) - Updated open api doc.
### Added
- [BG-31882](https://jira.mgcorp.co/browse/BG-31882) - Created new card endpoint, deprecated the old generic endpoint.

## [1.2.1] - 2019-06-17
### Updated
- [BG-31124](https://jira.mgcorp.co/browse/BG-31124) – Updated logger package to have headers logged on request and response.

## [1.2.0] - 2019-05-13
### Added
- [BG-30986](https://jira.mgcorp.co/browse/BG-30986) - Free sale for joins.

## [1.1.2] - 2019-05-06
### Fixed
- [BG-29588](https://jira.mgcorp.co/browse/BG-29588) - "Upgrade to Lumen 5.8, PHPUnit 8 and vfsstream".

## [1.1.1] - 2019-04-17
### Added
- [BG-30363](https://jira.mgcorp.co/browse/BG-30363) - Obfuscate sensitive information on exception trace for transactions.

## [1.1.0] - 2019-04-16
### Fixed
- Removed unused EventStore methods.
### Added
- Docker volume for mysql container.
### Changed
- [BG-30258](https://jira.mgcorp.co/browse/BG-30258) - Update the TransactionCreatedEvent definition to also store the billerTransactionId and a generation timestamp.

## [1.0.3] - 2019-04-01
### Changed
- Renamed the event to Transaction_Created.
- Update the event definition to use sessionId and the biller Name instead of id.

## [1.0.2] - 2019-04-01
### Updated
- [BG-29558](https://jira.mgcorp.co/browse/BG-29558) - Namespace of RocketgateChargeService class, php docs and unused imports.

## [1.0.1] - 2019-02-27
### Fixed
- Set BILLER_ROCKETGATE_TEST_MODE to false on production.

## [1.0.0] - 2019-02-27
- Initial release.
