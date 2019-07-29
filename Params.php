<?php  

/**
 *	This is a container class for Stamp album professional.
 *  the parameters are read from a .ini file and the file MUST contain
 *	a [params] section for the default getters and setters to work
 *	
 *	
 *
 * @package    		Stamp Album Pro
 * @subpackage   	Album
 * @class    		Params
 * @version			1.0
 * @author			Dexter Oddwick <dexter@oddwick.com>
 * @copyright  		Copyright (c)2017
 * @license    		http://www.oddwick.com
 *
 * @todo    		make easier
 * @requires		~/config/defaults.ini
 * @requires		~/config/options.ini
 *
 */



class Params extends Nerb_Params {
		


		/**
		 * 	Constructor initiates Param object
		 * 
		 * 	@access public
		 * 	@param mixed $ini
		 * 	@param array $params (default: array())
		 * 	@throws Nerb_Error
		 * 	@return Params
		 */
		public function __construct( $ini, $params = array() ){
		
			# force the default construction
			parent::__construct( $ini, $params );
			
			# format creation date and mod date as a date and add it to the array 
			$this->_params["info"]["CreationDate"] = $this->_params["info"]["ModDate"] = date( "F j, Y  g:i a", time() );
			 
			return $this;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		

		
		/**
		*	Fetches all the display parameters
		*
		*	@access		public
		*	@return 	array the return the display variables
		*/
		public function display(){
					
			// returns all values
			return $this->_params["display"];
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
} // end class
?>
