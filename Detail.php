<?php   

/**
 *	Base class for the Stamp album professional that generates high quality pdf
 *	pages for stamp albums
 *
 *
 * @package    		Stamp Album Pro
 * @subpackage   	Album
 * @class    		Detail
 * @version			1.0
 * @author			Dexter Oddwick <dexter@oddwick.com>
 * @copyright  		Copyright (c)2017
 * @license    		http://www.oddwick.com
 *
 * @todo    		
 *
 */



class Detail {
		
		/**
		*	@var	Param $params
		*	@var	array $_data
		*	@var	array $_versionInfo
		*/
		protected $params; // params object
		protected $_stamp; // Nerb_Database_Row item containing stamp data
		protected $_pdf; 
		protected $_maxWidth = 0; 
		protected $_actual_height = 0; 
		protected $_data = array(
					'issue_date' => "Issue Date",	
					'issue_location' => "Issued at",	
					'issue_qty' => "Qty",	
					'designer' => "Designer",	
					'engraver' => "Engr.",	
					'press' => "Press",	
					'printer' => "Printer",	
					'paper' => "Paper",	
					'watermark' => "Wtrmk",	
				);


		/**
		*	Constructor initiates Stamp object
		*
		*	@access	public
		*	@param	Param $params the paramater data object
		*	@param	Cezpdf $pdf the pdf that is being created
		*	@param	int $stamp_id corresponds to the stamp_id in the database
		*	@return 	Stamp
		*/
		public function __construct( $stamp, $pdf, $params, $maxWidth){
			
				$this->_stamp = $stamp;
				$this->params = $params;
				$this->_pdf = $pdf;
				$this->_maxWidth = $maxWidth;
				return $this;
			
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		
		/**
		*	actually writes the stamp details to the pdf
		*
		*	@access		public
		*	@param		float $x
		*	@param		float $y
		*	@return 	bool 
		*/
		public function write( $x, $y){
		
				
				// reset color and stroke
				// for legibility purposes, the default caption font is always helvetica and black
				$this->_pdf->setColor(0,0,0);
				$this->_pdf->setLineStyle(1);
				$this->_pdf->selectFont(__FONTS__.'/Helvetica.afm');
				
				// set y and margins
				$this->_pdf->ezSetY( $y);
				$this->_pdf->ezSetmargins(0, 0, $x, $this->params->max_x - $x - $this->_maxWidth);
					
				foreach( $this->_data as $key=>$title){
					if( $info = $this->_stamp->data( $key)){
						$caption = "<b>".$title."</b> - ".$info;
						$this->_pdf->ezText( $caption, $this->params->caption_size, array('text_justify'=>'left'));
						$this->_pdf->ezSetDy(-5);
					}
				}
				// debugging mode on and will print out a square showing the available field size
				if( $this->params->debug){
					$this->debug( $x, $y);
				}

		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		


		/**
		*	Debugging function that visually shows the margins of the available working area
		*
		*	@access		private
		*	@return 		void
		*/
		protected function debug( $x , $y){
		
					$this->_pdf->setStrokeColor(1, 1, 0);
					$this->_pdf->setLineStyle(1);
					$this->_pdf->rectangle( $x, $y, $maxWidth, $this->actual_height);
					// reset line color					   
					$this->_pdf->setStrokeColor(0.6, 0.6, 0.6);

			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------

		
} // end class
?>
