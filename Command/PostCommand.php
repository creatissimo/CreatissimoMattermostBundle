<?php
/**
 * User: prossa
 * Date: 16/10/16
 * Time: 22:12
 */

namespace Creatissimo\MattermostBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Crea\MattermostBundle\Services\MattermostHelper;

class PostCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mattermost:post')
            ->setDescription('Send a post to mattermost')
            ->setDefinition(
                new InputDefinition(array(
                    new InputOption('text', 't', InputOption::VALUE_REQUIRED),
                    new InputOption('channel', 'c', InputOption::VALUE_OPTIONAL),
                    new InputOption('username', 'u', InputOption::VALUE_OPTIONAL),
                    new InputOption('icon', 'i', InputOption::VALUE_OPTIONAL)
                ))
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mmService = $this->getContainer()->get('mattermost.service');

        $text = $input->getOption('text');
        $mmService->setText($text);

        $channel = $input->getOption('channel');
        if($channel) $mmService->setChannel($channel);

        $username = $input->getOption('username');
        if($username) $mmService->setUsername($username);

        $icon = $input->getOption('icon');
        if($icon) $mmService->setIcon($icon);

        $mmService->sendMessage();
    }
}