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

    private function getStoreName(\Fgms\ShopifyEmbed\Entity\ShopSettings $settings)
    {
        return preg_replace('/\\.myshopify\\.com$/u','',$settings->getStoreName());
    }

    private function getShopify(\Fgms\ShopifyEmbed\Entity\ShopSettings $settings)
    {
        $name = $this->getStoreName($settings);
        $shopify = new \Fgms\SpecialOffersBundle\Utility\ShopifyClient('','',$name);
        $token = $settings->getAccessToken();
        $shopify->setToken($token);
        return $shopify;
    }

    private function getSpecialOfferStrategy(\Fgms\ShopifyEmbed\Entity\ShopSettings $settings)
    {
        return new \Fgms\SpecialOffersBundle\Strategy\SpecialOfferStrategy($this->getShopify($settings));
    }

    private function start(\Symfony\Component\Console\Output\OutputInterface $output, \DateTime $now, \Fgms\ShopifyEmbed\Entity\ShopSettings $settings)
    {
        $repo = $this->getSpecialOfferRepository();
        $offers = $repo->getStarting($now,$this->getStoreName($settings));
        $strategy = $this->getSpecialOfferStrategy($settings);
        $em = $this->getEntityManager();
        foreach ($offers as $o) {
            $changes = $strategy->apply($o);
            $o->setStatus('active')
                ->setApplied($now);
            foreach ($changes as $c) $em->persist($c);
            $em->persist($o);
            $em->flush();
        }
        return count($offers);
    }

    private function end(\Symfony\Component\Console\Output\OutputInterface $output, \DateTime $now, \Fgms\ShopifyEmbed\Entity\ShopSettings $settings)
    {
        $repo = $this->getSpecialOfferRepository();
        $offers = $repo->getEnding($now,$this->getStoreName($settings));
        $strategy = $this->getSpecialOfferStrategy($settings);
        $em = $this->getEntityManager();
        foreach ($offers as $o) {
            $changes = $strategy->revert($o);
            $o->setStatus('expired')
                ->setReverted($now);
            foreach ($changes as $c) $em->persist($c);
            $em->persist($o);
            $em->flush();
        }
        return count($offers);
    }

    private function getSiteSettings()
    {
        $doctrine = $this->getDoctrine();
        $repo = $doctrine->getRepository(\Fgms\ShopifyEmbed\Entity\ShopSettings::class);
        return $repo->findBy([
            'nameSpace' => 'FgmsSpecialOffersBundle',
            'status' => 'active'
        ]);
    }

    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $tz = new \DateTimeZone(date_default_timezone_get());
        foreach ($this->getSiteSettings() as $settings) {
            $now = new \DateTime();
            $now->setTimezone($tz);
            $output->writeln(
                sprintf(
                    'Processing "%s" at %s',
                    $this->getStoreName($settings),
                    $now->format(\DateTime::ATOM)
                )
            );
            $started = $this->start($output,$now,$settings);
            $output->writeln(sprintf('Started %d',$started));
            $ended = $this->end($output,$now,$settings);
            $output->writeln(sprintf('Ended %d',$ended));
        }
    }
}
