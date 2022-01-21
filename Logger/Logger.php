<?php

namespace Paypal\PaypalPlusBrasil\Logger;

use Paypal\PaypalPlusBrasil\Gateway\GeneralConfig;

class Logger extends \Monolog\Logger
{
    /**
     * @var GeneralConfig
     */
    private $generalConfig;

    /**
     * @param GeneralConfig $generalConfig
     * @param string $name
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        GeneralConfig $generalConfig,
        $name,
        $handlers = [],
        $processors = []
    ) {
        $this->generalConfig = $generalConfig;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * @param int $level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addRecord($level, $message, array $context = [])
    {
        if ($this->generalConfig->isDebugEnable()) {
            return parent::addRecord($level, $message, $context);
        }
        return true;
    }
}
