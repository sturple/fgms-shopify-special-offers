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

    private function start(\Symfony\Component\Console\Output\OutputInterface $output, \DateTime $last = null, \DateTime $now)
    {
        $repo = $this->getSpecialOfferRepository();
        $offers = $repo->getStarting($last,$now);
        return count($offers);
    }

    private function end(\Symfony\Component\Console\Output\OutputInterface $output, \DateTime $last = null, \DateTime $now)
    {
        $repo = $this->getSpecialOfferRepository();
        $offers = $repo->getEnding($last,$now);
        return count($offers);
    }

    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $repo = $doctrine->getRepository(\Fgms\SpecialOffersBundle\Entity\Run::class);
        //  This time is used throughout the run as the current time,
        //  even if the current time changes, this will insure that
        //  no SpecialOffer entities are missed due to a change in time
        //  while this cron job is running
        $now = new \DateTime();
        $last = $repo->getLast();
        //  Write out messages (what time we're running at, when we
        //  last ran (if at all))
        $tz = new \DateTimeZone(date_default_timezone_get());
        $now->setTimezone($tz);
        $output->writeln(sprintf('Running at %s',$now->format(\DateTime::ATOM)));
        if (!is_null($last)) {
            $last = clone $last->getWhen();
            $last->setTimezone($tz);
            $output->writeln(sprintf('Last ran at %s',$last->format(\DateTime::ATOM)));
        } else {
            $output->writeln('This is the first run');
        }
        //  Actually start and end special offers
        $run = new \Fgms\SpecialOffersBundle\Entity\Run();
        $run->setWhen($now);
        $started = $this->start($output,$last,$now);
        $ended = $this->end($output,$last,$now);
        $run->setStarted($started);
        $run->setEnded($ended);
        $output->writeln(sprintf('Started %d, Ended %d',$started,$ended));
        //  Write log so that the work we did isn't done
        //  over again
        $em = $doctrine->getManager();
        $em->persist($run);
        $em->flush();
    }
}
