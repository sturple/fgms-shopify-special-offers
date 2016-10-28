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

    private function getDoctrine()
    {
        return $this->getContainer()->get('doctrine');
    }

    private function getSpecialOfferRepository()
    {
        return $this->getDoctrine()->getRepository(\Fgms\SpecialOffersBundle\Entity\SpecialOffer::class);
    }

    private function getEntityManager()
    {
        return $this->getDoctrine()->getEntityManager();
    }

    private function getShopify()
    {
        $doctrine = $this->getDoctrine();
        $repo = $doctrine->getRepository(\Fgms\ShopifyEmbed\Entity\ShopSettings::class);
        $settings = $repo->findOneBy(['nameSpace' => 'FgmsSpecialOffersBundle','status' => 'active']);
        if (is_null($settings)) throw new \LogicException('No StoreSettings entity');
        $name = preg_replace('/\\.myshopify\\.com$/u','',$settings->getStoreName());
        $shopify = new \Fgms\SpecialOffersBundle\Utility\ShopifyClient('','',$name);
        $token = $settings->getAccessToken();
        $shopify->setToken($token);
        return $shopify;
    }

    private function getSpecialOfferStrategy()
    {
        return new \Fgms\SpecialOffersBundle\Strategy\SpecialOfferStrategy($this->getShopify());
    }

    private function start(\Symfony\Component\Console\Output\OutputInterface $output, \DateTime $now)
    {
        $repo = $this->getSpecialOfferRepository();
        $offers = $repo->getStarting($now);
        $strategy = $this->getSpecialOfferStrategy();
        $em = $this->getEntityManager();
        foreach ($offers as $o) {
            $changes = $strategy->apply($o);
            $o->setStatus('active');
            foreach ($changes as $c) $em->persist($c);
            $em->persist($o);
            $em->flush();
        }
        return count($offers);
    }

    private function end(\Symfony\Component\Console\Output\OutputInterface $output, \DateTime $now)
    {
        $repo = $this->getSpecialOfferRepository();
        $offers = $repo->getEnding($now);
        $strategy = $this->getSpecialOfferStrategy();
        $em = $this->getEntityManager();
        foreach ($offers as $o) {
            $changes = $strategy->revert($o);
            $o->setStatus('expired');
            foreach ($changes as $c) $em->persist($c);
            $em->persist($o);
            $em->flush();
        }
        return count($offers);
    }

    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $now = new \DateTime();
        $tz = new \DateTimeZone(date_default_timezone_get());
        $now->setTimezone($tz);
        $output->writeln(sprintf('Current time is %s',$now->format(\DateTime::ATOM)));
        $started = $this->start($output,$now);
        $ended = $this->end($output,$now);
        $output->writeln(sprintf('Started %d, Ended %d',$started,$ended));
    }
}
