<?php
/**
 * Log symfony exceptionst to Mattermost
 *
 * User: pascal
 * Date: 16.10.16
 * Time: 21:33
 */

namespace Creatissimo\MattermostBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Request;
use Creatissimo\MattermostBundle\Services\AttachmentHelper;
use Creatissimo\MattermostBundle\Services\ExceptionHelper;
use Creatissimo\MattermostBundle\Services\MattermostService;


class KernelExceptionListener
{
    /** @var MattermostService */
    private $mmService;

    /** @var ExceptionHelper */
    private $exceptionHelper;

    /** @var AttachmentHelper */
    private $attachmentHelper;

    /** @var \Exception */
    private $exception;

    /** @var  Request */
    private $request;

    /**
     * @param MattermostService  $mmService
     */
    public function __construct(MattermostService $mmService, ExceptionHelper $exceptionHelper, AttachmentHelper $attachmentHelper)
    {
        $this->mmService = $mmService;
        $this->exceptionHelper = $exceptionHelper;
        $this->attachmentHelper = $attachmentHelper;
    }

    /**
     * Handle the exception
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if($this->mmService->isEnabled('exception')) {
            $this->exception = $event->getException();
            if ($this->exceptionHelper->shouldProcessException($this->exception)) {
                $this->request = $event->getRequest();
                $this->postToMattermost();
            }

            return;
        }
    }

    /**
     * Post exception details to Mattermost
     */
    protected function postToMattermost()
    {
        $message = $this->exceptionHelper->convertExceptionToMessage($this->exception);
        $message->addAttachment($this->attachmentHelper->convertRequestToAttachment($this->request));
        $this->mmService->setMessage($message)->send();
    }
}