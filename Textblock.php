<?php  

/**
 *	Base class for the Stamp album professional that generates high quality pdf
 *	pages for stamp albums
 *
 *
 * @package    		Stamp Album Pro
 * @subpackage   	Album
 * @class    		Textblock
 * @version			1.0
 * @author			Dexter Oddwick <dexter@oddwick.com>
 * @copyright  		Copyright (c)2017
 * @license    		http://www.oddwick.com
 *
 * @todo    		
 *
 */



class Textblock {
		
		/**
		*	@var	array $params
		*	@var	Ezpdf $_pdf
		*	@var	int $_x  current x position of the cursor
		*	@var	int $_y  current y position of the cursor
		*	@var	int $_min_x
		*	@var	int $_min_y
		*	@var	int $_max_x  page width
		*	@var	int $_max_y  page height
		*	@var	int $_page keeps track of the number of pages 
		*	@var	array $_versionInfo
		*/
		protected $params;
		protected $_pdf; // ezpdf object
		protected $_maxWidth = 0;
		protected $_maxHeight = 0;
		protected $_actual_width = 0;
		protected $_actual_height = 0;
		protected $_column_width = 0;
		protected $_columnHeight = 0;
		protected $_linesPerColumn = 0; // the number of text lines per column rounded up to the nearest whole number
		protected $_maxLinesPerColumn = 0; // the maximum number of text lines per column based on available area
		protected $_gutter = 15;
		protected $_columns = 3;
		protected $_title = NULL;
		protected $_subtitle = NULL;
		protected $_text = NULL;
		protected $_titleHeight = 0;
		protected $_textHeight = 0;


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
					$this->_maxWidth = $maxWidth;
					$this->_maxHeight = $maxHeight;
					
					// the default columns are 3 unless columns are set, then the 
					// number is overriden since the number of stamp columns will always be 1
					if( $this->params->cols) $this->_columns = $this->params->cols;
					
					return $this;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
	
	

