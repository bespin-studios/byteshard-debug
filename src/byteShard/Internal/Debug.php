<?php
/**
 * @copyright  Copyright (c) 2009 Bespin Studios GmbH
 * @license    See LICENSE file that is distributed with this source code
 */

namespace byteShard\Internal;

use Psr\Log\LoggerInterface;

class Debug
{
    /**
     * @var array<LoggerInterface>
     */
    private static array $loggers = [];

    private string $logDir        = 'logs';
    private string $debugFilename = 'debug.html';
    private int    $num           = 0;
    private string $file;
    private int    $line;
    private string $message;
    private mixed  $variable      = null;
    private string $variableType;
    private        $criticality;

    /**
     * Log Level 1 or greater
     * @param string $message
     * @param array $context
     * @param string $channel
     * @param int $debugDepth
     */
    public static function emergency(string $message, array $context = [], string $channel = 'byteShard', int $debugDepth = 4): void
    {
        self::log('emergency', $message, $context, $channel, $debugDepth);
    }

    /**
     * Log Level 2 or greater
     * @param string $message
     * @param array $context
     * @param string $channel
     * @param int $debugDepth
     */
    public static function alert(string $message, array $context = [], string $channel = 'byteShard', int $debugDepth = 4): void
    {
        self::log('alert', $message, $context, $channel, $debugDepth);
    }

    /**
     * Log Level 3 or greater
     * @param string $message
     * @param array $context
     * @param string $channel
     * @param int $debugDepth
     */
    public static function critical(string $message, array $context = [], string $channel = 'byteShard', int $debugDepth = 4): void
    {
        self::log('critical', $message, $context, $channel, $debugDepth);
    }

    /**
     * Log Level 4 or greater
     * @param string $message
     * @param array $context
     * @param string $channel
     * @param int $debugDepth
     */
    public static function error(string $message, array $context = [], string $channel = 'byteShard', int $debugDepth = 4): void
    {
        self::log('error', $message, $context, $channel, $debugDepth);
    }

    /**
     * Log Level 5 or greater
     * @param string $message
     * @param array $context
     * @param string $channel
     * @param int $debugDepth
     */
    public static function warning(string $message, array $context = [], string $channel = 'byteShard', int $debugDepth = 4): void
    {
        self::log('warning', $message, $context, $channel, $debugDepth);
    }

    /**
     * Log Level 6 or greater
     * @param string $message
     * @param array $context
     * @param string $channel
     * @param int $debugDepth
     */
    public static function notice(string $message, array $context = [], string $channel = 'byteShard', int $debugDepth = 4): void
    {
        self::log('notice', $message, $context, $channel, $debugDepth);
    }

    /**
     * Log Level 7 or greater
     * @param string $message
     * @param array $context
     * @param string $channel
     * @param int $debugDepth
     */
    public static function info(string $message, array $context = [], string $channel = 'byteShard', int $debugDepth = 4): void
    {
        self::log('info', $message, $context, $channel, $debugDepth);
    }

    /**
     * Log Level 8 or greater
     * @param string $message
     * @param array $context
     * @param string $channel
     * @param int $debugDepth
     */
    public static function debug(string $message, array $context = [], string $channel = 'byteShard', int $debugDepth = 4): void
    {
        self::log('debug', $message, $context, $channel, $debugDepth);
    }

