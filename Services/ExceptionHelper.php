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
     *
     * @return Message $mmMessage
     */
    public function convertExceptionToMessage(\Exception $exception, $source=NULL)
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

        $mmMessage->addAttachment($attachment);

        return $mmMessage;
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
        if (!empty($config) && array_key_exists('exception', $config)) {
            $exceptionConf = $config['exception'];
            if(array_key_exists('exclude_class', $exceptionConf)) {
                $className = get_class($exception);
                $excludeList = $exceptionConf['exclude_class'];
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