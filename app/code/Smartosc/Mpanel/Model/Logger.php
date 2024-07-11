<?php

namespace Smartosc\Mpanel\Model;

use Monolog\Logger as BaseLogger;
use Smartosc\Mpanel\Logger\Handler\BaseFactory as HandlerFactory;

/**
 * Class Minor Logger
 */
class Logger extends BaseLogger
{
    const DEFAULT_NAME = 'Smartosc';
    /**
     * @param HandlerFactory $handlerFactory
     * @param string $name
     * @param array $processors
     * @param string|null $directoryName
     */
    public function __construct(
        HandlerFactory $handlerFactory,
        $name = self::DEFAULT_NAME,
        $processors = [],
        $directoryName = null
    ) {
        parent::__construct(
            $name,
            [$handlerFactory->create(['directoryName' => $directoryName])],
            $processors
        );
    }
}
