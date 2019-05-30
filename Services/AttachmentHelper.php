<?php
/**
 * Helper to create attachments for Mattermost messages
 *
 * User: pascal
 * Date: 04.11.16
 * Time: 16:05
 */

namespace Creatissimo\MattermostBundle\Services;

use Creatissimo\MattermostBundle\Entity\Attachment;
use Creatissimo\MattermostBundle\Entity\AttachmentField;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AttachmentHelper
 * @package Creatissimo\MattermostBundle\Services
 */
class AttachmentHelper
{
    /** @var ConsoleHelper */
    private $consoleHelper;

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
    public function convertRequestToAttachment(Request $request): Attachment
    {
        $attachment = new Attachment('Request information');
        $headers    = $request->headers;

        $attachment->addField(new AttachmentField('Host', $headers->get('host'), true));
        $attachment->addField(new AttachmentField('URI', $request->getRequestUri(), true));
        $attachment->addField(new AttachmentField('Method', $request->getMethod(), true));
        $attachment->addField(new AttachmentField('IP', $request->getClientIp(), true));
        if ($user = $request->getUser()) {
            $attachment->addField(new AttachmentField('User', $request->getUser(), true));
            $attachment->addField(new AttachmentField('User info', $request->getUserInfo(), true));
        }
        $referer = $headers->get('referer');
        if (!empty($referer)) {
            $attachment->addField(new AttachmentField('Referer', $referer));
        }
        $userAgent = $headers->get('user-agent');
        if (!empty($userAgent)) {
            $attachment->addField(new AttachmentField('User-Agent', $userAgent));
        }
        $data = $request->request->all();
        foreach ($data as $key => $value) {
            $attachment->addField(new AttachmentField($key, $value, true));
        }
        $attachment->addField(new AttachmentField('Request', $request->__toString()));

        return $attachment;
    }


    /**
     * @param Command        $command
     * @param InputInterface $input
     *
     * @return Attachment
     */
    public function convertCommandToAttachment(Command $command, InputInterface $input): Attachment
    {
        $attachment = new Attachment('Command information');

        $attachment->addField(new AttachmentField('Command', $command->getName()));

        $inputStr = $input->__toString();
        if (!empty($inputStr)) {
            $attachment->addField(new AttachmentField('Input', $inputStr));
        }

        if ($argumentString = $this->consoleHelper->argumentsToString($input)) {
            $attachment->addField(new AttachmentField('Arguments', $argumentString));
        }

        if ($optionString = $this->consoleHelper->optionsToString($input)) {
            $attachment->addField(new AttachmentField('Options', $optionString));
        }

        return $attachment;
    }
}