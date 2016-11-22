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

    private function getStoreRepository()
    {
        return $this->getDoctrine()->getRepository(\Fgms\SpecialOffersBundle\Entity\Store::class);
    }

    private function getEntityManager()
    {
        return $this->getDoctrine()->getEntityManager();
    }

    private function getEventDispatcher()
    {
        return $this->getContainer()->get('event_dispatcher');
    }

    private function getShopify(\Fgms\SpecialOffersBundle\Entity\Store $store)
    {
        $config = $this->getContainer()->getParameter('fgms_special_offers.config');
        $shopify = new \Fgms\SpecialOffersBundle\Shopify\Client($config['api_key'],$config['secret'],$store->getName());
        $shopify->setToken($store->getAccessToken());
        return $shopify;
    }

    private function getSpecialOfferStrategy(\Fgms\SpecialOffersBundle\Entity\Store $store)
    {
        return new \Fgms\SpecialOffersBundle\Strategy\SpecialOfferStrategy($this->getShopify($store));
    }

    private function start(\Symfony\Component\Console\Output\OutputInterface $output, \DateTime $now, \Fgms\SpecialOffersBundle\Entity\Store $store)
    {
        $repo = $this->getSpecialOfferRepository();
        $offers = $repo->getStarting($now,$store);
        $strategy = $this->getSpecialOfferStrategy($store);
        $em = $this->getEntityManager();
        $dispatcher = $this->getEventDispatcher();
        foreach ($offers as $o) {
            $changes = $strategy->apply($o);
            $o->setStatus('active')
                ->setApplied($now);
            foreach ($changes as $c) {
                $o->addPriceChange($c);
                $em->persist($c);
            }
            $em->persist($o);
            $em->flush();
            $dispatcher->dispatch(
                'specialoffers.started',
                new \Fgms\SpecialOffersBundle\Event\PriceChangeEvent($o,$changes)
            );
        }
        return count($offers);
    }

    private function end(\Symfony\Component\Console\Output\OutputInterface $output, \DateTime $now, \Fgms\SpecialOffersBundle\Entity\Store $store)
    {
        $repo = $this->getSpecialOfferRepository();
        $offers = $repo->getEnding($now,$store);
        $strategy = $this->getSpecialOfferStrategy($store);
        $em = $this->getEntityManager();
        $dispatcher = $this->getEventDispatcher();
        foreach ($offers as $o) {
            $changes = $strategy->revert($o);
            $o->setStatus('expired')
                ->setReverted($now);
            foreach ($changes as $c) {
                $o->addPriceChange($c);
                $em->persist($c);
            }
            $em->persist($o);
            $em->flush();
            $dispatcher->dispatch(
                'specialoffers.ended',
                new \Fgms\SpecialOffersBundle\Event\PriceChangeEvent($o,$changes)
            );
        }
        return count($offers);
    }

    private function getAllStores()
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
        foreach ($this->getStoreRepository()->getActive() as $store) {
            $now = new \DateTime();
            $now->setTimezone($tz);
            $output->writeln(
                sprintf(
                    'Processing "%s" at %s',
                    $store->getName(),
                    $now->format(\DateTime::ATOM)
                )
            );
            $started = $this->start($output,$now,$store);
            $output->writeln(sprintf('Started %d',$started));
            $ended = $this->end($output,$now,$store);
            $output->writeln(sprintf('Ended %d',$ended));
        }
    }
}
