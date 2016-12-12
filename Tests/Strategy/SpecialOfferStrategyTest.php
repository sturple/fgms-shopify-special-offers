<?php

namespace Fgms\SpecialOffersBundle\Tests\Strategy;

class SpecialOfferStrategyTest extends \PHPUnit_Framework_TestCase
{
	private $strategy;
	private $shopify;
	private $offer;

	protected function setUp()
	{
		$this->shopify = new \Fgms\SpecialOffersBundle\Shopify\MockClient();
		$this->strategy = new \Fgms\SpecialOffersBundle\Strategy\SpecialOfferStrategy($this->shopify);
		$this->offer = new \Fgms\SpecialOffersBundle\Entity\SpecialOffer();
		//	Just to avoid insanity
		$this->offer->setDiscountCents(1)
			->setStart(\DateTime::createFromFormat('U','1480550400'))
			->setEnd(\DateTime::createFromFormat('U','1480636800'))
			->setTitle('Test');
		$reflection = new \ReflectionClass($this->offer);
		$prop = $reflection->getProperty('id');
		$prop->setAccessible(true);
		$prop->setValue($this->offer,17);
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
				'product_id' => 1,
				'compare_at_price' => null,
				'price' => '8.00'
			]
		]);
		$this->shopify->addResponse([
			'product' => [
				'id' => 1,
				'tags' => ''
			]
		]);
		//	For when it actually tries to change the price
		$this->shopify->addResponse(new \stdClass());
		$this->shopify->addResponse(new \stdClass());
		$this->offer->setVariantIds([4]);
		$arr = $this->apply(1);
		$change = $arr[0];
		$this->assertSame(4,$change->getVariantId());
		$this->assertSame(800,$change->getBeforeCents());
		$this->assertSame(799,$change->getAfterCents());
		$res = $this->shopify->getRequests();
		$this->assertCount(4,$res);
		$r = $res[0];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/variants/4',$r->endpoint);
		$this->assertCount(1,$r->args);
		$this->assertSame('id,product_id,compare_at_price,price',$r->args['fields']);
		$r = $res[1];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/products/1',$r->endpoint);
		$this->assertCount(1,$r->args);
		$this->assertSame('id,tags',$r->args['fields']);
		$r = $res[2];
		$this->assertSame('PUT',$r->method);
		$this->assertSame('/admin/variants/4',$r->endpoint);
		$this->assertArrayHasKey('variant',$r->args);
		$v = $r->args['variant'];
		$this->assertArrayHasKey('id',$v);
		$this->assertSame(4,$v['id']);
		$this->assertArrayHasKey('compare_at_price',$v);
		$this->assertSame('8.00',$v['compare_at_price']);
		$this->assertArrayHasKey('price',$v);
		$this->assertSame('7.99',$v['price']);
		$this->assertArrayHasKey('metafields',$v);
		$mfs = $v['metafields'];
		$this->assertTrue(is_array($mfs));
		$this->assertCount(5,$mfs);
		$mf = $mfs[0];
		$this->assertTrue(is_array($mf));
		$this->assertCount(4,$mf);
		$this->assertArrayHasKey('key',$mf);
		$this->assertSame('title',$mf['key']);
		$this->assertArrayHasKey('namespace',$mf);
		$this->assertSame('fgms_special_offers',$mf['namespace']);
		$this->assertArrayHasKey('value_type',$mf);
		$this->assertSame('string',$mf['value_type']);
		$this->assertArrayHasKey('value',$mf);
		$this->assertSame('Test',$mf['value']);
		$mf = $mfs[1];
		$this->assertTrue(is_array($mf));
		$this->assertCount(4,$mf);
		$this->assertArrayHasKey('key',$mf);
		$this->assertSame('subtitle',$mf['key']);
		$this->assertArrayHasKey('namespace',$mf);
		$this->assertSame('fgms_special_offers',$mf['namespace']);
		$this->assertArrayHasKey('value_type',$mf);
		$this->assertSame('string',$mf['value_type']);
		$this->assertArrayHasKey('value',$mf);
		$this->assertSame('',$mf['value']);
		$mf = $mfs[2];
		$this->assertTrue(is_array($mf));
		$this->assertCount(4,$mf);
		$this->assertArrayHasKey('key',$mf);
		$this->assertSame('summary',$mf['key']);
		$this->assertArrayHasKey('namespace',$mf);
		$this->assertSame('fgms_special_offers',$mf['namespace']);
		$this->assertArrayHasKey('value_type',$mf);
		$this->assertSame('string',$mf['value_type']);
		$this->assertArrayHasKey('value',$mf);
		$this->assertSame('',$mf['value']);
		$mf = $mfs[3];
		$this->assertTrue(is_array($mf));
		$this->assertCount(4,$mf);
		$this->assertArrayHasKey('key',$mf);
		$this->assertSame('start',$mf['key']);
		$this->assertArrayHasKey('namespace',$mf);
		$this->assertSame('fgms_special_offers',$mf['namespace']);
		$this->assertArrayHasKey('value_type',$mf);
		$this->assertSame('integer',$mf['value_type']);
		$this->assertArrayHasKey('value',$mf);
		$this->assertSame(1480550400 * 1000,$mf['value']);
		$mf = $mfs[4];
		$this->assertTrue(is_array($mf));
		$this->assertCount(4,$mf);
		$this->assertArrayHasKey('key',$mf);
		$this->assertSame('end',$mf['key']);
		$this->assertArrayHasKey('namespace',$mf);
		$this->assertSame('fgms_special_offers',$mf['namespace']);
		$this->assertArrayHasKey('value_type',$mf);
		$this->assertSame('integer',$mf['value_type']);
		$this->assertArrayHasKey('value',$mf);
		$this->assertSame(1480636800 * 1000,$mf['value']);
		$r = $res[3];
		$this->assertSame('PUT',$r->method);
		$this->assertSame('/admin/products/1',$r->endpoint);
		$this->assertArrayHasKey('product',$r->args);
		$p = $r->args['product'];
		$this->assertArrayHasKey('id',$p);
		$this->assertSame(1,$p['id']);
		$this->assertArrayHasKey('tags',$p);
		$this->assertSame('',$p['tags']);
	}

	public function testApplyPercent()
	{
		$this->offer->setDiscountCents(null);
		$this->offer->setDiscountPercent(50);
		$this->shopify->addResponse([
			'variant' => [
				'id' => 4,
				'product_id' => 1,
				'compare_at_price' => null,
				'price' => '8.00'
			]
		]);
		$this->shopify->addResponse([
			'product' => [
				'id' => 1,
				'tags' => ''
			]
		]);
		//	For when it actually tries to change the price
		$this->shopify->addResponse(new \stdClass());
		$this->shopify->addResponse(new \stdClass());
		$this->offer->setVariantIds([4]);
		$arr = $this->apply(1);
		$change = $arr[0];
		$this->assertSame(4,$change->getVariantId());
		$this->assertSame(800,$change->getBeforeCents());
		$this->assertSame(400,$change->getAfterCents());
		$res = $this->shopify->getRequests();
		$this->assertCount(4,$res);
		$r = $res[0];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/variants/4',$r->endpoint);
		$this->assertCount(1,$r->args);
		$this->assertSame('id,product_id,compare_at_price,price',$r->args['fields']);
		$r = $res[1];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/products/1',$r->endpoint);
		$this->assertCount(1,$r->args);
		$this->assertSame('id,tags',$r->args['fields']);
		$r = $res[2];
		$this->assertSame('PUT',$r->method);
		$this->assertSame('/admin/variants/4',$r->endpoint);
		$this->assertArrayHasKey('variant',$r->args);
		$v = $r->args['variant'];
		$this->assertArrayHasKey('id',$v);
		$this->assertSame(4,$v['id']);
		$this->assertArrayHasKey('compare_at_price',$v);
		$this->assertSame('8.00',$v['compare_at_price']);
		$this->assertArrayHasKey('price',$v);
		$this->assertSame('4.00',$v['price']);
		$r = $res[3];
		$this->assertSame('PUT',$r->method);
		$this->assertSame('/admin/products/1',$r->endpoint);
		$this->assertArrayHasKey('product',$r->args);
		$p = $r->args['product'];
		$this->assertArrayHasKey('id',$p);
		$this->assertSame(1,$p['id']);
		$this->assertArrayHasKey('tags',$p);
		$this->assertSame('',$p['tags']);
	}

	public function testApplyZero()
	{
		$this->shopify->addResponse([
			'variant' => [
				'id' => 4,
				'product_id' => 1,
				'compare_at_price' => null,
				'price' => '0.01'
			]
		]);
		$this->shopify->addResponse([
			'product' => [
				'id' => 1,
				'tags' => 'foo'
			]
		]);
		//	For when it actually tries to change the price
		$this->shopify->addResponse(new \stdClass());
		$this->shopify->addResponse(new \stdClass());
		$this->offer->setVariantIds([4]);
		$arr = $this->apply(1);
		$change = $arr[0];
		$this->assertSame(4,$change->getVariantId());
		$this->assertSame(1,$change->getBeforeCents());
		$this->assertSame(0,$change->getAfterCents());
		$res = $this->shopify->getRequests();
		$this->assertCount(4,$res);
		$r = $res[0];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/variants/4',$r->endpoint);
		$this->assertCount(1,$r->args);
		$this->assertSame('id,product_id,compare_at_price,price',$r->args['fields']);
		$r = $res[1];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/products/1',$r->endpoint);
		$this->assertCount(1,$r->args);
		$this->assertSame('id,tags',$r->args['fields']);
		$r = $res[2];
		$this->assertSame('PUT',$r->method);
		$this->assertSame('/admin/variants/4',$r->endpoint);
		$this->assertArrayHasKey('variant',$r->args);
		$v = $r->args['variant'];
		$this->assertArrayHasKey('id',$v);
		$this->assertSame(4,$v['id']);
		$this->assertArrayHasKey('compare_at_price',$v);
		$this->assertSame('0.01',$v['compare_at_price']);
		$this->assertArrayHasKey('price',$v);
		$this->assertSame('0.00',$v['price']);
		$r = $res[3];
		$this->assertSame('PUT',$r->method);
		$this->assertSame('/admin/products/1',$r->endpoint);
		$this->assertArrayHasKey('product',$r->args);
		$p = $r->args['product'];
		$this->assertArrayHasKey('id',$p);
		$this->assertSame(1,$p['id']);
		$this->assertArrayHasKey('tags',$p);
		$this->assertSame('foo',$p['tags']);
	}

	public function testApplyAlreadyOnSpecialOffer()
	{
		$this->shopify->addResponse([
			'variant' => [
				'id' => 4,
				'product_id' => 1,
				'compare_at_price' => '5.00',
				'price' => '0.00'
			]
		]);
		$this->shopify->addResponse([
			'product' => [
				'id' => 1,
				'tags' => ''
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
				'product_id' => 1,
				'compare_at_price' => null,
				'price' => '0.00'
			]
		]);
		$this->shopify->addResponse([
			'product' => [
				'id' => 1,
				'tags' => ''
			]
		]);
		$this->offer->setVariantIds([4]);
		$this->expectException(\LogicException::class);
		$this->apply();
	}

	public function testApplyAddTags()
	{
		$this->shopify->addResponse([
			'variant' => [
				'id' => 4,
				'product_id' => 1,
				'compare_at_price' => null,
				'price' => '0.01'
			]
		]);
		$this->shopify->addResponse([
			'product' => [
				'id' => 1,
				'tags' => 'foo'
			]
		]);
		//	For when it actually tries to change the price
		$this->shopify->addResponse(new \stdClass());
		$this->shopify->addResponse(new \stdClass());
		$this->offer->setVariantIds([4]);
		$this->offer->setTags(['bar']);
		$arr = $this->apply(1);
		$change = $arr[0];
		$this->assertSame(4,$change->getVariantId());
		$this->assertSame(1,$change->getBeforeCents());
		$this->assertSame(0,$change->getAfterCents());
		$res = $this->shopify->getRequests();
		$this->assertCount(4,$res);
		$r = $res[0];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/variants/4',$r->endpoint);
		$this->assertCount(1,$r->args);
		$this->assertSame('id,product_id,compare_at_price,price',$r->args['fields']);
		$r = $res[1];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/products/1',$r->endpoint);
		$this->assertCount(1,$r->args);
		$this->assertSame('id,tags',$r->args['fields']);
		$r = $res[2];
		$this->assertSame('PUT',$r->method);
		$this->assertSame('/admin/variants/4',$r->endpoint);
		$this->assertArrayHasKey('variant',$r->args);
		$v = $r->args['variant'];
		$this->assertArrayHasKey('id',$v);
		$this->assertSame(4,$v['id']);
		$this->assertArrayHasKey('compare_at_price',$v);
		$this->assertSame('0.01',$v['compare_at_price']);
		$this->assertArrayHasKey('price',$v);
		$this->assertSame('0.00',$v['price']);
		$r = $res[3];
		$this->assertSame('PUT',$r->method);
		$this->assertSame('/admin/products/1',$r->endpoint);
		$this->assertArrayHasKey('product',$r->args);
		$p = $r->args['product'];
		$this->assertArrayHasKey('id',$p);
		$this->assertSame(1,$p['id']);
		$this->assertArrayHasKey('tags',$p);
		$this->assertThat(
			$p['tags'],
			$this->logicalOr(
				$this->identicalTo('foo, bar'),
				$this->identicalTo('bar, foo')
			)
		);
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
				'product_id' => 1,
				'compare_at_price' => '85.00',
				'price' => '60.00'
			]
		]);
		$this->shopify->addResponse([
			'product' => [
				'id' => 1,
				'tags' => ''
			]
		]);
		//	For when it actually tries to change the price
		$this->shopify->addResponse(new \stdClass());
		$this->shopify->addResponse(new \stdClass());
		//	For metafield
		$this->shopify->addResponse((object)[
			'metafields' => [
				(object)[
					'id' => 10
				]
			]
		]);
		$this->shopify->addResponse(new \stdClass());
		$this->offer->setVariantIds([4]);
		$arr = $this->revert(1);
		$change = $arr[0];
		$this->assertSame(4,$change->getVariantId());
		$this->assertSame(6000,$change->getBeforeCents());
		$this->assertSame(8500,$change->getAfterCents());
		$this->assertEmpty($change->getAfterTags());
		$res = $this->shopify->getRequests();
		$this->assertCount(6,$res);
		$r = $res[0];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/variants/4',$r->endpoint);
		$this->assertCount(1,$r->args);
		$this->assertSame('id,product_id,compare_at_price,price',$r->args['fields']);
		$r = $res[1];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/products/1',$r->endpoint);
		$this->assertCount(1,$r->args);
		$this->assertSame('id,tags',$r->args['fields']);
		$r = $res[2];
		$this->assertSame('PUT',$r->method);
		$this->assertSame('/admin/variants/4',$r->endpoint);
		$this->assertArrayHasKey('variant',$r->args);
		$v = $r->args['variant'];
		$this->assertArrayHasKey('id',$v);
		$this->assertSame(4,$v['id']);
		$this->assertArrayHasKey('compare_at_price',$v);
		$this->assertSame(null,$v['compare_at_price']);
		$this->assertArrayHasKey('price',$v);
		$this->assertSame('85.00',$v['price']);
		$r = $res[3];
		$this->assertSame('PUT',$r->method);
		$this->assertSame('/admin/products/1',$r->endpoint);
		$this->assertArrayHasKey('product',$r->args);
		$p = $r->args['product'];
		$this->assertArrayHasKey('id',$p);
		$this->assertSame(1,$p['id']);
		$this->assertArrayHasKey('tags',$p);
		$this->assertSame('',$p['tags']);
		$r = $res[4];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/variants/4/metafields',$r->endpoint);
		$this->assertCount(2,$r->args);
		$this->assertArrayHasKey('fields',$r->args);
		$this->assertSame('id',$r->args['fields']);
		$this->assertArrayHasKey('namespace',$r->args);
		$this->assertSame('fgms_special_offers',$r->args['namespace']);
		$r = $res[5];
		$this->assertSame('DELETE',$r->method);
		$this->assertSame('/admin/variants/4/metafields/10',$r->endpoint);
		$this->assertCount(0,$r->args);
	}

	public function testRevertNotOnSpecialOffer()
	{
		$this->shopify->addResponse([
			'variant' => [
				'id' => 4,
				'product_id' => 1,
				'compare_at_price' => null,
				'price' => '1.00'
			]
		]);
		$this->shopify->addResponse([
			'product' => [
				'id' => 1,
				'tags' => ''
			]
		]);
		$this->offer->setVariantIds([4]);
		$this->expectException(\Fgms\SpecialOffersBundle\Exception\NotOnSpecialOfferException::class);
		$this->revert();
	}

	public function testRevertRemoveTags()
	{
		$this->shopify->addResponse([
			'variant' => [
				'id' => 4,
				'product_id' => 1,
				'compare_at_price' => '85.00',
				'price' => '60.00'
			]
		]);
		$this->shopify->addResponse([
			'product' => [
				'id' => 1,
				'tags' => 'bar, foo'
			]
		]);
		//	For when it actually tries to change the price
		$this->shopify->addResponse(new \stdClass());
		$this->shopify->addResponse(new \stdClass());
		//	For metafields
		$this->shopify->addResponse((object)[
			'metafields' => [
				(object)[
					'id' => 5
				]
			]
		]);
		$this->shopify->addResponse(new \stdClass());
		$this->offer->setVariantIds([4]);
		$this->offer->setTags(['foo']);
		$arr = $this->revert(1);
		$change = $arr[0];
		$this->assertSame(4,$change->getVariantId());
		$this->assertSame(6000,$change->getBeforeCents());
		$this->assertSame(8500,$change->getAfterCents());
		$res = $this->shopify->getRequests();
		$this->assertCount(6,$res);
		$r = $res[0];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/variants/4',$r->endpoint);
		$this->assertCount(1,$r->args);
		$this->assertSame('id,product_id,compare_at_price,price',$r->args['fields']);
		$r = $res[1];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/products/1',$r->endpoint);
		$this->assertCount(1,$r->args);
		$this->assertSame('id,tags',$r->args['fields']);
		$r = $res[2];
		$this->assertSame('PUT',$r->method);
		$this->assertSame('/admin/variants/4',$r->endpoint);
		$this->assertArrayHasKey('variant',$r->args);
		$v = $r->args['variant'];
		$this->assertArrayHasKey('id',$v);
		$this->assertSame(4,$v['id']);
		$this->assertArrayHasKey('compare_at_price',$v);
		$this->assertSame(null,$v['compare_at_price']);
		$this->assertArrayHasKey('price',$v);
		$this->assertSame('85.00',$v['price']);
		$r = $res[3];
		$this->assertSame('PUT',$r->method);
		$this->assertSame('/admin/products/1',$r->endpoint);
		$this->assertArrayHasKey('product',$r->args);
		$p = $r->args['product'];
		$this->assertArrayHasKey('id',$p);
		$this->assertSame(1,$p['id']);
		$this->assertArrayHasKey('tags',$p);
		$this->assertSame('bar',$p['tags']);
		$r = $res[4];
		$this->assertSame('GET',$r->method);
		$this->assertSame('/admin/variants/4/metafields',$r->endpoint);
		$this->assertCount(2,$r->args);
		$this->assertArrayHasKey('fields',$r->args);
		$this->assertSame('id',$r->args['fields']);
		$this->assertArrayHasKey('namespace',$r->args);
		$this->assertSame('fgms_special_offers',$r->args['namespace']);
		$r = $res[5];
		$this->assertSame('DELETE',$r->method);
		$this->assertSame('/admin/variants/4/metafields/5',$r->endpoint);
		$this->assertCount(0,$r->args);
	}
}
