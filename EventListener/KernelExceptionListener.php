<?php
/**
 * Log symfony exceptionst to Mattermost
 *
 * User: pascal
 * Date: 16.10.16
 * Time: 21:33
 */

namespace Creatissimo\MattermostBundle\EventListener;

use Creatissimo\MattermostBundle\Services\AttachmentHelper;
use Creatissimo\MattermostBundle\Services\ExceptionHelper;
use Creatissimo\MattermostBundle\Services\MattermostService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;


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
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
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
        $message->addAttachment($this->attachmentHelper->convertRequestToAttachment($this->request));
        $this->mmService->setMessage($message)->send();
    }
}