<?php

namespace Tal;

trait ChangeableMessageTrait
{
    use MessageTrait {
        setProtocolVersion as public;
        setHeader as public;
        addHeader as public;
        deleteHeader as public;
        setBody as public;
    }
}
