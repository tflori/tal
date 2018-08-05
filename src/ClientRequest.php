<?php

namespace Tal;

use Psr\Http\Message\UriInterface;
use Tal\Psr7Extended\ClientRequestInterface;

class ClientRequest extends Request implements ClientRequestInterface
{
    use ChangeableMessageTrait;

    /** {@inheritDoc} */
    public function setRequestTarget($requestTarget)
    {
        return parent::setRequestTarget($requestTarget);
    }

    /** {@inheritDoc} */
    public function setMethod($method)
    {
        return parent::setMethod($method);
    }

    /** {@inheritDoc} */
    public function setUri(UriInterface $uri, $preserveHost = false)
    {
        return parent::setUri($uri, $preserveHost);
    }
}
