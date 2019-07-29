<?php  

/**
 *	Base class for the Stamp album professional that generates high quality pdf
 *	pages for stamp albums
 *
 *
 * @package    		Stamp Album Pro
 * @subpackage   	Album
 * @class    		Title
 * @version			1.0
 * @author			Dexter Oddwick <dexter@oddwick.com>
 * @copyright  		Copyright (c)2017
 * @license    		http://www.oddwick.com
 *
 * @todo    		
 *
 */


class Title extends Core {
		
		/**
		 * _display
		 * 
		 * (default value: array())
		 * 
		 * @var array
		 * @access protected
		 */
		protected $_display = array();
		
		/**
		 * _stamp
		 * 
		 * (default value: array())
		 * 
		 * @var array
		 * @access protected
		 */
		protected $_stamp = array();
		
		/**
		 * _title
		 * 
		 * (default value: "")
		 * 
		 * @var string
		 * @access protected
		 */
		protected $_title = "";
		
		/**
		 * _catalog
		 * 
		 * (default value: "")
		 * 
		 * @var string
		 * @access protected
		 */
		protected $_catalog = "";
		
		
		
		
		
		

		/**
		*	Constructor initiates stamp title
		*
		*	@access		public
		*	@param		array $stamp
		*	@throws		Nerb_Error
		*	@return 	void
		*/
		public function __construct( array $stamp ){
			
			#	transfer param and pdf to internal variables
			#  this line is required in all classes
			parent::__construct();
			
			# 	add stamp data to array
			if( is_array( $stamp ) ){
				$this->_stamp = $stamp;
			} else {
				throw new Nerb_Error( "Stamp data was not properly passed as an array." );
			}
			
			#	add dispaly variables
			$this->_display = $this->params->value( "params", "display" );
			
			
			//Nerb::inspect( $this->_display, true );
			
			#	construct a title
			$this->_title = $this->_build();
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
	

		/**
		*	Returns a formatted title string
		*
		*	@access		public
		*	@return 	string
		*/
		public function __toString(){
				return $this->_title;
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		/**
		*	Alias of $this->__toString()
		*
		*	@access		public
		*	@return 	string
		*/
		public function title(){
		
				return $this->__toString();
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		/**
		*	returns formatted catalog number for this stamp
		*
		*	@access		public
		*	@return 	string 
		*/
		public function cat(){
			
				return $this->_catalog;
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		

		/**
		*	formats the catalog number into a string and adds it to _catalog
		*
		*	@access		protected
		*	@return 	string 
		*/
		protected function _cat(){
		
			return $this->_catalog = $this->_stamp["prefix"].$this->_stamp["catalog_number"].$this->_stamp["suffix"];
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		

		/**
		*	creates the title string
		*
		*	@access		protected
		*	@return 	string 
		*/
		protected function _build(){
			
			# initialize title
			$title = "";
			
			// define scott numbering if cat is set
			if( $this->_display["catalog_number"] ){
				$title .= $this->_cat();
				$title .= " - ";
			}
			
			#	year of issue
			if( $this->_display["issue_year"] ){
				$title .= $this->_stamp["issue_year"]." ";
			}
			if( $this->_display["denomination"] ) $title .= $this->_stamp["denomination"]." ";
			
			$title .= stripslashes( $this->_stamp["title"] );
			
			// if short desctiption is on, truncate the variety
			if( $this->_display["variety"] && $this->_stamp["variety"]){
				$title .= ",";
			
				if( $this->_display["shortDescription"]){
					$title .= stripslashes( substr( $this->_stamp["variety"], 0, $this->params->stamp_max_chars ) );
					$title .= strlen( $this->_stamp["variety"]) > $this->params->stamp_max_chars ?"&hellip;":NULL;	
				} else {
					$title .= stripslashes( $this->_stamp["variety"]);
				}
			}
			
			if( $this->_display["color"] && $this->_stamp["color"] ){ 
				$title .= ",\n".ucwords( $this->_stamp["color"]);
			}
			
			if( $this->_display["press"] && $this->_stamp["press"] ) {
				$title .= ", ".stripslashes( $this->_stamp["press"]);
			}
			
			if( $this->_display["perforation"] && $this->_stamp["perforation"]>0 ) {
				$title .= ", <i>perf&nbsp;".stripslashes( $this->_stamp["perforation"]);
			}
			
			if( $this->_display["paper"] && $this->_stamp["paper"] ) {
				$title .= ", ".stripslashes( $this->_stamp["paper"]);
			}
			
			if( $this->_display["watermark"] && $this->_stamp["watermark"] ) {
				$title .= ", ".stripslashes( $this->_stamp["watermark"])." watermark</i>";
			}
			
			if( $this->_display["issue_date"] && $this->_stamp["issue_date"] ) {
				$date = date("F d, Y", strtotime( stripslashes( $this->_stamp["issue_date"] ) ) );
				$title .= ", ".$date;
			}
			
			if( $this->_display["issue_location"] && $this->_stamp["issue_location"] ) {
				$title .= ", ".stripslashes( $this->_stamp["issue_location"]);
			}
			
			if( $this->_display["designer"] && $this->_stamp["designer"] ) {
				$title .= ", Designer: <i>".stripslashes( $this->_stamp["designe"]);
			}
			
			if( $this->_display["engraver"] && $this->_stamp["engraver"] ) {
				$title .= ", Engr: <i>".stripslashes( $this->_stamp["engraver"]);
			}
			
			if( $this->_display["issue_qty"] && $this->_stamp["issue_qty"] ) {
				$title .= ", ".stripslashes( $this->_stamp["issue_qty"])." issued";
			}
			
			if( $this->_display["printer"] && $this->_stamp["printer"] ) {
				$title .= ", ".stripslashes( $this->_stamp["printer"]);
			}
			
			
			return $title;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		

		/**
		*	formats the title string with tags
		*
		*	@access		public
		*	@param		string $string
		*	@param		string $tag
		*	@return 	string 
		*/
		protected function _format( $string, $tag = "i" ){
			
			#	wrap and return formatted string
			return "<$tag>$string</$tag>";

		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------		
		
} // end class 

?>
