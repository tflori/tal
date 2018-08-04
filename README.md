# tal

This is a clone of guzzle/psr-7 with additional functionality like sending responses and managing cookies. Because they
changed the access level of the properties to be private I was forced to copy the code instead of extending it.

If you think the code from guzzle has changed a lot and this is outdated - feel free open a pull request.

## PSR-7 Extended

This fork is using an extended and fully compatible version of PSR-7. This extension breaks some statements made on the
meta document of PSR-7.

In PSR-7 every message should be immutable. That means that you can not change a message after it got created. Instead
you have to clone it and modify the clone. I do not fully agree that every message has to be immutable. While it is true
that a request send from a client on the servers side should not be changed because the request already happened. The
request can sill be changed before it gets sent to the server. The same is valid for the response: the client should
not change the response but the server can change the response before it gets sent to the client.

Therefore we split the request and response interfaces two:

* `Tal\Psr7Extended\ClientRequestInterface` - the request that will be sent from client to server
* `Tal\Psr7Extended\ClientResponseInterface` - the response that got sent from server to client
* `Tal\Psr7Extended\ServerRequestInterface` - the request that got sent from client to server
* `Tal\Psr7Extended\ServerResponseInterface` - the response that will be sent from server to client

They are fully compatible with the PSR-7 interfaces. That means that the `with*()` methods still exist and can also be
used to change the client request and server response. But you have to understand that this is not just a philosophical
question: when you clone the message and modify the properties it means that the object has to be copied in the memory.
Even if you don't store the old object it has to be collected and removed from the garbage collector.
