# Transaction Service

https://wiki.mgcorp.co/display/PROBILLER/Transaction+Service

## Project dependencies
* [Logger](https://stash.mgcorp.co/projects/pbngbe/repos/logger/browse/README.md)

## Get started

### Prerequisites
* [Docker](https://stash.mgcorp.co/projects/PBNGBE/repos/init/browse/DOCKER-SETTINGS.md)
* CLI (Command-line interpreter) that supports shell script execution.

### Steps
1. Checkout [this project](ssh://git@stash.mgcorp.co:7999/pbngbe/transaction-service.git).
2. Execute `sh init.sh`

Check [here](https://wiki.mgcorp.co/display/PROBILLER/Developer) for more information regarding setting up your development environment and overall development practices check.

## FAQ

1. How to stop the service?

    *Execute `docker-compose stop`*

2. How to start the service?

    *Execute `docker-compose up -d`*

3. How to go inside the service container?

    *Execute `docker-compose exec web bash`*
    
4. How to run composer:

    *Execute `composer install` into the container*
    
5. How I can access the service?

    *You will be able to access using http://localhost:8001.*
    
## Available Operations
- Create a transaction
- Retrieve transaction data

## Logs
This section explains the logging structure expected and presents the related configuration.

#### Application Logs
Any information that needs to be captured or is of some interest in future needs to be logged. These type of logs constitute application logs.

Example: Request to the application, various steps performed during execution, response returned by the application.

To support this, we added Filebeat/Elasticsearch/Kibana modules to gather and visualize such logs. 

_Related docker services:_
- elasticsearch
- filebeat
- kibana

_Logs directory:_
- /lumen/storage/logs

_Log file format_
- \<file name>.log

To view logs in Kibana, go to `http://localhost:5601` and switch to `Logs` tab.

#### Business Intelligence Event Logs
Certain specific information needs to be returned to BI (related to our processing) as requested by them. As the consumption of these events would be in Kafka, we needed to pass such events to Kafka broker.

To support this, we added Filebeat/Kafka modules to gather and visualize such events.

_Related docker services:_
- filebeat
- kafka

_Event logs directory:_
- /lumen/storage/events

_Event log file format_
- \<file name>.log

To view event logs, go to `http://localhost:3030/kafka-topics-ui/` and check for _topic-\<year.month.day>_ topic.

## Generate the client
### Requeriments
- Java JRE. https://java.com/en/download/manual.jsp;
- The `transaction-service` and `transaction-service-client` folders have to be on the same level.
### Windows
```
cd <workspace-folder>\transaction-service\docs
generate-client.cmd
```
It will download the openapi-generator-cli.jar, in case it is missing.

You should see something like:
```
openapi-generator-cli installed version:
3.3.4
Executing:
java -jar openapi-generator-cli.jar generate -c config.json -i openapi.yml -g php -o ../../transaction-service-client/
[main] INFO  o.o.c.languages.AbstractPhpCodegen - Environment variable PHP_POST_PROCESS_FILE not defined so the PHP code may not be properly formatted. To define it, try 'export PHP_POST_PROCESS_FILE="/usr/local/bin/prettier --write"' (Linux/Mac)
[main] INFO  o.o.c.languages.AbstractPhpCodegen - NOTE: To enable file post-processing, 'enablePostProcessFile' must be set to `true` (--enable-post-process-file for CLI).
...
```

Read more: https://wiki.mgcorp.co/display/PE/Generating+Client+Code+from+Open+API
