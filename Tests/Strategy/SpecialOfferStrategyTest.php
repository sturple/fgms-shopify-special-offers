<?php

namespace Fgms\SpecialOffersBundle\Tests\Strategy;

class SpecialOfferStrategyTest extends \PHPUnit_Framework_TestCase
{
	private $strategy;
	private $shopify;
	private $offer;

	protected function setUp()
	{
		$this->shopify = new \Fgms\SpecialOffersBundle\Utility\MockShopifyClient();
		$this->strategy = new \Fgms\SpecialOffersBundle\Strategy\SpecialOfferStrategy($this->shopify);
		$this->offer = new \Fgms\SpecialOffersBundle\Entity\SpecialOffer();
		//	Just to avoid insanity
		$this->offer->setDiscountCents(1);
	}

	private function apply($expected = 0)
	{
		$retr = $this->strategy->apply($this->offer);
		$this->checkPriceChanges($expected,$retr,true);
		return $retr;
	}

	private function revert($expected = 0)
	{
		$retr = $this->strategy->revert($this->offer);
		$this->checkPriceChanges($expected,$retr,false);
		return $retr;
	}
	
	private function checkPriceChanges($expected, array $arr, $apply)
	{
		$this->assertCount($expected,$arr);
		foreach ($arr as $change) {
			$this->assertInstanceOf(\Fgms\SpecialOffersBundle\Entity\PriceChange::class,$change);
			$this->assertSame($this->offer,$change->getSpecialOffer());
			$this->assertSame($apply ? 'apply' : 'revert',$change->getType());
		}
	}

	public function testApplyEmpty()
	{
		$this->apply(0);
	}

	public function testApplyDiscountBoth()
	{
		//	Should throw because both percent and cents 
		$this->offer->setDiscountPercent(1);
		$this->expectException(\LogicException::class);
		$this->apply();
	}

	public function testApplyDiscountNeither()
	{
		//	Should throw because no discount
		$this->offer->setDiscountCents(null);
		$this->expectException(\LogicException::class);
		$this->apply();
	}

	public function testApplyCents()
	{
		$this->shopify->addResponse([
			'variant' => [
				'id' => 4,
				'compare_at_price' => null,
				'price' => '8.00'
			]
		]);
		//	For when it actually tries to change the price
		$this->shopify->addResponse(new \stdClass());
		$this->offer->setVariantIds([4]);
		$arr = $this->apply(1);
		$change = $arr[0];
		$this->assertSame(4,$change->getVariantId());
		$this->assertSame(800,$change->getBeforeCents());
		$this->assertSame(799,$change->getAfterCents());
		$res = $this->shopify->getRequests();
		$this->assertCount(2,$res);
		$r = $res[0];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/variants/4.json',$r->endpoint);
		$this->assertCount(0,$r->args);
		$r = $res[1];
		$this->assertSame('PUT',$r->method);
		$this->assertSame('/admin/variants/4.json',$r->endpoint);
		$this->assertArrayHasKey('variant',$r->args);
		$v = $r->args['variant'];
		$this->assertArrayHasKey('id',$v);
		$this->assertSame(4,$v['id']);
		$this->assertArrayHasKey('compare_at_price',$v);
		$this->assertSame('8.00',$v['compare_at_price']);
		$this->assertArrayHasKey('price',$v);
		$this->assertSame('7.99',$v['price']);
	}

	public function testApplyZero()
	{
		$this->shopify->addResponse([
			'variant' => [
				'id' => 4,
				'compare_at_price' => null,
				'price' => '0.01'
			]
		]);
		//	For when it actually tries to change the price
		$this->shopify->addResponse(new \stdClass());
		$this->offer->setVariantIds([4]);
		$arr = $this->apply(1);
		$change = $arr[0];
		$this->assertSame(4,$change->getVariantId());
		$this->assertSame(1,$change->getBeforeCents());
		$this->assertSame(0,$change->getAfterCents());
		$res = $this->shopify->getRequests();
		$this->assertCount(2,$res);
		$r = $res[0];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/variants/4.json',$r->endpoint);
		$this->assertCount(0,$r->args);
		$r = $res[1];
		$this->assertSame('PUT',$r->method);
		$this->assertSame('/admin/variants/4.json',$r->endpoint);
		$this->assertArrayHasKey('variant',$r->args);
		$v = $r->args['variant'];
		$this->assertArrayHasKey('id',$v);
		$this->assertSame(4,$v['id']);
		$this->assertArrayHasKey('compare_at_price',$v);
		$this->assertSame('0.01',$v['compare_at_price']);
		$this->assertArrayHasKey('price',$v);
		$this->assertSame('0.00',$v['price']);
	}

	public function testApplyAlreadyOnSpecialOffer()
	{
		$this->shopify->addResponse([
			'variant' => [
				'id' => 4,
				'compare_at_price' => '5.00',
				'price' => '0.00'
			]
		]);
		$this->offer->setVariantIds([4]);
		$this->expectException(\Fgms\SpecialOffersBundle\Exception\AlreadyOnSpecialOfferException::class);
		$this->apply();
	}

	public function testApplyNegative()
	{
		$this->shopify->addResponse([
			'variant' => [
				'id' => 4,
				'compare_at_price' => null,
				'price' => '0.00'
			]
		]);
		$this->offer->setVariantIds([4]);
		$this->expectException(\LogicException::class);
		$this->apply();
	}

	public function testRevertEmpty()
	{
		$this->revert(0);
	}

	public function testRevert()
	{
		$this->shopify->addResponse([
			'variant' => [
				'id' => 4,
				'compare_at_price' => '85.00',
				'price' => '60.00'
			]
		]);
		//	For when it actually tries to change the price
		$this->shopify->addResponse(new \stdClass());
		$this->offer->setVariantIds([4]);
		$arr = $this->revert(1);
		$change = $arr[0];
		$this->assertSame(4,$change->getVariantId());
		$this->assertSame(6000,$change->getBeforeCents());
		$this->assertSame(8500,$change->getAfterCents());
		$res = $this->shopify->getRequests();
		$this->assertCount(2,$res);
		$r = $res[0];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/variants/4.json',$r->endpoint);
		$this->assertCount(0,$r->args);
		$r = $res[1];
		$this->assertSame('PUT',$r->method);
		$this->assertSame('/admin/variants/4.json',$r->endpoint);
		$this->assertArrayHasKey('variant',$r->args);
		$v = $r->args['variant'];
		$this->assertArrayHasKey('id',$v);
		$this->assertSame(4,$v['id']);
		$this->assertArrayHasKey('compare_at_price',$v);
		$this->assertSame(null,$v['compare_at_price']);
		$this->assertArrayHasKey('price',$v);
		$this->assertSame('85.00',$v['price']);
	}

	public function testRevertNotOnSpecialOffer()
	{
		$this->shopify->addResponse([
			'variant' => [
				'id' => 4,
				'compare_at_price' => null,
				'price' => '1.00'
			]
		]);
		$this->offer->setVariantIds([4]);
		$this->expectException(\Fgms\SpecialOffersBundle\Exception\NotOnSpecialOfferException::class);
		$this->revert();
	}
}