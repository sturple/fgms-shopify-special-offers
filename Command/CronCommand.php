<?php

namespace Fgms\SpecialOffersBundle\Command;

class CronCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('specialoffers:cron');
        $this->setDescription('Performs cron jobs for the FgmsSpecialOffersBundle');
        $this->setHelp('Performs all outstanding tasks for the FgmsSpecialOffersBundle');
    }

    private function getSpecialOfferRepository()
    {
        $doctrine = $this->getContainer()->get('doctrine');
        return $doctrine->getRepository(\Fgms\SpecialOffersBundle\Entity\SpecialOffer::class);
    }

    private function start(\Symfony\Component\Console\Output\OutputInterface $output, \DateTime $now)
    {
        $repo = $this->getSpecialOfferRepository();
        $offers = $repo->getStarting($now);
        return count($offers);
    }

    private function end(\Symfony\Component\Console\Output\OutputInterface $output, \DateTime $now)
    {
        $repo = $this->getSpecialOfferRepository();
        $offers = $repo->getEnding($now);
        return count($offers);
    }

    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $repo = $doctrine->getRepository(\Fgms\SpecialOffersBundle\Entity\Run::class);
        $now = new \DateTime();
        $tz = new \DateTimeZone(date_default_timezone_get());
        $now->setTimezone($tz);
        $output->writeln(sprintf('Current time is %s',$now->format(\DateTime::ATOM)));
        $started = $this->start($output,$now);
        $ended = $this->end($output,$now);
        $output->writeln(sprintf('Started %d, Ended %d',$started,$ended));
    }
}
