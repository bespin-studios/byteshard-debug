<?php
/**
 * @copyright  Copyright (c) 2009 Bespin Studios GmbH
 * @license    See LICENSE file that is distributed with this source code
 */

namespace byteShard;

use byteShard\Internal\Debug as InternalDebug;
use Psr\Log\LoggerInterface;

class Debug
{
    /**
     * Log Level 1 or greater
     * @param string $message
     * @param array $context
     * @param string $channel
     */
    public static function emergency(string $message, array $context = [], string $channel = 'default'): void
    {
        InternalDebug::log('emergency', $message, $context, $channel);
    }

    /**
     * Log Level 2 or greater
     * @param string $message
     * @param array $context
     * @param string $channel
     */
    public static function alert(string $message, array $context = [], string $channel = 'default'): void
    {
        InternalDebug::log('alert', $message, $context, $channel);
    }

    /**
     * Log Level 3 or greater
     * @param string $message
     * @param array $context
     * @param string $channel
     */
    public static function critical(string $message, array $context = [], string $channel = 'default'): void
    {
        InternalDebug::log('critical', $message, $context, $channel);
    }

    /**
     * Log Level 4 or greater
     * @param string $message
     * @param array $context
     * @param string $channel
     */
    public static function error(string $message, array $context = [], string $channel = 'default'): void
    {
        InternalDebug::log('error', $message, $context, $channel);
    }

    /**
     * Log Level 5 or greater
     * @param string $message
     * @param array $context
     * @param string $channel
     */
    public static function warning(string $message, array $context = [], string $channel = 'default'): void
    {
        InternalDebug::log('warning', $message, $context, $channel);
    }

    /**
     * Log Level 6 or greater
     * @param string $message
     * @param array $context
     * @param string $channel
     */
    public static function notice(string $message, array $context = [], string $channel = 'default'): void
    {
        InternalDebug::log('notice', $message, $context, $channel);
    }

    /**
     * Log Level 7 or greater
     * @param string $message
     * @param array $context
     * @param string $channel
     */
    public static function info(string $message, array $context = [], string $channel = 'default'): void
    {
        InternalDebug::log('info', $message, $context, $channel);
    }

    /**
     * Log Level 8 or greater
     * @param string $message
     * @param array $context
     * @param string $channel
     */
    public static function debug(string $message, array $context = [], string $channel = 'default'): void
    {
        InternalDebug::log('debug', $message, $context, $channel);
    }

    /**
     * @param string $name
     * @param LoggerInterface $logger
     */
    public static function addLogger(string $name, LoggerInterface $logger): void
    {
        InternalDebug::addLogger($name, $logger);
    }
}