    /**
     * @param string $type
     * @param string $message
     * @param array $context
     * @param string $channel
     * @param int $debugDepth
     * @internal
     */
    public static function log(string $type, string $message, array $context = [], string $channel = 'default', int $debugDepth = 4): void
    {
        $trace           = self::getStackTraceInformation($debugDepth);
        $context['file'] = $trace['file'];
        $context['line'] = $trace['line'];
        $log_channel     = null;
        if (array_key_exists($channel, self::$loggers)) {
            $log_channel = $channel;
        } elseif (array_key_exists('default', self::$loggers)) {
            $log_channel = 'default';
        }
        if ($log_channel !== null) {
            switch ($type) {
                case 'emergency':
                    self::$loggers[$log_channel]->emergency($message, $context);
                    break;
                case 'alert':
                    self::$loggers[$log_channel]->alert($message, $context);
                    break;
                case 'critical':
                    self::$loggers[$log_channel]->critical($message, $context);
                    break;
                case 'error':
                    self::$loggers[$log_channel]->error($message, $context);
                    break;
                case 'warning':
                    self::$loggers[$log_channel]->warning($message, $context);
                    break;
                case 'notice':
                    self::$loggers[$log_channel]->notice($message, $context);
                    break;
                case 'info':
                    self::$loggers[$log_channel]->info($message, $context);
                    break;
                case 'debug':
                    self::$loggers[$log_channel]->debug($message, $context);
                    break;
            }
        }
    }

    /**
     * @param string $name
     * @param LoggerInterface $logger
     */
    public static function addLogger(string $name, LoggerInterface $logger): void
    {
        self::$loggers[$name] = $logger;
    }

    /**
     * @param int $depth
     * @return array
     */
    private static function getStackTraceInformation(int $depth = 4): array
    {
        $calls = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $depth);
        foreach ($calls as $call) {
            if (isset($call['class'], $call['file'], $call['line']) && ($call['class'] === Debug::class || $call['class'] === \byteShard\Debug::class) && in_array($call['function'], ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'])) {
                return ['file' => $call['file'], 'line' => $call['line']];
            }
        }
        $last          = array_pop($calls);
        $trace['file'] = $last['file'] ?? '';
        $trace['line'] = $last['line'] ?? '';
        return $trace;
    }

    public function __construct(string $message, mixed $variable = null, $criticality = null, int $backtraceLevel = 1, string $fileName = '', bool $plain = false)
    {
        if (class_exists('config')) {
            $config       = new \config();
            $this->logDir = $config->getLogPath();
        } else {
            $this->logDir = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$this->logDir;
        }

        if ($variable !== null) {
            $this->variable = $variable;
        }
        $this->criticality = $criticality;
        $tmp               = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $backtraceLevel);
        $backtraceLevel--;
        $this->file    = $tmp[$backtraceLevel]['file'];
        $this->line    = $tmp[$backtraceLevel]['line'];
        $this->message = $message;

