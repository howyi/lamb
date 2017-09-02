[![Build Status](https://travis-ci.org/howyi/lamb.svg?branch=master)](https://travis-ci.org/howyi/lamb)
[![Coverage Status](https://coveralls.io/repos/github/howyi/lamb/badge.svg?branch=master)](https://coveralls.io/github/howyi/lamb?branch=master)
# lamb
JSON Schema(&YAML) extension & API document generator

<!-- ## wiki: https://github.com/howyi/lamb/wiki -->

## Usable API
|||
|-------------|:-------------:|
|method|GET, POST, PUT, DELETE|
|body|JSON|

## Quickstart
### Install
`composer require howyi/lamb`
### Config
Put `lamb.yml` in current working directory
```YAML
name: LambTestAPI
version: 1.0.0

path:
  collection:  sample/collection
  environment: sample/environment
  request:     sample/request
  scenario:    sample/scenario

environment:
  host: https://example.com/v2

default:
  request:
    header:
      sessionKey:
        value: ((sessionKey))
    parameter:
      # sessionKey:
      #   required: true
      #   value: ((sessionKey))
```

## API format
#### YAML
sample/collection/account/settings.yml  
API endpoint: https://<i></i>example.com/v2/account/settings
```YAML
POST:
  description: Updates user's settings.

  request:
    body:
      $schema: http://json-schema.org/draft-04/schema#
      type: object
      properties:
        language:
          title: user's language
          type: string
        age:
          title: age
          type: int
      additionalProperties: true
      required: []


  response:
    body:
      $schema: http://json-schema.org/draft-04/schema#
      type: object
      properties:
        language:
          title: user's language
          type: string
        age:
          title: age
          type: int
      additionalProperties: true
      required: [language, age]
```
#### JSON
sample/collection/account/update_profile.json  
API endpoint: https://<i></i>example.com/v2/account/update_profile
```JSON
{
  "POST": {

    "description": "Updates user's profile.",

    "request": {
      "body": {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "object",
        "properties": {
          "language": {
            "title": "user's language",
            "type": "string"
          },
          "age": {
            "title": "age",
            "type": "int"
          }
        },
        "additionalProperties": true,
        "required" : []
      }
    },

    "response": {
      "body": {
        "$schema": "http://json-schema.org/draft-04/schema#",
        "type": "object",
        "properties": {
          "language": {
            "title": "user's language",
            "type": "string"
          },
          "age": {
            "title": "age",
            "type": "int"
          }
        },
        "additionalProperties": true,
        "required" : ["language", "age"]
      }
    }

  }
}
```
## Environment format
#### YAML
sample/environment/sample_env.yml  
```YAML
sessionKey: hogehogehogehoge
```
#### JSON
```JSON
{
  "sessionKey": "hogehogehogehoge"
}
```
## Generate POSTMAN Collection
```php
$collection = \Lamb\CollectionStructureFactory::fromDir();
dump(\Lamb\Generator\Postman::collection($collection));

$environment = \Lamb\EnvironmentStructureFactory::fromDir();
dump(\Lamb\Generator\Postman::environment($environment));
```

## Generate Swagger document
```php
$collection = \Lamb\CollectionStructureFactory::fromDir();
$environment = \Lamb\EnvironmentStructureFactory::fromDir();

dump(\Lamb\Generator\Swagger::document($collection, $environment, 'your_env'));
```
