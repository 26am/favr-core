<?php
/**
 * FieldSet tests.
 *
 * @package FavrCore
 */

declare(strict_types=1);

namespace FavrCore\Tests\Unit;

use FavrCore\Fields\FieldSet;

final class FieldSetTest extends TestCase {

	public function test_fields_are_normalized_and_grouped(): void {
		$set = new FieldSet(
			array(
				array( 'id' => 'phone', 'type' => 'tel', 'tab' => 'contact' ),
				array( 'id' => 'notes', 'tab' => 'staff', 'private' => true ),
			),
			array( 'contact' => array( 'label' => 'Contact', 'icon' => 'dashicons-phone' ) )
		);
		$this->assertSame( array( 'phone' ), array_keys( $set->forTab( 'contact' ) ) );
		$this->assertSame( 'text', $set->get( 'notes' )['type'], 'Defaults are filled in.' );
		$this->assertFalse( $set->get( 'phone' )['required'] );
		$this->assertNull( $set->get( 'missing' ) );
		$this->assertSame( 'Contact', $set->tabs()['contact']['label'] );
	}

	public function test_radio_is_sanitized_like_select(): void {
		$field = FieldSet::normalize(
			array(
				'id'      => 'type',
				'type'    => 'radio',
				'options' => array( 'individual' => 'Individual', 'business' => 'Business' ),
			)
		);
		$this->assertSame( 'business', \FavrCore\Fields\Sanitizer::sanitize( $field, 'business' ) );
		$this->assertSame( '', \FavrCore\Fields\Sanitizer::sanitize( $field, 'robot' ) );
	}
}
