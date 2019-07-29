<?php  

/**
 *	Base class for the Stamp album professional that generates high quality pdf
 *	pages for stamp albums
 *
 *
 * @package    		Stamp Album Pro
 * @subpackage   	Album
 * @class    		Row
 * @version			1.0
 * @author			Dexter Oddwick <dexter@oddwick.com>
 * @copyright  		Copyright (c)2017
 * @license    		http://www.oddwick.com
 *
 * @todo    		
 *
 */



class Row extends Core {
		
		/**
		*	@var	array $params
		*	@var	Ezpdf $_pdf
		*	@var	int $_x  current x position of the cursor
		*	@var	int $_y  current y position of the cursor
		*	@var	int $_min_x
		*	@var	int $_min_y
		*	@var	int $max_x  page width
		*	@var	int $max_y  page height
		*	@var	int $_page keeps track of the number of pages 
		*	@var	array $_versionInfo
		*/
		protected $_stamps = array(); // array of stamp objects object
		protected $_data = array(
					"actual" => array( "x" => 0, 
									  "y" => 0, 
									  "width" => 0, 
									  "height" => 0 
									  ),
					"max" => array( "x" => 0, 
									  "y" => 0, 
									  "width" => 0, 
									  "height" => 0 
									  ),
				);



		/**
		*	Constructor creates a row object
		*
		*	@access		public
		*	@param		array $dimensions the actual working dimensions of the field
		*	@return 	Row
		*/
		public function __construct( $maxRowWidth, $maxRowHeight ){
		
			#	transfer param and pdf to internal variables
			#  this line is required in all classes
			parent::__construct();

			# set working dimensions
			$this->max_width = $maxRowWidth;
			$this->max_height = $maxRowHeight;
			
			return $this;
				
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		

	

			
		/**
		*	tests a stamp to see if it will fit in the row and calculates row height
		*	returns true if stamp fits and adds to array, otherwise returns false
		*
		*	@access			public
		*	@param			Stamp $stamp stamp object
		*	@return 		bool 
		*/
		public function addStamp( Stamp $stamp ){
		
		
			# error checking
			# for right now, just exit out of the rquest if a stamp is too large for the page
			# this should really only apply to a handful of stamps like flag strips etc. 
			if( $stamp->height() > $this->max_height ) {
				Nerb::jump( "/build/createPage?error=".urlencode("<strong>".$stamp->title()."</strong> is too tall for your current settings, Please remove it and try again") );
			} 
			
			if( $stamp->width() > $this->max_width ){
				Nerb::jump( "/build/createPage?error=".urlencode("<strong>".$stamp->title()."</strong>  is too wide for your current settings, Please remove it and try again") );
			}
			
			if ( !$stamp ||  get_class( $stamp ) != "Stamp" ){
				Nerb::jump( "/build/createPage?error=".urlencode($stamp->title()." caused an error creating your page") );
			} 
			
			
			
			# this block checks to see it the stamp will fit 
			# if column limits are set and the row is full return false
			if( $this->params->force_columns > 0 && count( $this->_stamps ) >= $this->params->force_columns )
				return 0;
			
			# else determines if a stamp will fit into the row with the gutter
			if( $stamp->width() + ( count( $this->_stamps ) > 0 ? $this->params->gutter : 0 ) + $this->actual_width > $this->max_width )
				return 0;
			
			// checks to see if the stamp is taller than the tallest stamp
			// encountered, and if so, sets the row height to the height of the stamp
			if( $stamp->height() > $this->actual_height){
				$this->actual_height = $stamp->height();
			}
			
			// add stamp width + gutter (if more than one stamp) to the actual width
			$this->actual_width += ( $stamp->width() + ( count( $this->_stamps) > 0 ? $this->params->gutter : 0 ) );
			
			// add stamp to array
			$this->_stamps[] = $stamp;
			
			# if stamp has been sucessfully added, then return true
			return true;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	
	
	

		/**
		*	returns the current height of the row
		*
		*	@access		public
		*	@return 	float 
		*/
		public function height(){
			return	$this->actual_height;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	
	
		
		
		/**
		*	returns the current width of the row
		*
		*	@access		public
		*	@return 	float 
		*/
		public function width(){
			return	$this->actual_width;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

		
		
		
		/**
		*	returns the number of stamps currently in the row
		*
		*	@access		public
		*	@return 	int 
		*/
		public function count(){
			return count( $this->_stamps);
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	
	
		
		
		
		/**
		*	writes out the actual row
		*
		*	@access		protected
		*	@param		float $x the x starting location of the row
		*	@param		float $y the y starting location of the row
		*	@return 	void 
		*/
		protected function _write( $x, $y ){
			
				$this->_origin( $x, $y, "Starting point");
				
				# initialixe variables
				$this->_setOrigin( $x, $y - $this->height() );
				$count = 0;
				
				// cycle through the stamps and output the stamps
				foreach( $this->_stamps as $stamp ){
					
					if( $this->params->stamp_vert_align == "top"){
						// top vertical alignment is y pointer plus row height, minus stamp height
						$vAlign = $y - $stamp->height() + $this->actual_height - $stamp->actual_height;
						
					}elseif( $this->params->stamp_vert_align == "bottom"){
						// align bottoms of stamps
						$vAlign = $y - $stamp->height();
					
					}else{
						// default top vertical alignment is y pointer plus 1/2 row height, minus stamp height
						$vAlign = $y - $stamp->height() + ( $this->actual_height - $stamp->actual_height) / 2;
					}
					
					// write the stamp
					$stamp->write( $x, $vAlign);
					
					// move to X pointer over to the next stamp
					$x = $x + $stamp->actual_width + $this->params->gutter;
				} // end foreach 
				
				
				# debugging mode on and will print out a square showing the available field size
				if(  $this->params->debug == "ALL" ||  $this->params->debug == "row" ){
					$this->_debug( $this->_data );
				}
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	
		
		
		
		
} // end class





?>
