# MockServer

This is a local php server that lets you mock external web services so that you can
do integration testing.

You define the types of requests, and the responses each should provide, in a json
file.  Then run it using the local php server and point your application at the
mocked server.

It's kind-of inspired by MailCatcher (which is super).


## Features

 - Define GET and POST requests separately
 - Full parameter matching so you can respond differently to the same route
 but with different params
 - Respond with the appropriate HTTP code
 - RESTful API to retrieve requests/responses if necessary in your tests


## Usage

### Dependencies

This uses [Silex](http://silex.sensiolabs.org).  Install it with composer.  And you'll need
php 5.4 for the webserver and other bits and bobs.


### The .json file

You need a json file that the server will use to match routes and any GET or POST parameters,
and respond with the content and HTTP code you define.  Each route can have multiple
parameter matches.

The json file should look something like this:

```json
{
    "routes": {

        "lists/subscribe": {

            "get": [
                {
                    "params": {
                        "a": 2
                    },
                    "response": {
                        "httpcode": 200,
                        "body": "hello"
                    }
                },
                {
                    "params": {
                        "b": "testing",
                        "c": 45
                    },
                    "response": {
                        "httpcode": 301
                        "body": {
                            "an array": "can be provided",
                            "and": "it will do a json response"
                        }
                    }
                }
            ],

            "post": [
                {
                }
            ]
        },

        "another/route": {
            "get": [
                {
                    "params": {
                        "q": "if it is empty you will get a 200 response with no content"
                    }
                }
            ]
        }

    }
}

```

So the structure is something like this: `routes["particular/route"].get = []`.  The array is an array of objects,
each one specifying the parameters to match, and the response to return.  That is, for a particular route and method,
you can return different things depending on the parameters passed.  In a GET query, these are the query parameters,
in a POST they are the POSTed data.

If you specify `params`, they will have to match the request precisely.  If you do not specify any `params` then
anything will match.  So a request with parameters that do not match exactly any of the specified param sets will
return the same as an empty request.

The default value for `httpcode` is 200, and for `body` is '', the empty string.


Before starting the php server, you can use environment variables to set the definitions and data storage files:

```bash
mockserver$ export MOCKSERVER_DEFINITION=/path/to/your/server_definition.json
mockserver$ export MOCKSERVER_DATAFILE=/path/to/save/data/to.txt
```

These are optional: it will use `./mockserver.json` and `data/requests.txt` if they are not specified.


### Start the php server

Like this:

```bash
mockserver$ php -S localhost:8000 index.php
```

on whatever port.


### Point your application at the local server

That's up to you.  Hostnames might work, or a suitable config setting for the testing environment.


## Retrieving request/responses

There's a simple API that'll let you retrieve your requests and responses to the server.  Obviously
you'll need to use the same hostname:port that you use when you started the php server.  If the
 API you're mocking has a route `/__mockserver/` then you'll be in a bit of trouble and may have to
hack this a bit to remove the conflict.


#### http://localhost:8000/__mockserver/clear

Clears out any data (which is stored in ./data/requests.txt as a serialized array).  Because of this,
you'll want to clear it out at the beginning of any tests, and probably at the end too.  And
if you really want to hammer it (I can't see why, but anyway) then you might want to implement
a more efficient storage solution.

#### http://localhost:8000/__mockserver/show/{id}

Retrieves one or all requests/responses.  {id} may be:

 - `all`  Gets them all
 - `last` Gives the last request/response received
 - *`i`* An integer: gives a single request/response object

As mentioned, the data is stored in an array.  So *i* is the index in the array since you last
`clear`ed it out.

#### What you get

Something like this (for one).  They're always in an array.

```json
[
    {
        "request": {
            "path": "/lists/subscribe",
            "params": {
                "a": 4,
                "b": 50
            },
            "method": "GET"
        },
        "response": {
            "httpcode": 200,
            "content": "hello"
        }
    }
]

```

In your tests you'll want to use Guzzle or something to grab these afterwards (that's what
I do with MailCatcher, anyway).


## Why?

I want to run integration tests for Mailchimp.  This way I can specify realistic looking
responses to the API requests I make, and make sure my application is handling them
appropriately.


## Tests

There's some unit tests in `mockserver/src/tests` you can run with phpunit, if you like.  There's some unit
tests for the various components, and a Silex integration test which uses the server specified in `src/tests/testserver.json`


## License

MIT


## Copyright

Matt Parker, Lamplight Database Systems Limited 2014
www.lamplightdb.co.uk