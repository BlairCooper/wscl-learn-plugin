<?php
declare(strict_types = 1);
namespace RCS\WP;

use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;
use RCS\Traits\SingletonTrait;

class PluginLogger implements LoggerInterface
{
    use SingletonTrait;

    private string $pluginSlug;
    private string $logFile= '';

    private LoggerInterface $logger;

    protected function __construct(?string $pluginSlug)
    {
        if (is_null($pluginSlug)) {
            $this->pluginSlug = 'unknown_plugin';
        } else {
            $this->pluginSlug = $pluginSlug;
        }
    }

    protected function initializeInstance(): void
    {
        $uploadDirInfo = wp_get_upload_dir();

        $logDir = trailingslashit(trailingslashit($uploadDirInfo['basedir']) . $this->pluginSlug);
        wp_mkdir_p($logDir);

        $this->logFile = $logDir . $this->pluginSlug . '.log';

        $dateFormat = 'M d H:i:s';
        $msgFormat = join(' ', [
            '%datetime%',
            '%level_name%',
            '[%extra.reqId%]',
            ':',
            '%message% %context% %extra%'
        ]
            ).PHP_EOL;

            $formatter = new LineFormatter ($msgFormat, $dateFormat, false, true);
            $formatter->setMaxLevelNameLength(3);

//             $handler = new StreamHandler($this->logFile);
//             $handler->setFormatter($formatter); //  attach the formatter to the handler

            $handler = new RotatingFileHandler($this->logFile, 14, Level::Debug);
            $handler->setFormatter($formatter); //  attach the formatter to the handler

            $logger = new Logger($this->pluginSlug);
            $logger->pushHandler($handler);
            $logger->pushProcessor(new PsrLogMessageProcessor(null, true));
            $logger->pushProcessor(function (LogRecord $record): LogRecord {
                if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
                    $reqId = $_SERVER['REQUEST_TIME_FLOAT'];
                } else {
                    $reqId = $_SERVER['REQUEST_TIME'];
                }

                $record->extra['reqId'] = str_pad(strval($reqId), 15, '0', STR_PAD_RIGHT);

                return $record;
            });

                $this->logger = $logger;
    }

    public function getLogFile(): string
    {
        return $this->logFile;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::alert()
     */
    public function alert(string|\Stringable $message, array $context = array()): void
    {
        $this->logger->alert($message, $context);
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::critical()
     */
    public function critical(string|\Stringable $message, array $context = array()): void
    {
        $this->logger->critical($message, $context);
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::debug()
     */
    public function debug(string|\Stringable $message, array $context = array()): void
    {
        $this->logger->debug($message, $context);
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::emergency()
     */
    public function emergency(string|\Stringable $message, array $context = array()): void
    {
        $this->logger->emergency($message, $context);
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::error()
     */
    public function error(string|\Stringable $message, array $context = array()): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::info()
     */
    public function info(string|\Stringable $message, array $context = array()): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::log()
     */
    public function log($level, string|\Stringable $message, array $context = array()): void
    {
        $this->logger->log($level, $message, $context);
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::notice()
     */
    public function notice(string|\Stringable $message, array $context = array()): void
    {
        $this->logger->notice($message, $context);
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Log\LoggerInterface::warning()
     */
    public function warning(string|\Stringable $message, array $context = array()): void
    {
        $this->logger->warning($message, $context);
    }
}
