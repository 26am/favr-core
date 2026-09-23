<?php
/**
 * Rate limit tests.
 *
 * @package FavrCore
 */

declare(strict_types=1);

namespace FavrCore\Tests\Unit;

use Brain\Monkey\Functions;
use FavrCore\Support\RateLimit;

final class RateLimitTest extends TestCase {

	public function test_allows_up_to_max_then_blocks(): void {
		$store = array();
		Functions\when( 'set_transient' )->alias(
			static function ( $k, $v ) use ( &$store ) {
				$store[ $k ] = $v;
				return true;
			}
		);
		Functions\when( 'get_transient' )->alias( static function ( $k ) use ( &$store ) {
			return $store[ $k ] ?? false;
		} );
		if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
			define( 'HOUR_IN_SECONDS', 3600 );
		}
		$this->assertTrue( RateLimit::hit( 'a', 2 ) );
		$this->assertTrue( RateLimit::hit( 'a', 2 ) );
		$this->assertFalse( RateLimit::hit( 'a', 2 ) );
		$this->assertTrue( RateLimit::hit( 'b', 2 ) );
	}
}