        $this->variableType = '<i>'.gettype($variable).'</i>';
        if (is_object($variable)) {
            $this->variableType .= ' (of type: <i>'.get_class($variable).'</i>)';
        } elseif (is_bool($variable)) {
            $this->variable = $variable === true ? 'true' : 'false';
        }
        if ($plain === true) {
            $this->printWithoutFormatToFile($message, $fileName, true);
        } else {
            $this->printToFile(print_r($this->variable, true), true, $fileName);
        }
    }

    private function printWithoutFormatToFile(string $message, string $filename, bool $date = true): void
    {
        if (preg_match("//u", $message)) {
            $message = utf8_decode($message);
        }
        $message    = ($date !== false) ? date('Y-m-d H:i:s').' '.$message.PHP_EOL : $message.PHP_EOL;
        $fileHandle = fopen($this->logDir.DIRECTORY_SEPARATOR.$filename, 'a+');
        if ($fileHandle !== false) {
            fwrite($fileHandle, $message);
            fclose($fileHandle);
        }
    }

    private function printToFile(string $string, bool $date = true, string $fileName = null): void
    {
        if ($fileName === null) {
            $filename = $this->logDir.DIRECTORY_SEPARATOR.(($date !== false) ? date('YmdHis').'_' : '').$this->debugFilename;
        } else {
            $filename = $this->logDir.DIRECTORY_SEPARATOR.$fileName;
        }

        $insertScript = true;
        if (preg_match("//u", $string)) {
            $string = utf8_decode($string);
        }
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            if ($content !== false) {
                if (str_contains($content, '<b>Logged on:</b>')) {
                    $insertScript = false;
                    $count        = substr_count($content, 'href="javascript: toggleDiv');
                    if ($count > 0) {
                        $this->num = $count;
                    }
                }
                unset($content);
            }
        }
        $fileHandle = fopen($filename, 'a+');
        if ($fileHandle !== false) {
            fwrite($fileHandle, $this->formatString($string, $insertScript));
            fclose($fileHandle);
        }
    }

    private function formatString(string $inputString, bool $script = true): string
    {
        // this formats the output of arrays and objects so that data is more readable
        $outputString = '';
        if ($script === true) {
            // if the log file doesn't have a javascript included, include this javascript
            $outputString .= "<script>function toggleDiv(num){var a=document.getElementById('d'+num);var b=document.getElementById('a'+num);var c=a.style.display;if(c=='none'){b.innerHTML='-';a.style.display='inline';}else{b.innerHTML='+';a.style.display='none';}}</script>
<style type=\"text/css\"><!--.arr {color:#6185A6}.ass {color:#C0C0C0}.ind {color:#00CC00}.pri {color:#CC0000}.pro {color:#9900CC}--></style>
";
        } else {
            // the log file already has some content, insert a horizontal line
            $outputString .= '<div style="width:100%;height:0;border-top:1px #000 solid;border-bottom:1px #CCC solid;"></div>';
        }
        $outputString .= '<pre>';

        // insert the date and time
        $outputString .= '<b>Logged on:</b> <i>'.date('d.m.y - G:i:s').'</i>';
        if (defined('MAIN') && isset($_SESSION[MAIN]) && class_exists('\byteShard\Internal\Session') && $_SESSION[MAIN] instanceof \byteShard\Internal\Session) {
            $outputString .= ' <b>by:</b> <i>'.$_SESSION[MAIN]->getUserID().'</i>';
        }

        // insert in which file Debug was called
        $outputString .= "\n<b>Debug called in file:</b> <i>".$this->file.'</i> <b>on line:</b> <i>'.$this->line.'</i>';
        if ($this->criticality !== null) {
            $outputString .= "\n<b>Criticality:</b> ".$this->criticality;
        }

        // insert the debug message in the log file
        $outputString .= "\n<b>Message:</b> ".$this->message;
        /*$captured = preg_split("/\r?\n/", $input_string);
        foreach ($captured as $line) {
            $outputString .= preg_replace("/(\s+)\)$/", '$1)</span>', preg_replace_callback("/(\s+)\($/", array($this, 'n_div'), $line))."\n";
        }*/

        if ($this->variable !== null) {
            // insert the optional inserted variable
            // todo: recursive function to run through arrays and objects and not only print_r them but also output the variable type (int, string etc...)
            $outputString .= "\n<b>Content (Type: ".$this->variableType."):</b> ";
            if ($this->variable !== null) {
                $captured = preg_split("/\r?\n/", $inputString);
                foreach ($captured as $line) {
                    $outputString .= preg_replace("/(\s+)\)$/", '$1)</span>', preg_replace_callback("/(\s+)\($/", $this->n_div(...), $line))."\n";
                }
            }
        }

        $outputString .= "</pre>\n";
        //Format the output
        $outputString = preg_replace("/\[(\d*)\]/i", '[<span class="ind">$1</span>]', $outputString);
        $outputString = str_replace('=> Array', '=&gt; <span class="arr">Array</span>', $outputString);
        $outputString = str_replace(':protected', ':<span class="pro">protected</span>', $outputString);
        $outputString = str_replace(':private', ':<span class="pri">private</span>', $outputString);
        $outputString = str_replace('=>', '<span class="ass">=&gt;</span>', $outputString);
        return $outputString;
    }

    private function n_div(array $matches): string
    {
        $this->num++;
        return $matches[1].'<a id=a'.$this->num.' href="javascript: toggleDiv('.$this->num.')">+</a><span id=d'.$this->num.' style="display:none">(';
    }
}
