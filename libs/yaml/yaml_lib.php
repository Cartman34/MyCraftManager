<?php
/** The yaml class
 * This class is made to get YAML configuration.
*/
class YAML extends ConfigCore {

	protected static $extension = 'yaml';
	
	/**	Loads configuration from new source.
	 * @param $source An identifier or a path to get the source.
	 * @param $cached True if this configuration should be cached.
	 * @return The loaded configuration array.
	 * 
	 * If an identifier, loads a configuration from a .yaml file in CONFDIR.
	 * Else $source is a full path to the YAML configuration file.
	*/
// 	public function load($source, $cached=true) {		
// // 		// Full path given
// // 		if( is_readable($source) ) {
// // 			$confPath = $source;
			
// // 		// File in configs folder
// // 		} else if( is_readable(static::getFilePath($source)) ) {
// // 			$confPath = static::getFilePath($source);
// // 			if( empty($confPath) ) {
// // 				return false;
// // 			}
			
// // 		/// File not found
// // 		} else {
// // 			return array();
// // 		}
// // 		$parsed = yaml_parse_file($confPath);
// 		return true;
// 	}

	/**	Parse configuration from given source.
	 * @param $source An identifier or a path to get the source.
	 * @return The loaded configuration array.
	 *
	 * If an identifier, load a configuration from a .yaml file in CONFDIR.
	 * Else $source is a full path to the YAML configuration file.
	 */
	public static function parse($source) {
		$path	= static::getFilePath($source);
		return $path ? yaml_parse_file(static::getFilePath($source)) : array();
	}

	/**	Checks if configuration source exists
	 * @param $source An identifier to check the source.
	 * 
	 * Checks the configuration from the source is available.
	*/
// 	public function checkSource($source) {
// 		try {
// 			return is_readable($source) || is_readable(static::getFilePath($source));
// 		} catch( Exception $e ) {
// 			return false;
// 		}
// 	}

	/**	Gets the file path
	 * @param $source An identifier to get the source.
	 * @return The configuration file path according to Orpheus file are organized.
	 * 
	 * Gets the configuration file path in CONFDIR.
	*/
// 	public static function getFilePath($source) {
// 		return pathOf(CONFDIR.$source.'.'.self::EXT, true);
// 	}
	
}

if( !class_exists('Config', false) ) {
	class Config extends YAML {}
}
