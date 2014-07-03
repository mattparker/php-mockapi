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

This uses (Silex)[http://silex.sensiolabs.org].  Install it with composer.  And you'll need
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


### Start the php server

Like this:

```bash
mockserver$ php -S localhost:8000 index.php
```

on whatever port.


### Point your application at the local server

That's up to you.  Hostnames might work?


## Retrieving request/responses

There's a simple API that'll let you retrieve your requests and responses to the server.  Obviously
you'll need to use the same hostname:port that you use when you started the php server.  If the
 API you're mocking has a route /__mockserver/ then you'll be in a bit of trouble and may have to
hack this a bit to remove the conflict.


#### http://localhost:8000/__mockserver/clear

Clears out any data (which is stored in ./data/requests.txt as a serialized array).

#### http://localhost:8000/__mockserver/show/{id}

Retrieves one or all requests/responses.  {id} may be:

 - all  Gets them all
 - last Gives the last request/response received
 - *i* An integer: gives a single request/response object

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
            }
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

There's some unit tests in `mockserver/src/tests` you can run with phpunit, if you like.


## License

MIT


## Copyright

Matt Parker, Lamplight Database Systems Limited 2014
www.lamplightdb.co.uk