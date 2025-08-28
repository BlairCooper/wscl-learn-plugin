<?php
declare(strict_types=1);
namespace RCS\Logging;

use Psr\Log\LoggerInterface;

class MethodLogger
{
    private string $method;
    private Timer $timer;

    public function __construct(
        private LoggerInterface $logger
        )
    {
        $this->logger = $logger;
        $this->method = $this->getCallingMethodName();

        $this->logger->debug("Entering {$this->method}");

        $this->timer = new Timer(true);
    }

    public function __destruct()
    {
        $this->timer->stop();

        $this->logger->debug("Exiting {$this->method} after {$this->timer->getTime()}");
    }

    private function getCallingMethodName(): string
    {
        $trace = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = $trace[2];

        $str = '';
        if (isset($caller['class'])) {
            $str .= $caller['class'];
        }
        if (isset($caller['type'])) {
            $str .= $caller['type'];
        }

        $str .= $caller['function'];

        return $str;
    }
}
