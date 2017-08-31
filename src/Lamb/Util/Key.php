<?php

namespace Lamb\Util;

class Key
{
    const DESCRIPTION = 'description';
    const REQUEST     = 'request';
    const RESPONSE    = 'response';
    const METHOD      = 'method';

    const USABLE_METHOD_LIST = [
      'GET',
      'POST',
      'PUT',
      'DELETE',
    ];

    const HEADER    = 'header';
    const PARAMETER = 'parameter';
    const BODY      = 'body';

    const JSON_SCHEMA         = '$schema';
    const JSON_SCHEMA_VERSION = 'http://json-schema.org/draft-04/schema#';

    const POSTMAN_VERSION = 'https://schema.getpostman.com/json/collection/v2.0.0/collection.json';
    const SWAGGER_VERSION = '2.0';
}
