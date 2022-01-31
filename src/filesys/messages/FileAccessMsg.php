<?php

declare(strict_types=1);
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

namespace pvc\filesys\messages;

use pvc\msg\Msg;

/**
 * Class FileAccessMsg
 */
class FileAccessMsg extends Msg
{
    protected string $domain = "messages+intl-icu";

    public function __construct(string $msgId, array $parameters = [])
    {
        parent::__construct($msgId, $parameters, $this->domain);
    }
}