		/**
		*	returns the current height of the textblock
		*
		*	@access		public
		*	@return 		float 
		*/
		public function height(){
				
				return	$this->_actual_height;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	
	
		
		/**
		*	returns the current width of the text block
		*
		*	@access		public
		*	@return 		int 
		*/
		public function width(){
				
				return	$this->_maxWidth;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	


		
		/**
		*	adds a title to the text block
		*
		*	@access		public
		*	@param		string $title 
		*	@param		string $subtitle 
		*	@return 	void 
		*/
		public function addText( $text, $title = NULL, $subtitle = NULL){
				
				// if no titles are given drop out of function
				if(!$text) return false;
				
				// clean up text
				$text = html_entity_decode( $text, ENT_QUOTES, "ISO8859-1" );
				$text = str_replace(chr(9), "     ", $text);
				$text = str_replace("\\", "", $text);
				$text = "     ".str_replace(chr(13), chr(13)."     ", $text);
				
				$this->_text = explode(chr(13), $text);
				$this->_title = $title;
				$this->_subtitle = $subtitle;
				
				// calculate size
				$this->_actual_height = $this->_calculateSize();
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

		
		
		/**
		*	writes out the contents of the title and subtitle
		*
		*	@access		public
		*	@param		float $y 
		*	@return 		void 
		*/
		public function write( $y, $x = NULL ){
				
				// if the text is not in an array drop out of function
				if(! count( $this->_text) ) return false;
				
				// get font size
				$font_size = $this->_pdf->getFontHeight( $this->params->default_font_size);

				// debugging mode on and will print out a square showing the available field size
				if( $this->params->debug){
					$this->debug( $x ? $x : ( $this->params->max_x - $this->_maxWidth) /2, $y);
				}
				
				// if x coordinate is given
				if( $x){
					// set the margins so that the text block is as wide as the row
					$margin = $this->params->max_x - ( $this->_maxWidth + $x);
					$this->_pdf->ezSetmargins(0, 0,
									  $x + $this->params->margin_adjust,
									  $margin);
									  
				} else {
					// set the margins so that the text block is as wide as the row
					$margin = ( $this->params->max_x - $this->_maxWidth) /2;
					$this->_pdf->ezSetmargins(0, 0,
									  $margin + $this->params->margin_adjust,
									  $margin);
				}		  
								  
								  
				
				$y = $y + $this->height();
				$this->_pdf->ezSetY( $y);
				
				// write the title block
				$this->_writeTitle( $y );
				
				// define the top of the column
				$yCol = $yColBegin = $this->_pdf->ezGetY();
				
				
				// define the left edge
				$xColBegin = $x ? $x : (( $this->params->max_x - $this->width())/2)+( $this->params->margin_adjust/2);
				
				// write out the text and calculate the number of lines of text per column
				foreach( $this->_text as $text){
					do{
						$text = $this->_pdf->addTextWrap( $xColBegin,$yCol,$this->_column_width,$this->params->default_font_size,$text,$this->params->text_justify);
					
						++$lines;
						$yCol -= $font_size;
						
						// reset to new column
						if( $lines % $this->_linesPerColumn == 0){
							$yCol = $yColBegin;
							$xColBegin += $this->_column_width + $this->_gutter;
							// text has exceeded allowed space and break out of while loop
							if(++$col == $this->_columns) break;
						}
					
					} while ( $text);
				}// end foreach
				
				//die;
				$this->_linesPerColumn = ceil( $lines/$this->_columns);
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	
		
		
		
		/**
		*	writes out the contents of the title and subtitle
		*
		*	@access		public
		*	@param		float $y 
		*	@return 		void 
		*/
		private function _calculateSize(){
		
				//calculate the title height
				if( $this->_title){
					$this->_titleHeight += $this->_pdf->getFontHeight( $this->params->row_title_font_size) + $this->params->group_title_separation;
				}
		
				if( $this->_subtitle){
					$this->_titleHeight += $this->_pdf->getFontHeight( $this->params->row_subtitle_font_size) + $this->params->group_caption_spacing;
				}
				
				// calculate the column width with gutters
				$this->_column_width = ( $this->_maxWidth - (( $this->_columns -1) * $this->_gutter ))/$this->_columns;
		
				// set the y at the top of the page
				$this->_pdf->ezSetY( $this->params->max_y);
				
				// write out the text and calculate the number of lines of text per column
				$this->_pdf->transaction('start');
				
				foreach( $this->_text as $text){
					do{
						$text = $this->_pdf->addTextWrap(0,0,$this->_column_width,$this->params->default_font_size, $text, $this->params->text_justify);
						++$lines;
					} while ( $text);
				}
				
				
				$this->_pdf->transaction('abort');
				
				$this->_linesPerColumn = ceil( $lines/$this->_columns);
				$this->_maxLinesPerColumn = floor(( $this->_maxHeight- $this->_titleHeight)/$this->_pdf->getFontHeight( $this->params->default_font_size));
				
				// calculate the maximum number of lines based on the available area and if the text 
				// exceeds the allowed lines, truncate with the max allowed lines
				if( $this->_linesPerColumn > $this->_maxLinesPerColumn) $this->_linesPerColumn = $this->_maxLinesPerColumn;
				
				return $this->_actual_height = ( $this->_linesPerColumn * $this->_pdf->getFontHeight( $this->params->default_font_size)) + $this->_titleHeight;

		}



				
		/**
		*	writes out the contents of the title and subtitle
		*
		*	@access		public
		*	@param		float $y 
		*	@return 		void 
		*/
		private function _writeTitle( $y){
		
				// if no title is given, return false
				if(!$this->_title) return false;
				
				// return the title
				$this->_pdf->ezText("<b>".ucwords( $this->_title)."</b>", $this->params->row_title_font_size, array('text_justify'=>'center'));
					
				// return the subtitle
				if( $this->_subtitle){
					$this->_pdf->ezSetDy(-$this->params->group_caption_spacing);				
					$this->_pdf->ezText("<i>".ucwords( $this->_subtitle)."</i>", $this->params->row_subtitle_font_size, array('text_justify'=>'center'));	
				}
				
				// set the spacing if given
				$this->_pdf->ezSetDy( - $this->params->group_title_separation - 10 );
												
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	
	
	
		
		/**
		*	Debugging function that visually shows the margins of the available working area
		*
		*	@access		private
		*	@return 		void
		*/
		protected function debug( $x, $y){
		
					$this->_pdf->setStrokeColor(0.5, 1, 0.5);
					$this->_pdf->setLineStyle(5);
					$this->_pdf->rectangle( $x,
										   $y,
										   $this->width(), 										   							
										   $this->height());
					// reset line color					   
					$this->_pdf->setStrokeColor(0.6, 0.6, 0.6);

			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
} // end class
?>
