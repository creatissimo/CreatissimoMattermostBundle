<?php
/**
 * Helper to create attachments for Mattermost messages
 *
 * User: pascal
 * Date: 04.11.16
 * Time: 16:05
 */

namespace Creatissimo\MattermostBundle\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Creatissimo\MattermostBundle\Entity\Attachment;
use Creatissimo\MattermostBundle\Entity\AttachmentField;

/**
 * Class AttachmentHelper
 * @package Creatissimo\MattermostBundle\Services
 */
class AttachmentHelper
{
    /**
     * AttachmentHelper constructor.
     *
     * @param ConsoleHelper $consoleHelper
     */
    public function __construct(ConsoleHelper $consoleHelper)
    {
        $this->consoleHelper = $consoleHelper;
    }

    /**
     * @param Request $request
     *
     * @return Attachment
     */
    public function convertRequestToAttachment(Request $request)
    {
        $attachment = new Attachment('Request information');
        $headers = $request->headers;

        $attachment->addField(new AttachmentField('Host', $headers->get('host'), true));
        $attachment->addField(new AttachmentField('URI', $request->getRequestUri(), true));
        $attachment->addField(new AttachmentField('Method', $request->getMethod(), true));
        $attachment->addField(new AttachmentField('IP', $request->getClientIp(), true));
        if($user = $request->getUser()) {
            $attachment->addField(new AttachmentField('User', $request->getUser(), true));
            $attachment->addField(new AttachmentField('User info', $request->getUserInfo(), true));
        }
        if(!empty($headers->get('referer'))) $attachment->addField(new AttachmentField('Referer', $headers->get('referer')));
        if(!empty($headers->get('user-agent'))) $attachment->addField(new AttachmentField('User-Agent', $headers->get('user-agent')));
        $attachment->addField(new AttachmentField('Request', $request->__toString()));

        return $attachment;
    }


    /**
     * @param Command        $command
     * @param InputInterface $input
     *
     * @return Attachment
     */
    public function convertCommandToAttachment(Command $command, InputInterface $input)
    {
        $attachment = new Attachment('Command information');

        $attachment->addField(new AttachmentField('Command', $command->getName()));

        if(!empty($input->__toString())) {
            $attachment->addField(new AttachmentField('Input', $input->__toString()));
        }

        if($argumentString = $this->consoleHelper->argumentsToString($input)) {
            $attachment->addField(new AttachmentField('Arguments', $argumentString));
        }

        if($optionString = $this->consoleHelper->optionsToString($input)) {
            $attachment->addField(new AttachmentField('Options', $optionString));
        }

        return $attachment;
    }
}