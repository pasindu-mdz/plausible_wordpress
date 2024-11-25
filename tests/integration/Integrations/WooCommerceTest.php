<?php
/**
 * @package Plausible Analytics Integration Tests - Integrations > WooCommerce
 */

namespace Plausible\Analytics\Tests\Integration;

use AllowDynamicProperties;
use Plausible\Analytics\Tests\TestCase;
use Plausible\Analytics\WP\Integrations\WooCommerce;
use function Brain\Monkey\Functions\when;

#[AllowDynamicProperties]
class WooCommerceTest extends TestCase {
	/**
	 * @see WooCommerce::track_entered_checkout()
	 * @return void
	 */
	public function testTrackEnteredCheckout() {
		when( 'is_checkout' )->justReturn( true );

		$cart_mock = $this->getMockBuilder( 'WC_Cart' )->setMethods(
			[
				'get_subtotal',
				'get_shipping_total',
				'get_total_tax',
				'get_total',
			]
		)->getMock();

		$cart_mock->method( 'get_subtotal' )->willReturn( 10 );
		$cart_mock->method( 'get_shipping_total' )->willReturn( 5 );
		$cart_mock->method( 'get_total_tax' )->willReturn( 1 );
		$cart_mock->method( 'get_total' )->willReturn( "16.00" );

		$woo_mock       = $this->getMockBuilder( 'WooCommerce' )->getMock();
		$woo_mock->cart = $cart_mock;
		when( 'WC' )->justReturn( $woo_mock );

		$class = new WooCommerce( false );

		$this->expectOutputContains( '{"props":{"subtotal":10,"shipping":5,"tax":1,"total":"16.00"}}' );

		$class->track_entered_checkout();
	}

	/**
	 * @see WooCommerce::track_purchase()
	 * @return void
	 */
	public function testTrackPurchase() {
		$class = new WooCommerce( false );
		$mock  = $this->getMockBuilder( 'WC_Order' )->setMethods(
			[
				'get_meta',
				'get_total',
				'get_currency',
				'add_meta_data',
				'save',
			]
		)->getMock();
		$mock->method( 'get_meta' )->willReturn( false );
		$mock->method( 'get_total' )->willReturn( 10 );
		$mock->method( 'get_currency' )->willReturn( 'EUR' );

		when( 'wc_get_order' )->justReturn( $mock );

		$this->expectOutputContains( '{"revenue":{"amount":"10","currency":"EUR"}}' );

		$class->track_purchase( 1 );
	}
}
