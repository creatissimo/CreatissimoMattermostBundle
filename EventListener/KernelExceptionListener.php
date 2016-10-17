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
use Creatissimo\MattermostBundle\Constant\ExceptionConstant;
use Creatissimo\MattermostBundle\Services\MattermostService;

class KernelExceptionListener
{
    /** @var MattermostService */
    private $mmService;

    /** @var ExceptionHelper */
    private $exceptionHelper;

    /** @var \Exception $exception */
    private $exception;

    /**
     * @param MattermostService  $mmService
     */
    public function __construct(MattermostService $mmService, ExceptionHelper $exceptionHelper)
    {
        $this->mmService = $mmService;
        $this->exceptionHelper = $exceptionHelper;
    }

    /**
     * Handle the exception
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $this->exception = $event->getException();
        if ($this->exceptionHelper->shouldProcessException($this->exception))
        {
            $this->postToMattermost();
        }
        return;
    }

    /**
     * Post exception details to Mattermost
     */
    protected function postToMattermost()
    {
        $this->exceptionHelper->formatExceptionForMessage($this->exception);
        $this->mmService->sendMessage();
    }
}