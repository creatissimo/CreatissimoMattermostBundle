<?php
/**
 * Log symfony exceptionst to Mattermost
 *
 * User: pascal
 * Date: 16.10.16
 * Time: 21:33
 */

namespace Crea\MattermostBundle\Service;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Crea\MattermostBundle\Service\MattermostHelper;

class ExceptionHandler
{
    /** @var  LoggerInterface  */
    private $logger;
    /** @var string */
    private $environment;
    /** @var MattermostHelper */
    private $mmHelper;
    /** @var string */
    private $botname;
    /** @var array */
    private $environmentConfigurations;

    /**
     * @param LoggerInterface $logger
     * @param $environment
     */
    public function __construct(LoggerInterface $logger, $environment, MattermostHelper $mmHelper)
    {
        $this->logger = $logger;
        $this->environment = $environment;
        $this->mmHelper = $mmHelper;
    }

    /**
     * Handle the exception
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if ($this->shouldProcessException($exception))
        {
            $this->postToMattermost($exception);
        }
        return;
    }

    /**
     * Post exception details to Mattermost
     *
     * @param \Exception $exception
     */
    protected function postToMattermost(\Exception $exception)
    {
        $this->formatMattermostMessageForException($exception);
        $this->mmHelper->post($exception);
    }

    /**
     * Format the JSON message to post to Mattermost
     *
     * @param \Exception $exception
     */
    protected function formatExceptionForMessage(\Exception $exception)
    {
        $config = $this->getConfigForEnvironment();
        if (!empty($config) && $config['enabled'])
        {
            $this->mmHelper->setText($className . ' thrown in ' . $this->mmHelper->getName());

            $code = $exception->getCode();
            $text = $exception->getMessage();
            $file = $exception->getFile();
            $line = $exception->getLine();
            $fullClassName = get_class($exception);
            //$className = preg_replace('/^.*\\\\([^\\\\]+)$/', '$1', $fullClassName);
            $now = new \DateTime();

            $attachment = array(
                'fallback'=> $text,
                'color' => $config['color'],
                'pretext' => '',
                'title' => $fullClassName,
                'fields' => array(
                    array(
                        'title' => 'Message',
                        'value' => $text,
                    ),
                    array(
                        'title' => 'System',
                        'value' => $this->mmHelper->getName(),
                        'short' => 1,
                    ),
                    array(
                        'title' => 'Timestamp',
                        'value' => $now->format(DATE_ISO8601),
                        'short' => 1,
                    ),
                    array(
                        'title' => 'Code',
                        'value' => $code,
                        'short' => 1,
                    ),
                    array(
                        'title' => 'Environment',
                        'value' => $this->environment,
                        'short' => 1,
                    ),
                    array(
                        'title' => 'File',
                        'value' => $file,
                        'short' => 1,
                    ),
                    array(
                        'title' => 'Line',
                        'value' => $line,
                        'short' => 1,
                    ),
                ),
            );
            $this->mmHelper->addttachment($attachment);
        }
    }

    /**
     * Check to see if this exception is in an exclude list
     *
     * @param $exception
     * @return bool
     */
    private function shouldProcessException($exception)
    {
        $shouldProcess = true;
        $config = $this->getConfigForEnvironment();
        if (!empty($config))
        {
            if (array_key_exists('exclude_exception', $config))
            {
                $className = get_class($exception);
                $excludeList = $config['exclude_exception'];
                foreach ($excludeList as $exclude)
                {
                    if ($exclude == $className)
                    {
                        $shouldProcess = false;
                        break;
                    }
                }
            }
        }
        return $shouldProcess;
    }

    /**
     * Get the configuration for the current environment
     *
     * @return mixed
     */
    private function getConfigForEnvironment()
    {
        return $this->environmentConfigurations[$this->environment];
    }
}