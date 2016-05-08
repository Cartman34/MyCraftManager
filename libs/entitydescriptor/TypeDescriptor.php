<?php

abstract class TypeDescriptor {

	protected $name;
	protected $writable;
	protected $nullable;
	
// 	public function __construct() {
// 	}
	
	/**
	 * Get the type name
	 * @return string the type name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get true if field is writable
	 * @return boolean
	 */
	public function isWritable() {
		return $this->writable;
	}

	/**
	 * Get true if field is nullable
	 * @return boolean
	 */
	public function isNullable() {
		return $this->nullable;
	}

	/**
	 * Get the html input attributes string for the given args
	 * @return string
	 */
	public function htmlInputAttr($args) {
		return '';
	}

	/**
	 * Get the html input attributes array for the given Field descriptor
	 * @return string[]
	 */
	public function getHTMLInputAttr($Field) {
		return array();
	}

	/**
	 * Get true if we consider null an empty input string
	 * @return boolean
	 */
	public function emptyIsNull($field) {
		return true;
	}
	
	/**
	 * Parse args from field declaration
	 * @param $args string[] Arguments
	 * @return stdClass
	 */
	public function parseArgs($args) {
		return new stdClass();
	}
	
	public function validate($Field, &$value, $inputData, &$ref) {}
	
	/**
	 * Use formatValue()
	 * 
	 * @param FieldDescriptor $field The field to parse
	 * @param string $value The field value to parse
	 * @deprecated Use formatValue()
	 */
	public function format($Field, &$value) {}


	/**
	 * Parse the value from SQL scalar to PHP type
	 *
	 * @param FieldDescriptor $field The field to parse
	 * @param string $value The field value to parse
	 * @return string The parse $value
	 * @see PermanentObject::formatFieldValue()
	 */
	public function parseValue(FieldDescriptor $field, $value) {
		return $value;
	}

	/**
	 * Format the value from PHP type to SQL scalar 
	 *
	 * @param FieldDescriptor $field The field to parse
	 * @param string $value The field value to parse
	 * @return string The parse $value
	 * @see PermanentObject::formatFieldValue()
	 */
	public function formatValue(FieldDescriptor $field, $value) {
		return $value;
	}
	
}
