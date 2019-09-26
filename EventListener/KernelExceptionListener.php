<?php

namespace Creatissimo\MattermostBundle\EventListener;

use Creatissimo\MattermostBundle\Services\AttachmentHelper;
use Creatissimo\MattermostBundle\Services\ExceptionHelper;
use Creatissimo\MattermostBundle\Services\MattermostService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

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
     * @param MattermostService $mmService
     * @param ExceptionHelper   $exceptionHelper
     * @param AttachmentHelper  $attachmentHelper
     */
    public function __construct(MattermostService $mmService, ExceptionHelper $exceptionHelper, AttachmentHelper $attachmentHelper)
    {
        $this->mmService        = $mmService;
        $this->exceptionHelper  = $exceptionHelper;
        $this->attachmentHelper = $attachmentHelper;
    }

    /**
     * Handle the exception
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        if ($this->mmService->isEnabled('exception')) {
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
    protected function postToMattermost(): void
    {
        $message = $this->exceptionHelper->convertExceptionToMessage($this->exception);

        if ($this->exceptionHelper->shouldAddRequestInformation()) {
            $message->addAttachment($this->attachmentHelper->convertRequestToAttachment($this->request, $this->exceptionHelper->getTraceLevel()));
        }

        $this->mmService->setMessage($message)->send();
    }
}
