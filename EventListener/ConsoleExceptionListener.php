<?php

namespace Creatissimo\MattermostBundle\EventListener;

use Creatissimo\MattermostBundle\Services\AttachmentHelper;
use Creatissimo\MattermostBundle\Services\ExceptionHelper;
use Creatissimo\MattermostBundle\Services\MattermostService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Input\InputInterface;

class ConsoleExceptionListener
{
    /** @var MattermostService */
    private $mmService;

    /** @var ExceptionHelper */
    private $exceptionHelper;

    /** @var AttachmentHelper */
    private $attachmentHelper;

    /** @var \Exception $exception */
    private $exception;

    /** @var Command $command */
    private $command;

    /** @var InputInterface $input */
    private $input;

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
     * @param ConsoleExceptionEvent $event
     */
    public function onConsoleException(ConsoleExceptionEvent $event): void
    {
        if ($this->mmService->isEnabled('exception')) {
            $this->exception = $event->getException();
            if ($this->exceptionHelper->shouldProcessException($this->exception)) {
                $this->command = $event->getCommand();
                $this->input   = $event->getInput();
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

        $attachment = $this->attachmentHelper->convertCommandToAttachment($this->command, $this->input);
        $message->addAttachment($attachment);

        $this->mmService->setMessage($message)->send();
    }
}
