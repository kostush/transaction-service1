@echo off
::#########################################
::#     open-api 3 client generation      #
::#########################################

SET _service_name=transaction-service
SET _openapi_version=3.3.4

if not exist openapi-generator-cli.jar (
    ECHO openapi-generator-cli does not exist downloading...
    powershell -Command "Invoke-WebRequest -OutFile openapi-generator-cli.jar https://repo1.maven.org/maven2/org/openapitools/openapi-generator-cli/%_openapi_version%/openapi-generator-cli-%_openapi_version%.jar"
)

::#########################################
::#        PLEASE UPDATE PATHS            #
::#########################################

SET _config_path=./config.json
SET _docs_path=./openapi.yml
SET _client_generation_path=../../%_service_name%-client/

::#echo the command
ECHO java -jar openapi-generator-cli.jar generate -c %_config_path% -i %_docs_path% -g php -o %_client_generation_path%

java -jar openapi-generator-cli.jar generate -c %_config_path% -i %_docs_path% -g php -o %_client_generation_path%
