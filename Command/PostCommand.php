<?php
/**
 * User: prossa
 * Date: 16/10/16
 * Time: 22:12
 */

namespace Crea\MattermostBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Crea\MattermostBundle\Services\MattermostHelper;

class PostCommand extends ContainerAwareCommand
{
    use CommandTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mattermost:post')
            ->setDescription('Send a post to mattermost')
            ->addArgument('text', InputArgument::REQUIRED, 'Text to post')
            ->addArgument('channel', InputArgument::OPTIONAL, 'Channelname to post to; Default = Setting in Mattermost')
            ->addArgument('username', InputArgument::OPTIONAL, 'Username of user that sends post; Default = Bot');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mmHelper = $this->getContainer()->get('mattermost.helper');

        $text = $input->getArgument('text');
        $mmHelper->setText($text);

        $channel = $input->getArgument('channel');
        if($channel) $mmHelper->setChannel($channel);

        $username = $input->getArgument('username');
        if($username) $mmHelper->setUsername($username);

        $mmHelper->sendMessage();
    }
}