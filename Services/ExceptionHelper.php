<?php
/**
 * Log symfony exceptionst to Mattermost
 *
 * User: pascal
 * Date: 16.10.16
 * Time: 21:33
 */

namespace Creatissimo\MattermostBundle\Services;

use Creatissimo\MattermostBundle\Constant\ExceptionConstant;
use Creatissimo\MattermostBundle\Entity\Attachment;
use Creatissimo\MattermostBundle\Entity\AttachmentField;
use Creatissimo\MattermostBundle\Entity\Message;

class ExceptionHelper
{
    /** @var MattermostService */
    private $mmService;

    /**
     * @param MattermostService $mmService
     */
    public function __construct(MattermostService $mmService)
    {
        $this->mmService = $mmService;
    }

    /**
     * Format the JSON message to post to Mattermost
     *
     * @param \Exception  $exception
     * @param String|null $source
     *
     * @return Message $mmMessage
     */
    public function convertExceptionToMessage(\Exception $exception, $source = null, $trace = false)
    {
        $code          = $exception->getCode();
        $message       = $exception->getMessage();
        $file          = $exception->getFile();
        $line          = $exception->getLine();
        $fullClassName = get_class($exception);
        $className     = preg_replace('/^.*\\\\([^\\\\]+)$/', '$1', $fullClassName);
        $now           = new \DateTime();

        $text = "#### ";
        $text .= $className . ' thrown in ' . $this->mmService->getAppName();
        if ($source) $text .= " @ \n" . $source;

        $mmMessage = new Message($text);

        $attachment = new Attachment($fullClassName);
        $attachment->setColor(ExceptionConstant::EXCEPTION_COLOR)
                   ->setFallback($message);

        $attachment->addField(new AttachmentField('Message', $message));
        $attachment->addField(new AttachmentField('File', $file));
        $attachment->addField(new AttachmentField('Line', strval($line), true));
        $attachment->addField(new AttachmentField('Code', strval($code), true));
        $attachment->addField(new AttachmentField('System', $this->mmService->getAppName(), true));
        $attachment->addField(new AttachmentField('Environment', $this->mmService->getEnvironment(), true));
        $attachment->addField(new AttachmentField('Timestamp', $now->format(DATE_ISO8601), true));

        if ($trace || $this->shouldAddTrace()) {
            $attachment->addField(new AttachmentField('Trace', $this->getExceptionTraceAsString($exception)));
        }

        $mmMessage->addAttachment($attachment);

        return $mmMessage;
    }


    /**
     * @var \Exception $exception
     * @var string     $source
     * @var bool       $trace
     *
     * @return bool
     */
    public function sendException(\Exception $exception, $source = null, $trace = false)
    {
        $message = $this->convertExceptionToMessage($exception, $source, $trace);

        return $this->mmService->setMessage($message)->send();
    }

    /**
     * Check if trace should be added
     *
     * @return bool
     */
    public function shouldAddTrace()
    {
        $config = $this->mmService->getEnvironmentConfiguration();
        if (!empty($config) && array_key_exists('exception', $config)) {
            $exceptionConf = $config['exception'];
            if (array_key_exists('trace', $exceptionConf) && $exceptionConf['trace']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check to see if this exception is in an exclude list
     *
     * @param \Exception $exception
     *
     * @return bool
     */
    public function shouldProcessException(\Exception $exception)
    {
        $shouldProcess = true;
        $config        = $this->mmService->getEnvironmentConfiguration();
        if (!empty($config) && array_key_exists('exception', $config)) {
            $exceptionConf = $config['exception'];
            if (array_key_exists('exclude_class', $exceptionConf)) {
                $className   = get_class($exception);
                $excludeList = $exceptionConf['exclude_class'];
                foreach ($excludeList as $exclude) {
                    if ($exclude == $className) {
                        $shouldProcess = false;
                        break;
                    }
                }
            }
        }

        return $shouldProcess;
    }


    /**
     * @param \Exception $exception
     *
     * @return string
     */
    private function getExceptionTraceAsString(\Exception $exception)
    {
        $rtn   = "";
        $count = 0;
        foreach ($exception->getTrace() as $frame) {
            $args = "";
            if (isset($frame['args'])) {
                $args = [];
                foreach ($frame['args'] as $arg) {
                    if (is_string($arg)) {
                        $args[] = "'" . $arg . "'";
                    } elseif (is_array($arg)) {
                        $args[] = "Array";
                    } elseif (is_null($arg)) {
                        $args[] = 'NULL';
                    } elseif (is_bool($arg)) {
                        $args[] = ($arg) ? "true" : "false";
                    } elseif (is_object($arg)) {
                        $args[] = get_class($arg);
                    } elseif (is_resource($arg)) {
                        $args[] = get_resource_type($arg);
                    } else {
                        $args[] = $arg;
                    }
                }
                $args = join(", ", $args);
            }
            $rtn .= sprintf("#%s %s(%s): %s(%s)\n",
                $count,
                isset($frame['file']) ? $frame['file'] : 'unknown file',
                isset($frame['line']) ? $frame['line'] : 'unknown line',
                (isset($frame['class'])) ? $frame['class'] . $frame['type'] . $frame['function'] : $frame['function'],
                $args);
            $count++;
        }

        return $rtn;
    }
}