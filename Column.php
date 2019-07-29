<?php 

/**
 *	Base class for the Stamp album professional that generates high quality pdf
 *	pages for stamp albums
 *
 *
 * @package    		Stamp Album Pro
 * @subpackage   	Album
 * @class    		Column
 * @version			1.0
 * @author			Dexter Oddwick <dexter@oddwick.com>
 * @copyright  		Copyright (c)2017
 * @license    		http://www.oddwick.com
 *
 * @todo    		
 *
 */



class Column {
		
		/**
		*	@var	array $params
		*	@var	Ezpdf $_pdf
		*	@var	int $_min_x
		*	@var	int $_min_y
		*	@var	int $_max_x  page width
		*	@var	int $_max_y  page height
		*	@var	int $_page keeps track of the number of pages 
		*	@var	array $_versionInfo
		*/
		protected $params;
		protected $_stamps = array(); // array of stamp objects object
		protected $_pdf; // ezpdf object
		protected $_maxColWidth = 0;
		protected $_maxColHeight = 0;
		protected $_actual_width = 0;
		protected $_actual_height = 0;
		protected $_spacing = 0;
		protected $_title = NULL;
		protected $_subtitle = NULL;
		protected $_text = NULL;


		/**
		*	Constructor initiates Album object
		*
		*	@access		public
		*	@param		string $orientation paper orientation
		*	@return 		void
		*/
		public function __construct( $pdf, $params, $maxWidth, $maxHeight){
		
					// set paremeters
					$this->_pdf = $pdf;
					$this->params = $params;
					
					// set working dimensions
					$this->_maxColWidth = $maxWidth;
					$this->_maxColHeight = $maxHeight;
					
					return $this;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		

	

			
		/**
		*	tests a stamp to see if it will fit in the column and calculates column height
		*	returns true if stamp fits and adds to array, otherwise returns false
		*
		*	@access		public
		*	@param		Stamp $stamp stamp object
		*	@return 		bool 
		*/
		public function addStamp( $stamp){
		
				// returns false if empty string is entered
				if(!$stamp){ //get_class( $stamp) != "Stamp" || ) {
					return 0;
				} 
				
				
				// if a stamp is larger that the page allows, force it to fit inside the margins
				if( $stamp->actual_height > $this->_maxColHeight){
					$stamp->forceHeight( $this->_maxColHeight-20);
				}
				
				if( $stamp->actual_width > $this->_maxColWidth){
					$stamp->forceWidth( $this->_maxColWidth-1);
				} 
				
				// else determines if a stamp will fit into the column with the gutter
				
				if( $stamp->actual_height + $this->params->gutter < $this->_maxColWidth){
				
					// checks to see if the stamp is wider than the wideest stamp
					// encountered, and if so, sets the column width to the width of the stamp
					if( $stamp->actual_width > $this->_actual_width){
						$this->_actual_width = $stamp->actual_width;
					}
					
					
					// add stamp width + gutter (if more than one stamp) to the actual width
					$this->_actual_height += $stamp->actual_height + (count( $this->_stamps) > 0?$this->params->gutter:0);
					
					// add stamp to array
					$this->_stamps[] = $stamp;
					
					return 1;
				
				} else {
					// returns 0 in the event the stamp would not fit
					return 0;
				}
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	
	

		/**
		*	returns the current height of the column
		*
		*	@access		public
		*	@return 		float 
		*/
		public function height(){
				return	$this->_actual_height + $this->_titleHeight + $this->_spacing;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	
	
		
		/**
		*	returns the current width of the column
		*
		*	@access		public
		*	@return 		int 
		*/
		public function width(){
				
				return	$this->_actual_width;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	
	
		
		/**
		*	returns the current height of the column
		*
		*	@access		public
		*	@return 		float 
		*/
		public function available(){
				return	$this->_maxColHeight - $this->height();
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	
	
		
		/**
		*	returns the number of stamps currently in the column
		*
		*	@access		public
		*	@return 		int 
		*/
		public function count(){
				return count( $this->_stamps);
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	
	
		
		/**
		*	adds spacing to the column
		*
		*	@access		public
		*	@return 		void 
		*/
		public function space( $space){
				
				$this->_spacing += $space;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	


		
		/**
		*	returns the current width of the column
		*
		*	@access		public
		*	@param		float $y the y dimension of the column
		*	@return 		void 
		*/
		public function write( $x, $y){
				
				// initialize variables
				// the starting point is the width of the page minus the width of the column (margin) divided in half
				//$x = ( $this->params->margin_adjust + $this->params->max_x - $this->_actual_width)/2;

				// debugging
				// debugging mode on and will print out a square showing the available field size
				if( $this->params->debug){
					$this->debug( $x, $y);
				}

				// shift the stamps up the distance of the text block if it is under the stamps
//				if(!$this->params->group_text_location == "above" ){
//					$y = $y + $this->_textHeight;
//				}
				
				
				$count = 0;
				// cycle through the stamps and output the stamps
				foreach( $this->_stamps as $stamp){
					
					// calculate the height of the stamp and move the pointer
					$y -= $stamp->actual_height;
					// center the stamps on an axis
					$xAlign = $x + ( ( $this->width() - $stamp->actual_width)/2 );						
					// write the stamp
					$stamp->write( $xAlign, $y);
					// move the y pointer down the length of the padding
					$y -= $this->params->gutter;
				} // end for 
				
				// writes out a column title if applicable
				if( $this->_title){
					//$yy = $this->_writeTitle( $y);
				}
								
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	
	
		
		/**
		*	Debugging function that visually shows the margins of the available working area
		*
		*	@access		private
		*	@return 		void
		*/
		protected function debug( $x, $y){
		
					$this->_pdf->setStrokeColor(0, 1, 0);
					$this->_pdf->setLineStyle(2);
					$this->_pdf->rectangle( $x,
										   $y - $this->height(),
										   $this->width(), 										   							
										   $this->height());
					// reset line color					   
					$this->_pdf->setStrokeColor(0.6, 0.6, 0.6);

			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
} // end class
?>
