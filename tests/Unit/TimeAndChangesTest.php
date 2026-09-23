<?php
/**
 * Time sanitizing and pending-change comparison tests.
 *
 * @package FavrCore
 */

declare(strict_types=1);

namespace FavrCore\Tests\Unit;

use Brain\Monkey\Functions;
use FavrCore\Fields\Sanitizer;
use FavrCore\Moderation\PendingChanges;

final class TimeAndChangesTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Functions\when( 'wp_json_encode' )->alias( static fn( $v ) => json_encode( $v ) );
	}

	public function test_time_accepts_common_formats(): void {
		$this->assertSame( '09:05', Sanitizer::time( '9:05' ) );
		$this->assertSame( '17:30', Sanitizer::time( '17:30:00' ) );
		$this->assertSame( '13:00', Sanitizer::time( '1pm' ) );
		$this->assertSame( '00:15', Sanitizer::time( '12:15 am' ) );
		$this->assertSame( '', Sanitizer::time( '25:00' ) );
		$this->assertSame( '', Sanitizer::time( 'noonish' ) );
	}

	public function test_differs_treats_empty_values_alike(): void {
		$this->assertFalse( PendingChanges::differs( '', null ) );
		$this->assertFalse( PendingChanges::differs( array(), '' ) );
		$this->assertFalse( PendingChanges::differs( 5, '5' ) );
		$this->assertFalse( PendingChanges::differs( array( 1, 2 ), array( '1', '2' ) ) );
	}

	public function test_differs_detects_changes(): void {
		$this->assertTrue( PendingChanges::differs( 'a', 'b' ) );
		$this->assertTrue( PendingChanges::differs( array( 1, 2 ), array( 2, 1 ) ) );
		$this->assertTrue( PendingChanges::differs( array( 'x' => '1' ), array( 'x' => '2' ) ) );
	}

	public function test_propose_drops_unchanged_and_replaces(): void {
		$store = array();
		Functions\when( 'get_post_meta' )->alias( static function () use ( &$store ) {
			return $store ?: '';
		} );
		Functions\when( 'update_post_meta' )->alias( static function ( $id, $key, $value ) use ( &$store ) {
			$store = $value;
			return true;
		} );
		Functions\when( 'delete_post_meta' )->alias( static function () use ( &$store ) {
			$store = array();
			return true;
		} );

		$added = PendingChanges::propose( 1, array( 'phone' => array( 'old' => '1', 'new' => '2' ), 'email' => array( 'old' => 'a', 'new' => 'a' ) ), 7 );
		$this->assertSame( array( 'phone' ), $added );
		$this->assertSame( '2', PendingChanges::get( 1 )['phone']['new'] );

		PendingChanges::propose( 1, array( 'phone' => array( 'old' => '1', 'new' => '1' ) ), 7 );
		$this->assertSame( array(), PendingChanges::get( 1 ) );
	}
}
