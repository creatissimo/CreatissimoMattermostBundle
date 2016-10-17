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

class ExceptionHelper
{
    /** @var MattermostService */
    private $mmService;

    /**
     * @param MattermostService  $mmService
     */
    public function __construct(MattermostService $mmService)
    {
        $this->mmService = $mmService;
    }

    /**
     * Format the JSON message to post to Mattermost
     *
     * @param \Exception $exception
     * @param String|null $source
     */
    public function formatExceptionForMessage(\Exception $exception, $source=NULL)
    {
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $fullClassName = get_class($exception);
        $className = preg_replace('/^.*\\\\([^\\\\]+)$/', '$1', $fullClassName);
        $now = new \DateTime();

        $text = "#### ";
        $text .= $className . ' thrown in ' . $this->mmService->getAppName();
        if($source) $text .= " @ \n".$source;
        $this->mmService->setText($text);

        $attachment = array(
            'fallback'=> $message,
            'color' => ExceptionConstant::EXCEPTION_COLOR,
            'pretext' => '',
            'title' => $fullClassName,
            'fields' => array(
                array(
                    'title' => 'Message',
                    'value' => $message,
                ),
                array(
                    'title' => 'File',
                    'value' => $file,
                ),
                array(
                    'title' => 'Line',
                    'value' => strval($line),
                    'short' => true,
                ),
                array(
                    'title' => 'Code',
                    'value' => strval($code),
                    'short' => true,
                ),
                array(
                    'title' => 'System',
                    'value' => $this->mmService->getAppName(),
                    'short' => true,
                ),
                array(
                    'title' => 'Environment',
                    'value' => $this->mmService->getEnvironment(),
                    'short' => true,
                ),
                array(
                    'title' => 'Timestamp',
                    'value' => $now->format(DATE_ISO8601),
                    'short' => true,
                )
            ),
        );
        $this->mmService->addttachment($attachment);
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
        $config = $this->mmService->getEnvironmentConfiguration();
        if (!empty($config))
        {
            if(!$config['enabled']) {
                $shouldProcess = false;
            } elseif (array_key_exists('exclude_exception', $config)) {
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
}