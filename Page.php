<?php  

/**
 *	Base class for the Stamp album professional that generates high quality pdf
 *	pages for stamp albums
 *
 *
 * @package    		Stamp Album Pro
 * @subpackage   	Album
 * @class    		Page
 * @version			1.0
 * @author			Dexter Oddwick <dexter@oddwick.com>
 * @copyright  		Copyright (c)2017
 * @license    		http://www.oddwick.com
 *
 * @todo    		
 *
 */




class Page extends Core {
		
		/**
		*	@var	array $params
		*	@var	array $_versionInfo
		*/
		protected $max_x = 0;
		protected $max_y = 0;
		protected $_page_number = NULL;
		protected $_data = array(
				"title" => array( "x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
				"subtitle" => array("x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
				"header" => array("x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
				"footer" => array("x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
				"page_number" => array("x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
				"field" => array("x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
				"margin" => array("x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
		);


		/**
		*	Constructor initiates Page object
		*
		*	@access		public
		*	@param		int $stamp_id
		*	@return 	void
		*/
		public function __construct( $page_number = NULL ){
			
				#	transfer param and pdf to internal variables
				#  this line is required in all classes
				parent::__construct();


				# sets the boundries for the page
				$this->max_x = $this->params->max_x;
				$this->max_y = $this->params->max_y;
				$this->_page_number = $page_number;
				
				
				#sanitize user contributed text
				$this->params->title = $this->_cleanText( $this->params->title );
				$this->params->subtitle = $this->_cleanText( $this->params->subtitle );
				$this->params->header = $this->_cleanText( $this->params->header );
				$this->params->footer = $this->_cleanText( $this->params->footer );
				
				if( $this->params->autocaps ){
					$this->params->title = ucwords( $this->params->title );
					$this->params->subtitle = ucwords( $this->params->subtitle );
				}
				
				#calculate the element dimensions and locations
				$this->_preCalc();
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
	
	
	
		/**
		*	allows other classes to poll the page for dimensions.  
		*	if no $key is specified, then the entire array is returned
		*
		*	@access		public
		*	@param	 	string $key
		*	@return 	mixed
		*/
		public function field( $key = "" ){
			
			switch( strtolower( $key ) ){
				
				case "min_x":
				case "x":
					return $this->field_min_x;

				case "min_y":
				case "y":
					return $this->field_min_y;

				case "max_x":
				case "x":
					return $this->field_max_x;

				case "max_y":
				case "y":
					return $this->field_max_y;

				case "w":
				case "width":
				case "max_w":
				case "max_width":
					return $this->field_max_width;

				case "h":
				case "height":
				case "max_h":
				case "max_height":
					return $this->field_max_height;

				# if no key is given or key is not matched, then 
				# the entire field array is returned and the class
				# can sort it out.
				default:
					return $this->_data["field"];
			}
			
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------

		


		/**
		*	creates a page adding border and header and footer
		*
		*	@access		public
		*	@param	 	bool $append flag whether page is appended
		*	@return 	array data containing page size is returned
		*/
		public function page( $append = false ){
			
				#Extracts vars to current view scope
				extract( $this->_data );
			
				# inserts new page
				if( $append ) $this->_pdf->ezNewPage();
				
				# write the border
				$border = Nerb::fetch( "border" );
				$border->write();
				
				# reset the styles to default
				$this->_reset();			
								
				# reset margins for the title
				# force title and text margins regardless of what user sets margins at
				$this->_pdf->ezSetmargins( 0, 0, 400, 40 ); 
				
										  				
				# set initial color to black
				$this->_pdf->setColor( 0, 0, 0 );
				
				
				# write the page numbers if page numbering is set (non 0) and increment it
				if( $this->params->page_number > 0){
					$this->_pdf->addText( $page_number["x"], 
										  $page_number["y"], 
										  $this->params->system_font_size, 
										  $this->params->page_number + $this->_page_number
										);
					#increment the page number					
					$this->params->page_number++;
				}
				
				
				# force text content regardless of what user sets margins at
				$this->_pdf->ezSetmargins( 0, 
										   0, 
										   $this->params->margin + $this->params->margin_adjust, 
										   $this->params->margin 
										  ); 
				
				
				# write the footer
				if( $this->params->footer && ( $this->_page == 0 || footer_repeat==true) ){
					
					$this->_pdf->selectFont( RESOURCES.'/fonts/'.$this->params->default_font.'.afm' );
					
					#sets the cursor position as bottom margin + the footer height + the footer spacing 
					$this->_pdf->ezSetY( $footer["y"] + $footer["height"] - $this->_pdf->getFontDecender(  $this->params->default_font_size ) );
					$this->_pdf->ezText( $this->params->footer,
										 $this->params->default_font_size, 
										 array('left'=>0, 'right'=>0, 'justification'=>$this->params->text_justify)
										);
					
					# sets the new margin to exclude the footer
					# $this->params->margin_bottom = $this->params->margin_bottom + $footer + $this->params->footer_spacing + $this->params->gutter;
					
				}


				# reset the cursor to the top of the page
				# top of the page is defined as:
				#
				#	max_y - margin_top - [title font height]
				#
				
				# write the title
				if( $this->params->title){
					$this->_pdf->ezSetY( $title["y"]  + $title["height"] - $this->_pdf->getFontDecender(  $this->params->title_font_size ) );
					$this->_pdf->selectFont( RESOURCES.'/fonts/'.$this->params->title_font.'.afm' );
					$this->_pdf->ezText( $this->params->title, 
										 $this->params->title_font_size, 
										 array( 'justification' => 'center' ) 
										 );
				}
				
				
				# reset font for subtitle and text
				$this->_pdf->selectFont( RESOURCES.'/fonts/'.$this->params->default_font.'.afm' );
				
				
				# write the subtitle if given 
				if( $this->params->subtitle){
					$this->_pdf->ezSetY( $subtitle["y"] + $subtitle["height"] - $this->_pdf->getFontDecender(  $this->params->subtitle_font_size ) );
					$this->_pdf->ezText( $this->params->subtitle, 
										 $this->params->subtitle_font_size, 
										 array('justification'=>'center')
										);
				}
				
				
				# write textblock under title or subtitle if given
				# will only write if: 
				#		text is present AND
				#		page is first page OR
				# 		repeating of text is on
				if( $this->params->header && ( $this->_page == 0 || $this->params->header_repeat==true ) ){
					$this->_pdf->ezSetY( $header["y"] + $header["height"] - $this->_pdf->getFontDecender(  $this->params->default_font_size ) );
					$this->_pdf->ezText( $this->params->header,
										 $this->params->default_font_size, 
										 array('left'=>0, 'right'=>0, 'justification'=>$this->params->text_justify )
										);
				}
				
				
				# set y to top of available space with
				# mandatory header spacing of 20px
				$y = $this->_pdf->ezGetY() - $this->params->header_spacing - 20;
				
				
#TODO: finish upload images @Dexter Oddwick [11/20/17]			
				/*
				if( $_FILES['imageUpload']['tmp_name'] && ( $this->params->image_repeat || !$insert) ){
					// add image to page and calculate remaining space
					$y = ( $yhold = $this->_addImageToPage( $y))?$yhold:$y;
				}			
				*/
				
				
				
				# get the new dimensions of the working area after everything has been placed on page
				# wrap it up in an array, and pass it back to the Album for stamp placement
				# sets the margin data so that margins can be seen
				$this->field_min_x =  $this->params->margin_left + $this->params->margin_adjust;
				$this->field_max_x = $this->max_x - $this->params->margin_right;
				$this->field_min_y = $field["y"];
				$this->field_max_y = $field["y"] + $field["height"];
				$this->field_max_width = $field["width"];
				$this->field_max_height = $field["height"];
				$this->field_page_height = $this->params->max_y;
				$this->field_page_width = $this->params->max_x;
							
				
				# if debugging mode is on, then print borders around everything			
				if( $this->params->debug == "ALL" ||  $this->params->debug == "page" ){
					$this->_debug( $this->_data );
				}			
							
				//Nerb::inspect( $data, true );			
				return $this->_data["field"];
			
		
		}// end function ----------------------------------------------------------------------------------------------------------------------------------------------- 



		/**
		*	adds an image to the page with captions and copyrights
		*
		*	@access	public
		*	@return string 
		*/
		public function _addImageToPage( $topOfPage ){
		
		
		
					// if no image, return false
					if(!$image = $_FILES['imageUpload']['tmp_name']) return false;
		
					// get available width
					$availableWidth = $this->max_x - ( $this->params->margin * 2) - $this->params->margin_adjust;
					// get available height which cannot exceed 1/2 the page (need room for the stamps!)
					$availableHeight = ( $topOfPage - $this->params->margin_bottom)/2;
					
					//if image exists write it to page and is a jpeg image
					if(file_exists( $image) &&  $_FILES['imageUpload']['type'] =='image/jpeg'){
						//get image info
						$imageInfo = @getimagesize( $image);
						$width = $imageInfo[0]; //set width
						$height = $imageInfo[1]; //set height
						$ratio = $height/$width;
						
		
						
						if( $this->params->force_image_margins){
							// ensure image is within bounds of margin and resize accordingly
							if( $width > $this->max_x - ( $this->params->force_image_margins*2) - $this->params->margin_adjust){
								$width = $this->max_x - ( $this->params->force_image_margins*2)  - $this->params->margin_adjust;
								$height = $width * $ratio;
							}elseif( $height > $availableHeight ){
								$height = $availableHeight;
								$width = $height * $ratio;
							}
							
						} else {
							
							// ensure image is within bounds of margin and resize accordingly
							// or if image is to stretch to margins
							if( $this->params->image_stretch_to_margins || $width > $availableWidth){
								$width = $availableWidth * ( $this->params->image_stretch_to_margins > 0 ? $this->params->image_stretch_to_margins:1);
								$height = $width * $ratio;
							}
							
							// ensure that image cannot occupy more than 2/3 of the available space
							if( $height > $availableHeight * 0.66 ){
								$height = $availableHeight * 0.66 ;
								$width = $height / $ratio;
							}
						
						}
						
						// set the vertical position of the image
						if( $this->params->image_vert_pos =='below'){
							$y = $this->params->margin_bottom;						
						} else {
							$y = $topOfPage - $height - $this->params->header_spacing;
						}
						
						//calculate x as the center of the page
						$x = ( $this->max_x/2) - ( $width/2) + ( $this->params->margin_adjust/2);
						
					
						// add image to the page
						$this->_pdf->addJpegFromFile( $image, $x, $y, $width);
						
					// recalculate page size
					}
					
					//adds a copyright / credit to the lower right side of the image
					if( $this->params->image_credit){
						$this->_pdf->setColor(0.6, 0.6, 0.6);
						$caption = $this->_pdf->addTextWrap( $x+$width+2, $y+$height, $height , 4, $this->_cleanText( $this->params->image_credit), 'left', 90);
						$this->_pdf->setColor(0, 0, 0);
					}
					
					// adds photo caption
					if( $this->params->image_caption){
						$caption_height = $this->_pdf->getfontHeight( $this->params->caption_font_size);
						//$this->_pdf->ezSetY( $y);
						$lines = 0;
						$caption = "<i>".$this->params->image_caption."</i>";
						$captionY = $y - $caption_height;
						// iterate incase the text is longer than the image
						while( $caption){
							$caption = $this->_pdf->addTextWrap( $x, $captionY, $width ,$this->params->caption_font_size, $caption, 'full');
							$lines++;
							$captionY = $captionY - $caption_height;
						}	
						// set image height to include the caption as well
						$height = $height + ( $caption_height * ( $lines-1));
						$this->_pdf->ezSetY( $this->_pdf->ezGetY() - ( $caption_height * ( $lines-1)));
					}
					
					
					// return image dimensions
					return $y; //array('height'=>$height, 'width'=> $width);

		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------

		
		
		
				
		/**
		*	writes a block of text around an image in columns
		*
		*	@access		public
		*	@param		string $scott stamp number
		*	@param		float $x
		*	@param		float $y
		*	@return 	string 
		*/
		public function _writeTextColumn( $caption, $xStart, $xEnd, $yStart, $totalHeight, $max_y = NULL){
		
						
						// clean text up
						$caption = $this->_cleanText( $caption);
						
						// get font dimensions
						$height = $this->_pdf->getFontHeight( $this->params->default_font_size);

						if( $this->params->image_name && // if there is an image
						   $this->params->image_as_column && // image is set to be a column
						   $this->params->image_vert_pos == 'below'){
						// adjust starting position of the text
								$x = $this->params->margin_right;						
						} else {
								$x = $xStart;
						}
						
						// create drop cap and eleminate first char off of string
						if( $this->params->dropcaps == true){
						
							$cap = strtoupper(substr( $caption, 0, 1));// make sure letter is caps
							$caption = substr( $caption, 1);
							$size = $this->params->default_font_size * $this->params->dropcap_height;// determine height of drop cap
							
							$this->_pdf->selectFont(RESOURCES.'/fonts/'.$this->params->title_font.'.afm');
				
							$capHeight = $this->_pdf->getFontHeight( $size) + $this->_pdf->getFontDecender( $size);
							$capWidth = $this->_pdf->getTextWidth( $size, $cap) + 3;
							// write the title
							$this->_pdf->addText( $x, $yStart - ( $capHeight *0.8), $size, $cap);
				
						}
						
						
						// reset font
						$this->_pdf->selectFont(RESOURCES.'/fonts/'.$this->params->default_font.'.afm');
						
						$yStart = $yStart - $height - $this->_pdf->getFontDecender( $this->params->default_font_size);
						$y = $yStart;// adjust for font height
						
						
						// if there is a drop cap/ write the first lines before using easy text
						if( $this->params->dropcaps == true){
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
							} elseif( $this->params->image_name && // if there is an image
									 $this->params->image_as_column && // image is set to be a column
									 $this->params->image_vert_pos =='above' && // image is set align at the top
									 $y < $yStart - $this->params->dimensions['height']-$this->params->gutter){
								$start = $this->params->margin_right;
								$width = $xEnd - $start;
							} elseif( $this->params->image_name && // if there is an image
									 $this->params->image_as_column && // image is set to be a column
									 $this->params->image_vert_pos == 'below' && // image is set align at the top
									 $y > $this->params->dimensions['height']+$this->params->gutter+$this->params->margin_bottom){
								$start = $this->params->margin_right;
								$width = $xEnd - $start;
							} elseif( $this->params->word_wrap && $y < $yStart - $totalHeight - $this->params->gutter){
								$start = $this->params->margin_left;
								$width = $this->max_x - $this->params->margin_left-$this->params->margin_right;
							} else {
								$start = $xStart;
								$width = $xEnd - $xStart;
							}
												 
							
							
							
							$remainder = $this->_pdf->addTextWrap( $start, 
															  $y ,
															  $width,
															  $this->params->default_font_size,
															  $caption,
															  $this->params->text_justify);	
							//capture first line of text
							$string = @implode('', @explode ( $remainder, $caption, 2));
							
							// check to see if it contains new lines
							$nl = @explode("\n", $string);
							// newlines have been found
							if(count( $nl)>1){
								$this->_pdf->transaction('abort');
								// drop the first element of the array and add a text line
								$this->_pdf->addText( $start, $y , $this->params->default_font_size, array_shift( $nl));													
								// regroup the remaining pieces and add it to the caption
								$caption = @implode("\n", $nl).$remainder;
							} else {
								$this->_pdf->transaction('commit');
								$caption = $remainder;
							}
								
							// if the next line will be below the bottom margin, bring the text up under the image
							if( $y - $height < $this->params->margin_bottom || ( $max_y > 0 &&  $y - $height < $max_y)){
								break;
							}
							
							$y -= $height;
						}// end while

					$this->_pdf->ezSetY( $y);	
					return $caption;	
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------




		/**
		*	function that calculates all of the dimensions of the element an places the values
		*	into the $_data array for easy access.  
		*
		*	with the exceptions of fixed elements such as page numbers, this will run a series of transatcions
		*	to determine the dimensiions and then roll back transactions to clear page
		*
		*	@access		public
		*	@return 	Object Self 
		*/
		protected function _preCalc(){ 
			
			

				# -------------------------------- PAGE MARGINS --------------------------------------------
				# calculate the actual page area
				# select title font
				$this->margin_x = $this->params->margin_left + $this->params->margin_adjust; 
				$this->margin_y = $this->params->margin_bottom; 
				$this->margin_width = $this->max_x - $this->params->margin_right - $this->margin_x; 
				$this->margin_height = $this->max_y - $this->params->margin_bottom - $this->params->title_margin;
				
				
				
				
				# -------------------------------- TITLE --------------------------------------------
				# calculate the title size
				#	-- the title and subtitle must be calculated before the header in order that
				#	   their dimension can be used to get a starting location for the header
				if( $this->params->title){
					
					# select title font
					$this->_pdf->selectFont( RESOURCES.'/fonts/'.$this->params->title_font.'.afm' );
					
					# calculate width and height based on font and text
					#	-- height and width are obvious
					#	-- x is [1/2  max_x - width]  which will force the title to be centered
					#	-- y is top of the page - top margin  
					$this->title_width = $this->_pdf->getTextWidth( $this->params->title_font_size, $this->params->title ); 
					$this->title_height = $this->_pdf->getFontHeight( $this->params->title_font_size ); 
					$this->title_x = ( $this->max_x - $this->title_width) / 2; 
					$this->title_y = $this->max_y 
								   - $this->params->title_margin 
								   - $this->title_height; 
					
				}  // end if
				
				
				
				# -------------------------------- SUBTITIE --------------------------------------------
				# calculate the subtitle size and location 
				if( $this->params->subtitle ){
					# reset font for subtitle and text
					$this->_pdf->selectFont( RESOURCES.'/fonts/'.$this->params->default_font.'.afm' );

					$this->subtitle_width = $this->_pdf->getTextWidth( $this->params->subtitle_font_size, $this->params->subtitle ); 
					$this->subtitle_height = $this->_pdf->getFontHeight( $this->params->subtitle_font_size ); 
					$this->subtitle_x = ( $this->max_x - $this->subtitle_width) / 2; 
					$this->subtitle_y = $this->max_y 
									  - $this->params->title_margin 
									  - $this->title_height 
									  - $this->params->title_spacing
									  - $this->subtitle_height; 
				} // end if
				
				
				
				# -------------------------------- HEADER --------------------------------------------
				# calculate the dimensions of the header
				if( $this->params->header ){
					
					$this->header_width = $this->max_x - ( $this->params->margin * 2 ) - $this->params->margin_adjust;
					$this->header_height = $this->getTextHeight( $this->params->header, 
												   	 $this->header_width, 
												   	 $this->params->default_font_size, 
												   	 $this->params->text_justify 
												   	);
					# set data block for header
					$this->header_x = $this->params->margin + $this->params->margin_adjust;
					$this->header_y = $this->max_y 
									- $this->params->title_margin 
									- $this->title_height 
									- $this->params->title_spacing
									- $this->subtitle_height 
									- $this->params->subtitle_spacing
									- $this->header_height; 
				} // end if
				
				
				
				# -------------------------------- FOOTER --------------------------------------------
				# begin footer calculations
				if( $this->params->footer ){
					
					$this->footer_width = $this->max_x - ( $this->params->margin * 2 ) - $this->params->margin_adjust;
					$this->footer_height = $this->getTextHeight( $this->params->footer, 
												   	 $this->footer_width, 
												   	 $this->params->default_font_size, 
												   	 $this->params->text_justify 
												   	);
					# set data block for footer
					$this->footer_x = $this->params->margin + $this->params->margin_adjust;
					$this->footer_y = $this->params->margin_bottom;
					
				}  // end if
				
			
			
				# -------------------------------- PAGE NUMBER --------------------------------------------
				# this gets the placement and dimensions of the page number
				$this->_data["page_number"] = array(
					"x" => $this->max_x / 2 - ( $this->_pdf->getTextWidth( $this->params->system_font_size, $this->params->page_number + $this->_page_number ) / 2 ),
					"y" => $this->params->page_number_margin,
					"width" => $this->_pdf->getTextWidth( 8, $this->params->page_number + $this->_page_number ),
					"height" => 8				
				);				
				
				
				# -------------------------------- FIELD --------------------------------------------
				# The field is where all of the stamps are placed on the page and is defined as the area
				# remaining after all of the other content (borders, title, header, footer, etc) is placed
				# on the page. 
				$this->field_x = $this->params->margin + $this->params->margin_adjust;
				$this->field_y = $this->params->margin_bottom + $this->footer_height + $this->params->field_spacing;
				$this->field_width = $this->max_x - ( $this->params->margin * 2 ) - $this->params->margin_adjust;
				$this->field_height = $this->max_y										# -------		
												- $this->params->title_margin 			#	
												- $this->title_height 					#	
												- $this->params->title_spacing			#	The combined height of the top elements
												- $this->subtitle_height 				#	
												- $this->params->subtitle_spacing		#	
												- $this->header_height 					#	
												- $this->params->field_spacing			#--------
												- $this->field_y; 						# 	the combined height of the bottom content
					
		} 
		// end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

		
} // end class
?>
