<?php   

/**
 *	Base class for the Stamp album professional that generates high quality pdf
 *	pages for stamp albums
 *
 *
 * @package    		Stamp Album Pro
 * @subpackage   	Album
 * @class    		Field
 * @version			1.0
 * @author			Dexter Oddwick <dexter@oddwick.com>
 * @copyright  		Copyright (c)2017
 * @license    		http://www.oddwick.com
 *
 * @todo    		
 *
 */




class Field extends Core {
		
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
		protected $_mode = "rows"; // [cols|rows] how the field is going to be initialized
		protected $_data = array(); // dataset for stamps
		protected $_stats = array('x' => 0,
								  'y' => 0,
								  "width" => 0,
								  "height" => 0,
								  "field_min_x" => 0,
								  "field_max_x" => 0,
								  "field_min_y" => 0,
								  "field_max_y" => 0,
								  "max_row_width" => 0,
								  "max_row_height" => 0,
								  "max_col_width" => 0,
								  "max_col_height" => 0,
								  'current_row_width' => 0,
								  'current_height' => 0,
								  'available' => 0,
								 ); 


		/**
		*	Constructor initiates Album object
		*
		*	@access		public
		*	@param		string $orientation paper orientation
		*	@return 		void
		*/
		public function __construct( $data ){
		
				#	transfer param and pdf to internal variables
				#  this line is required in all classes
				parent::__construct();
				
				$this->_stats = array_merge( $this->_stats, $data );
				
				$this->_stats["available"] = $this->_stats["max_row_width"];
				
				//Nerb::inspect( $this->_stats, true );

				# sets the boundries for the page
				//$this->_stats = array_merge( $this->_stats, $data );
				//$this->_stats['maxRowWidth'] = $this->_stats['max_x'] - $this->_stats['min_x'];
				//$this->_stats['maxHeight'] = $this->_stats['max_y'] - $this->_stats['min_y'];
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		
			
		/**
		*	returns the images in rows
		*
		*	@access		public
		*	@param		array $stamps
		*	@return 		string 
		*/
		public function add( $row ){
		
				// if the row to be added + padding will not fit, return 0 and not 
				// accept any more rows
				if( $row->height() + $this->params->gutter > $this->available() ){
					return 0;
				}
				
				
				//add the row to the dataset
				$this->_data[] = $row;
				
				// increment the field height
				$this->_stats['totalHeight'] += $row->height(); 
				
				// sets the current row width to the widest row
				if( $row->width() > $this->_stats['actualRowWidth']){
					$this->_stats['actualRowWidth'] = $row->width(); 
				}
					
				return $this->available();
				
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

		
	

		
		
		/**
		*	writes the rows to the page
		*
		*	@access		public
		*	@param		float $start
		*	@return 		void
		*/
		public function write(){
				
				// define the starting point
				$start = $this->_stats['max_y'] - ( $this->_stats['maxHeight']/2) + ( $this->totalHeight()/2);
				
				// debugging mode on and will print out a square showing the available field size
				if( $this->params->debug){
					//$this->_debug( $start);
				}// end if debug

				// cycle rows array write each row
				foreach( $this->_data as $row){
					// increment the starting point of the next row
					$start = $start - $row->height();
					// write out contents of row
					$row->write( $start);
					// increment the starting point of the next row
					$start = $row->height() > 0 ? $start - $this->params->gutter : $start;
				}// end for each
					
		}// end function ----------------------------------------------------------------------------------------------------------------------------------------------- write()
		
		

		

		/**
		*	function for dumping the contents of the field to the pdf
		*
		*	@access		public
		*	@return 	string 
		*/
		public function dump(){
			return $this->_stats;
		}// end function ----------------------------------------------------------------------------------------------------------------------------------------------- write()
		

		

		/**
		*	gives the remainig area available for insertion
		*
		*	@access		public
		*	@return 	float 
		*/
		public function available(){
			return $this->_stats["available"];
			
		}// end function ----------------------------------------------------------------------------------------------------------------------------------------------- write()
		

		

		/**
		*	gives the total height of the rows currently added to the field
		*
		*	@access		public
		*	@return 	float 
		*/
		public function totalHeight(){
			// sets the gutters so that they are count-1 or inbetween the rows
			$gutters = $this->params->gutter * (count( $this->_data) > 1? count( $this->_data)-1 : 0 );
			return $this->_stats['totalHeight'] + $gutters;
			
		}// end function ----------------------------------------------------------------------------------------------------------------------------------------------- write()
		

		

		/**
		*	gives the current width of the field
		*
		*	@access		public
		*	@return 		float 
		*/
		public function width(){
		
			return $this->_stats['actualRowWidth'];
			
		}// end function ----------------------------------------------------------------------------------------------------------------------------------------------- write()
		

		

		/**
		*	writes the border to the page
		*
		*	@access		public
		*	@param		int $forceBorder forces the border to other than the default
		*	@return 	void 
		*/
/*

		public function head( $data ){
				// will add an image to the page if:
				//	image name is set
				//  and either the page is the first page OR image repeat is set to true
				if( $this->params['image_name'] && ( $this->_page == 0 || $this->params['image_repeat'] == true)){
	
					$topOfPage = $this->_pdf->ezGetY();
					$this->params['dimensions'] = $this->_addImageToPage();
					
					if( $this->params['image_as_column']){
						if( $this->params['image_horiz_align'] == 'right'){
							$this->params['margin_right'] = $this->params['margin_left'] + $this->params['dimensions']['width'] + $this->params['gutter'];
						} else {
							$this->params['margin_left'] = $this->params['margin_right'] + $this->params['dimensions']['width'] + $this->params['gutter'];
						}
						// reset y to top of page
					 	$this->_pdf->ezSetY( $topOfPage);
					} else {
					 	//$this->_pdf->ezSetY( $topOfPage - $this->params['dimensions']['height']);
					}

					if( $this->params['image_vert_pos'] == 'below'){
						$this->params['margin_bottom'] = 40 + $this->params['dimensions']['height'] + $this->params['footer_spacing'] + $this->params['gutter'];
					}
					
					
				} else{
					
					if( $this->params['margin_left'] > $this->params['margin_right']){
						$this->params['margin_left'] = $this->params['margin_right'];
					} else {
						$this->params['margin_right'] = $this->params['margin_left'];
					}
					$this->params['margin_bottom'] = 40 + $this->params['footer_spacing'];
					
				}

		}// end function ----------------------------------------------------------------------------------------------------------------------------------------------- write()

			
		
*/
		/**
		*	adds an image to the page with captions and copyrights
		*
		*	@access		public
		*	@param		string $scott stamp number
		*	@param		float $x
		*	@param		float $y
		*	@return 	string 
		*/
		
		
/*
		public function _addImageToPage(){
					// set image name to easy var
					$topOfPage = $this->_pdf->ezGetY();
					
					$image = $this->params['image_name'];
					$availableWidth = $this->_max_x - $this->params['margin_left'] - $this->params['margin_right'];
					$availableHeight = $topOfPage - $this->params['margin_bottom'];
					
					//if image exists write it to page
					if(file_exists( $image)){
						//get image info
						$imageInfo = @getimagesize( $image);
						$width = $imageInfo[0] / $this->params['image_resolution']; //set width
						$height = $imageInfo[1] / $this->params['image_resolution']; //set height
						$ratio = $height/$width;
						
						
						if( $this->params['force_image_margins']){
							// ensure image is within bounds of margin and resize accordingly
							if( $width > $this->_max_x - ( $this->params['force_image_margins']*2) ){
								$width = $this->_max_x - ( $this->params['force_image_margins']*2);
								$height = $width * $ratio;
							}elseif( $height > $availableHeight ){
								$height = $availableHeight;
								$width = $height * $ratio;
							}
							
						} elseif( $this->params['image_as_column'] && $width > ( $availableWidth / $this->params['image_column_width'])- $this->params['gutter']){
							// force image to 1/3 available space minus gutter if image is too big
							// image cannot exceede 1/3 of available space in column mode.
							$width = ( $availableWidth / $this->params['image_column_width']) - $this->params['gutter'];
							$height = $width * $ratio;
						
						} else {
							// ensure image is within bounds of margin and resize accordingly
							// or if image is to stretch to margins
							if( $this->params['image_stretch_to_margins'] || $width > $availableWidth){
								$width = $availableWidth;
								$height = $width * $ratio;
							}
							
							// ensure that image cannot occupy more than 2/3 of the available space
							if( $height > $availableHeight * 0.66 ){
								$height = $availableHeight * 0.66 ;
								$width = $height / $ratio;
							}
						
						}
						
						
						// set the vertical position of the image
						if( $this->params['image_vert_pos'] =='below'){
							$y = $this->params['margin_bottom'];						
						} else {
							$y = $topOfPage - $height;
						}
						
						// determine horizintal image position
						// center image
						if( $this->params['image_horiz_align'] =='center'){
							$x = ( $this->_max_x/2) - ( $width/2);
						
						// right justified  
						// NOTE MARGINS ARE REVERSED SO THAT THE IMAGE WILL ALWYAYS BE THE DISTANCE FROM THE OPPOSITE MARGIN TO THE IMAGE
						// ON THE POSITIVE MARGIN
						}elseif( $this->params['image_horiz_align'] =='right'){
							$x = $this->_max_x - $width - ( $this->params['force_image_margins']?$this->params['force_image_margins']:$this->params['margin_left']);
						
						// left justified (default)
						} else {
							$x = $this->_min_x + ( $this->params['force_image_margins']?$this->params['force_image_margins']:$this->params['margin_right']);
						}
						
						// add image to the page
						$this->_pdf->addJpegFromFile( $this->params['image_name'], $x, $y, $width);
						
						// reset y to bottom of image if image on text
						if(!$this->params['text_over_image'] && $this->params['image_vert_pos'] != 'below' ){
							$this->_pdf->ezSetY( $topOfPage-$height-$this->params['gutter']);
						}
					
					// recalculate page size
					}
					
					//adds a copyright / credit to the lower right side of the image
					if( $_SESSION['layout']['image_credit']){
						$this->_pdf->setColor(0.6, 0.6, 0.6);
						$caption = $this->_pdf->addTextWrap( $x+$width+2, $y+$height, $height , 4, $this->_cleanText( $_SESSION['layout']['image_credit']), 'left', 90);
						$this->_pdf->setColor(0, 0, 0);
					}
					
					// adds photo caption
					if( $_SESSION['layout']['image_caption']){
						$caption_height = $this->_pdf->getfontHeight( $this->params['caption_font_size']);
						//$this->_pdf->ezSetY( $y);
						$lines = 0;
						$caption = "<i>".$_SESSION['layout']['image_caption']."</i>";
						$captionY = $y - $caption_height;
						// iterate incase the text is longer than the image
						while( $caption){
							$caption = $this->_pdf->addTextWrap( $x, $captionY, $width ,$this->params['caption_font_size'], $caption, 'full');
							$lines++;
							$captionY = $captionY - $caption_height;
						}	
						// set image height to include the caption as well
						$height = $height + ( $caption_height * ( $lines-1));
						$this->_pdf->ezSetY( $this->_pdf->ezGetY() - ( $caption_height * ( $lines-1)));
					}
					
					
					// return image dimensions
					return array('height'=>$height, 'width'=> $width);

		}// end function ----------------------------------------------------------------------------------------------------------------------------------------------- write()
		
*/
		
		
				
		/**
		*	writes a block of text around an image in columns
		*
		*	@access		public
		*	@param		string $scott stamp number
		*	@param		float $x
		*	@param		float $y
		*	@return 	string 
		*/
		
		/*

		public function _writeTextColumn( $caption, $xStart, $xEnd, $yStart, $totalHeight, $max_y = NULL){
		
						
						// clean text up
						$caption = $this->_cleanText( $caption);
						
						// get font dimensions
						$height = $this->_pdf->getFontHeight( $this->params['default_font_size']);

						if( $this->params['image_name'] && // if there is an image
						   $this->params['image_as_column'] && // image is set to be a column
						   $this->params['image_vert_pos'] == 'below'){
						// adjust starting position of the text
								$x = $this->params['margin_right'];						
						} else {
								$x = $xStart;
						}
						
						// create drop cap and eleminate first char off of string
						if( $this->params['dropcaps'] == true){
						
							$cap = strtoupper(substr( $caption, 0, 1));// make sure letter is caps
							$caption = substr( $caption, 1);
							$size = $this->params['default_font_size'] * $this->params['dropcap_height'];// determine height of drop cap
							
							$this->_pdf->selectFont(__ROOT__.'/../fonts/'.$this->params['title_font'].'.afm');
				
							$capHeight = $this->_pdf->getFontHeight( $size) + $this->_pdf->getFontDecender( $size);
							$capWidth = $this->_pdf->getTextWidth( $size, $cap) + 3;
							// write the title
							$this->_pdf->addText( $x, $yStart - ( $capHeight *0.8), $size, $cap);
				
						}
						
						
						// reset font
						$this->_pdf->selectFont(__ROOT__.'/../fonts/'.$this->params['default_font'].'.afm');
						
						$yStart = $yStart - $height - $this->_pdf->getFontDecender( $this->params['default_font_size']);
						$y = $yStart;// adjust for font height
						
						
						// if there is a drop cap/ write the first lines before using easy text
						if( $this->params['dropcaps'] == true){
								$start = $x + $capWidth;
								$width = $xEnd-$x-$capWidth;
						} else {
								$start = $x;
								$width = $xEnd-$x;
						}// end 
								
						while(!empty( $caption)){
						
							$this->_pdf->transaction('start');
							// if text is below the dropcap move to the margin
							if( $y >= $yStart - $capHeight){
							// wrap text around image and bring text to left margin based on right margin size
							} elseif( $this->params['image_name'] && // if there is an image
									 $this->params['image_as_column'] && // image is set to be a column
									 $this->params['image_vert_pos'] =='above' && // image is set align at the top
									 $y < $yStart - $this->params['dimensions']['height']-$this->params['gutter']){
								$start = $this->params['margin_right'];
								$width = $xEnd - $start;
							} elseif( $this->params['image_name'] && // if there is an image
									 $this->params['image_as_column'] && // image is set to be a column
									 $this->params['image_vert_pos'] == 'below' && // image is set align at the top
									 $y > $this->params['dimensions']['height']+$this->params['gutter']+$this->params['margin_bottom']){
								$start = $this->params['margin_right'];
								$width = $xEnd - $start;
							} elseif( $this->params['word_wrap'] && $y < $yStart - $totalHeight - $this->params['gutter']){
								$start = $this->params['margin_left'];
								$width = $this->_max_x - $this->params['margin_left']-$this->params['margin_right'];
							} else {
								$start = $xStart;
								$width = $xEnd - $xStart;
							}
												 
							
							
							
							$remainder = $this->_pdf->addTextWrap( $start, 
															  $y ,
															  $width,
															  $this->params['default_font_size'],
															  $caption,
															  $this->params['text_justify']);	
							//capture first line of text
							$string = @implode('', @explode ( $remainder, $caption, 2));
							
							// check to see if it contains new lines
							$nl = @explode("\n", $string);
							// newlines have been found
							if(count( $nl)>1){
								$this->_pdf->transaction('abort');
								// drop the first element of the array and add a text line
								$this->_pdf->addText( $start, $y , $this->params['default_font_size'], array_shift( $nl));													
								// regroup the remaining pieces and add it to the caption
								$caption = @implode("\n", $nl).$remainder;
							} else {
								$this->_pdf->transaction('commit');
								$caption = $remainder;
							}
								
							// if the next line will be below the bottom margin, bring the text up under the image
							if( $y - $height < $this->params['margin_bottom'] || ( $max_y > 0 &&  $y - $height < $max_y)){
								break;
							}
							
							$y -= $height;
						}// end while

					$this->_pdf->ezSetY( $y);	
					return $caption;	
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
	
*/	

		
		
} // end class
?>
