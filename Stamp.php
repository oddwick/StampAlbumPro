<?php  

/**
 *	Base class for the Stamp album professional that generates high quality pdf
 *	pages for stamp albums
 *
 *
 * @package    		Stamp Album Pro
 * @subpackage   	Album
 * @class    		Stamp
 * @version			1.0
 * @author			Dexter Oddwick <dexter@oddwick.com>
 * @copyright  		Copyright (c)2017
 * @license    		http://www.oddwick.com
 *
 * @uses			Title   
 * @requires		table::Stamps   
 * @requires		table::Images   
 * @uses			Title   
 * 		
 * @todo    		
 *
 */





class Stamp extends Core {
		
		/**
		*	@var	array $_stamp
		*	@var	string $_title
		*	@var	array $_data
		*/
		protected $_stamp = array(); // array item containing stamp data
		protected $_title;
		protected $_data = array(
				"innerFrame" => array("x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
				"outerFrame" => array("x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
				"image" => array( "file_name" => "",
								  "x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
				"title" => array( "content" => "",
								  "x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
				"caption" => array( "content" => "",
								  "x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
				"actual" => array("x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
				"gutters" => array("x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
					);






		/**
		 * Constructor initiates Stamp object
		 * 
		 * @access public
		 * @param mixed $stamp_id
		 * @param string $text (default: "")
		 * @param bool $force_width (default: false)
		 * @param bool $force_height (default: false)
		 * @return void
		 */
		public function __construct( $stamp_id, $text = "" ){
		
			# transfer param and pdf to internal variables
			# this line is required in all classes
			parent::__construct();



			# retireve a copy of the stamp table and get stamp information
			if( !$Stamps = Nerb::fetch( "stamps" ) ){
				throw new Nerb_Error( "Database table '<code>stamps</code>' could not be found." );
			}  // end if
			
			elseif( !$Images = Nerb::fetch( "images" ) ){
				throw new Nerb_Error( "Database table '<code>images</code>' could not be found." );
			}// end if
			
	
			#	make sure the stamp is in the stamp database and transfer array to $_stamp
			if( !$this->_stamp = $Stamps->fetchArray( $stamp_id ) ) return FALSE;
			

			# fetch default image and add image name to $_stamp
			$image = $Images->fetchFirstRow( "`stamp_id` = ".$stamp_id." AND `is_default` = 1" );
			$this->_stamp["image_name"] = $image->image_name;
			
			
			# set the file_name of the stamp image
			# remember that this DOES NOT INCLUDE the extension so that a specific
			# image can be picked depending on the context
			# 	no extension (.jpg) - the full sized image
			#	_t.jpg - thumbnail image
			#	_c.jpg - cropped color image
			#	_bw.jpg - cropped black and white image
			//$this->_stamp["stamp_id"] =  $stamp_id;
			$this->image_file_name =  $this->params->directory."/".$this->_stamp["country_id"]."/".$this->_stamp["image_name"];
			$this->image_file_name .= $this->params->stamp_image_style == 2 ? "_c.jpg" : "_bw.jpg";


			# determine if the stamp is going to print with text
/*
			$caption = "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et 
			dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure 
			dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. ";
*/
			
			$this->caption_content = substr( $this->_cleanText( $caption ), 0, $this->params->stamp_max_chars );

			
			# create a title object and add it to the stack
			$this->_title();
			
			# this gets the actual dimensions of the stamp, with border and caption
			$this->_precalc();
			
			

			#set header and stream pdf contents
			//header("Content-type: application/pdf"); 	
			//$this->_pdf->ezStream();
			

# BREAKPOINT @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
	//die;
#@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
				
				return $this;
						
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		

		
		/**
		*	forces the stamp to be certain width
		*
		*	@access		public
		*	@param	 	float $width
		*	@return 	Stamp
		*/
		public function forceWidth( $width ){
					
			// get the ratio of the current width to the new width before it is changed
			$ratio = $this->_stamp["actual_width"] / $width;
			
			// change to the new width
			$this->_stamp["actual_width"] = $width;
			
			//calculate the proprotional height
			$this->_stamp["actual_height"] = $this->_stamp["actual_height"] / $ratio;
			
			// work backwards to calculate the new height and width 
			// based on the new max width
			$this->_stamp["width"] = $this->_stamp["actual_width"] - ( $this->params->stamp_border_padding * 2 );
			$this->_stamp["height"] = $this->_stamp["actual_height"]  - ( $this->params->stamp_border_padding * 2 ) - $this->params->caption_font_size - $this->params->caption_spacing - 5;
			
			return $this;
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		
		/**
		*	forces the stamp to be certain height
		*
		*	@access		public
		*	@param	 	float $height
		*	@return 	Stamp
		*/
		public function forceHeight( $height ){
					
			// get the ratio of the current width to the new width before it is changed
			$ratio = $this->_stamp["actual_height"]/$height;
			
			// change to the new width
			$this->_stamp["actual_height"] = $height;
			//calculate the proprotional height
			$this->_stamp["actual_width"] = $this->_stamp["actual_width"]/$ratio;
			
			// work backwards to calculate the new height and width 
			// based on the new max width
			$this->_stamp["width"] = $this->_stamp["actual_width"] - ( $this->params->stamp_border_padding * 2 );
			$this->_stamp["height"] = $this->_stamp["actual_height"]  - ( $this->params->stamp_border_padding * 2 ) - $this->params->caption_font_size - $this->params->caption_spacing - 5;
						
			return $this;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
			
		
		/**
		*	actually writes the stamp to the pdf with frames and captions
		*
		* 	This function operates under the premise that the mount size of the stamp is constant for 
		*	any given stamp.  the first version of this function based the frame size off of the stamp image,
		*	but there were minor inconsistecies when the page was printed, e.g the mounts didnt fit or some 
		*	frames were larger than others.  if the mount size is known, the rest of the image can extrapolated
		*	from that.  when printed, there is a centering frame that is the actual size of the mount, 
		*	the true frame, which is padded x points larger than the mount, and the image which is a 
		*	percentage of the framing box.
		*
		*	@access		protected
		*	@param		float $x
		*	@param		float $y
		*	@return 	bool 
		*/
		public function _write( $x, $y ){
		
			// fudge factor for offset because of caption
			//$y = $y + ( $this->_data["actual_height"]["height"] - $this->_data["height"] - $this->params->stamp_border_padding);
			
			// adjust x for text blocks
			//$xAdjust = ( $this->_data["actual_width"]["width"] - $this->_data["width"] - $this->params->stamp_border_padding)/2;
			
			# add offsets to origin to give final positions
			$this->_setOrigin( $x, $y );
						
			// create drop shadows before anything else so they are under the image
			//if( $this->params->use_drop_shadows == true){ $this->_use_drop_shadows( $x , $y ); } 
			
			# write out the inner frame
			$this->_innerFrame( $x, $y );

			# write out the outer frame
			$this->_outerFrame( $x, $y );

			$this->_writeTitle( $x, $y );
			
			$this->_writeCaption();
			
			// if image exists and these are not blank frames
			if( $this->params->stamp_image_style == 1 && file_exists( $this->image_file_name ) ){ 
				$this->_addImage( $x, $y);					
			}// end if file exists


			if( $this->params->stamp_image_style == 2){
				$this->_writeInternalCaption( $x, $y );
			}else{
				$this->_writeCaption( $x, $y );
			}
			
			# debugging mode on and will print out a square showing the available field size
			if(  $this->params->debug == "ALL" ||  $this->params->debug == "stamp" ){
				$this->_debug( $this->_data );
			}
			
			
			return $this;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		



		/**
		*	returns the actual height of the stamp with all of the fixins
		*
		*	@access		public
		*	@return 	float
		*/
		public function height(){
		
			return $this->actual_height;	
		
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		



		/**
		*	returns the actual width of the stamp with sprinkles
		*
		*	@access		public
		*	@return 	float
		*/
		public function width(){

			return $this->actual_width;	
		
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		



		/**
		*	returns the actual width of the stamp with sprinkles
		*
		*	@access		public
		*	@return 	float
		*/
		public function title(){

			return $this->_stamp["title"];	
		
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		



		/**
		*	creates a caption under the stamp
		*
		*	@access		public
		*	@return 	void
		*/
		protected function _addImage(){
				
			if( !file_exists( $this->image_file_name ) ) return; 	
				
			// add the image to the page 
			$this->_pdf->addJpegFromFile( $this->image_file_name, 
										  $this->image_x, 
										  $this->image_y, 
										  $this->image_width, 
										  $this->image_height);
									 
			// add a diagonal slash in lower left hand corner to "cancel" stamp per USPS regs 
			// when reproducing stamp in color
			if( $this->params->use_stamp_cancel){
				
				# will draw the cancellation based on the shortest dimension
				# --horizontal stamps
				if( $this->image_width > $this->image_height ){
					$offset = $this->image_height * $this->params->stamp_cancel_percent;
				} 
				
				# --vertical stamps
				else {
					$offset = $this->image_width * $this->params->stamp_cancel_percent;
				}
				
				# this is a fudge factor to make sure that the cancellation extends past the image and 
				# gives it a clean break at the borders
				$overbite = 2;
			
				$this->_pdf->setStrokeColor(1,1,1);
				$this->_pdf->setLineStyle(1);
				$this->_pdf->line(	$this->image_x - $overbite, 
									$this->image_y + $offset - $overbite,
									$this->image_x + $offset - $overbite, 
									$this->image_y  - $overbite
								);
			 }// end if cancel stamps
			 

		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------




		/**
		*	creates a caption string
		*
		*	@access		protected
		*	@return 	string
		*/
		protected function _title(){
				
			$title = new Title( $this->_stamp );
			$title = $title->__toString();
			$this->title_name = $this->_stamp["title"];
			return $this->title_content = $title;
					
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	



			
		/**
		*	adds a block of text under the stamp
		*
		*	@access		protected
		*	@param		string $text
		*	@param		float $x
		*	@param		float $y
		*	@return 	void
		*/
		protected function _text( $text, $x, $y ){

			// for legibility purposes, the default caption font is always helvetica and black
			$this->_reset();			
			
			// set the margins as the actual width
			$this->_pdf->ezSetMargins( 0,
									   0,
									   $x,
									   $this->params->max_x - $x - $this->_stamp["actual_width"]
									);
				
			$this->_pdf->ezSetDy( $this->params->caption_spacing * 2 );
			$this->_pdf->ezText( $text, 
								 $this->params->caption_font_size, 
								 array( "justification" => $this->params->text_justify ) 
								);
				
					
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		
		/**
		*	draws the inner frame of the stamp
		*
		*	@access		protected
		*	@param		float $x
		*	@param		float $y
		*	@return 	void
		*/
		protected function _innerFrame(){

			# reset the line style
			$this->_reset();
			
			# create the centering border which will be light gray and the actual mount size
			$this->_pdf->setStrokeColor( $this->params->stamp_inner_frame_color, 
										 $this->params->stamp_inner_frame_color, 
										 $this->params->stamp_inner_frame_color 
									);
			
			$this->_pdf->setLineStyle( $this->params->stamp_inner_border_size );
			
			# create a rectangle and set x,y for debugging
			$this->_pdf->rectangle( $this->innerFrame_x, 
									$this->innerFrame_y, 
									$this->innerFrame_width, 
									$this->innerFrame_height
								);
					
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		
		/**
		*	draws the outer frame of the stamp
		*
		*	@access		protected
		*	@param		float $x
		*	@param		float $y
		*	@return 	void
		*/
		protected function _outerFrame(){

			# reset the line style
			$this->_reset();
			
			# frame line style
			$this->_pdf->setLineStyle( $this->params->stamp_outer_border_size );
			
			# the actual frame is x number of points (stamp_border_padding) larger than the mount itself
			$this->_pdf->setStrokeColor( $this->params->stamp_border_color, 
										 $this->params->stamp_border_color, 
										 $this->params->stamp_border_color 
									);
			
			# write out outer frame
			$this->_pdf->rectangle( $this->outerFrame_x, 
									$this->outerFrame_y, 
									$this->outerFrame_width, 
									$this->outerFrame_height
								);
					
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
#TODO: create fancy frames @Dexter Oddwick [12/14/17]		
		/**
		*	creates an ornate stamp frame
		*
		*	@access		protected
		*	@param		float $x
		*	@param		float $y
		*	@return 	void
		*/
		protected function _fancyFrame(){

			# frame line style
			$this->_pdf->setLineStyle( $this->params->stamp_outer_border_size );
			
			# the actual frame is x number of points (stamp_border_padding) larger than the mount itself
			$this->_pdf->setStrokeColor( $this->params->stamp_border_color, 
										 $this->params->stamp_border_color, 
										 $this->params->stamp_border_color 
									);
			
			# write out outer frame
			$this->_pdf->rectangle( $this->outerFrame_x, 
									$this->outerFrame_y, 
									$this->outerFrame_width, 
									$this->outerFrame_height
								);
					
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		
		/**
		*	adds a block of text under the stamp
		*
		*	@access		protected
		*	@param		float $x
		*	@param		float $y
		*	@return 	void
		*/
		protected function _writeCaption(){
							
			// reset color and stroke
			$this->_reset();			
			
			//$caption = $this->_caption();
			
			$this->_pdf->ezSetY( $this->caption_y + $this->caption_height );
			
			$this->_pdf->ezText( $this->caption_content, 
								 $this->params->caption_font_size, 
								 array( "justification" =>"left",
								 		"aleft" => $this->caption_x,
								 		"aright" => $this->caption_x + $this->outerFrame_width
								  ) 
							);
				
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	


			
#TODO: finish this vertical title @Dexter Oddwick [12/14/17]		
		/**
		*	writes a title 90º to the stamp
		*
		*	@access		protected
		*	@param		float $x
		*	@param		float $y
		*	@return 	void
		*/
		protected function _writeVerticalTitle( $x, $y ){
				
			// reset color and stroke
			$this->_reset();
			
			// adds vertical caption to bottom of image
			$this->_pdf->addText( $x+$this->_stamp["$totalWidth"]+3, 
								  $y + $this->_pdf->getTextWidth( $this->params->caption_font_size, $caption), 
								  $this->params->caption_font_size, 
								  $caption, 
								  90 // rotation
								 );
			// adds vertical caption to top of image (alternate)
			//$this->_pdf->addText( $x+$totalWidth+3, $y + $totalHeight, $this->params->caption_font_size, $caption, 90);
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	


			
		
		/**
		*	writes out the title to the stamp
		*
		*	@access		protected
		*	@param		float $x
		*	@param		float $y
		*	@return 	void
		*/
		protected function _writeTitle(){
			
			// reset color and stroke
			$this->_reset();			
			
			$this->_pdf->ezSetY( $this->title_y + $this->title_height );
			
			$this->_pdf->ezText( $this->title_content, 
								 $this->params->caption_font_size, 
								 array( "justification" =>"centre",
								 		"aleft" => $this->title_x,
								 		"aright" => $this->title_x + $this->outerFrame_width
								  ) 
							);
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	


			
		
		/**
		*	adds a block of text under the stamp
		*
		*	@access		protected
		*	@param		float $x
		*	@param		float $y
		*	@return 	void
		*/
		protected function _writeInternalCaption( $x, $y ){
				
			// reset color and stroke
			// for legibility purposes, the default caption font is always helvetica and black
			$this->_pdf->setColor( 0, 0, 0 );
			$this->_pdf->setLineStyle(1);
			$this->_pdf->selectFont( RESOURCES."/fonts/Helvetica.afm" );
			
			$captions = explode(", ", $this->caption());
			$fontHeight = $this->_pdf->getFontHeight( $this->params->caption_font_size);
			
			// set the margins as image width
			$this->_pdf->ezSetMargins(0,0, $x + 10, $this->params->max_x - $x - $this->_data["actual_width"] + 10 );
			
			// figure out the actual height of the stamps
			$this->_pdf->transaction("start");
				$this->_pdf->ezSetY( $y );
				for( $i = 0; $i < count( $captions); $i++){
					$captions[$i] = $i < 1?"<b>".$captions[$i]."</b>":"<i>".$captions[$i]."</i>";
					$this->_pdf->ezText( $captions[$i], $this->params->caption_font_size, array("justification" =>"center") );
				}
				$height = $y - $this->_pdf->ezGetY();
			$this->_pdf->transaction("abort");
			
			$this->_pdf->ezSetY( $y + $this->_data["actual_height"] - ( $this->_data["actual_height"] - $height)/2  );
			
			foreach( $captions as $caption){
				$this->_pdf->ezText( $caption, $this->params->micro_font_size, array("justification" =>"center") );
			}
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		
		
		/**
		*	calculates the actual print dimensions of the stamp and locations of the stamp.  this function does all of the heavy lifting
		*
		*	@access		protected
		*	@return 	Stamp 
		*	@throws		Nerb_Error 
		*/
		protected function _precalc(){
			
			/*	
			 the dimensions of the stamp are 
			 Mount size in MM * MM to points  the mount size is fixed and constant.
			*/
			# if no stamp is defined, then 
			if( !$this->_stamp["height"] || !$this->_stamp["width"] ){
				$this->innerFrame_height = ( $this->params->default_stamp_height * $this->params->mm2point );
				$this->innerFrame_width = ( $this->params->default_stamp_width * $this->params->mm2point );
			} else {
				$this->innerFrame_height = ( $this->_stamp["height"] * $this->params->mm2point );
				$this->innerFrame_width = ( $this->_stamp["width"] * $this->params->mm2point );
			}
			
			#define outer frame as inner frame + padding
			$this->outerFrame_height = $this->innerFrame_height + ( 2 * $this->params->stamp_border_padding );
			$this->outerFrame_width =  $this->innerFrame_width + ( 2 * $this->params->stamp_border_padding );
			
			

			# fetch the image file and get its dimensions
			if( file_exists( $this->image_file_name ) ){
				
				$file = getimagesize( $this->image_file_name);
				$this->image_actual_width = $this->image_width = $file[0] / $this->params->mm2point;
				$this->image_actual_height = $this->image_height = $file[1] / $this->params->mm2point;
				
				
				# shrink image to fit inside of frame as a percentage of the frame by the largest dimension
				# --if width is greater
				if( $this->innerFrame_width > $this->innerFrame_height){
					
					#the width becomes the primary  dimension
					# calculate the height/width ratio
					$this->image_width = $this->innerFrame_width *  $this->params->stamp_image_padding;
					
					# calculate the height/width ratio
					$ratio = $this->image_width / $this->image_actual_width;
					
					# reduce the width by the ratio and reduce by padding percentage
					$this->image_height = $this->image_height * $ratio;
					
				} else {
					
					#the height is the primary  dimension
					# reduce the height by padding percentage
					$this->image_height = $this->innerFrame_height *  $this->params->stamp_image_padding;
					
					# calculate the height/width ratio
					$ratio = $this->image_height / $this->image_actual_height;
					
					# reduce the width by the ratio and reduce by padding percentage
					$this->image_width = $this->image_width * $ratio;
				} // end if

			} // end if
		
			# calculate the actuals include the caption and spacing and frame
			#
			# ! this is the most important number because this is what is returned when calculating
			# stamp placement and whether or not it will fit on the page
			$this->actual_width = $this->outerFrame_width;
			$this->actual_height = $this->outerFrame_height;
			

			# title calculations
			if( $this->title_content ){
				$this->title_height = $this->getTextHeight( $this->title_content, $this->outerFrame_width, $this->params->caption_font_size );
				$this->title_width = $this->outerFrame_width;
				
				# add title to actual height
				$this->actual_height += ( $this->params->caption_spacing + $this->title_height );
			}
				
				
			// if the stamp has a text block with it, calculate the height and width;
			if( $this->caption_content ){
				$this->caption_height = $this->getTextHeight( $this->caption_content, $this->outerFrame_width, $this->params->caption_font_size );
				$this->caption_width = $this->outerFrame_width;
				
				# add caption to actual height
				$this->actual_height += ( $this->params->caption_spacing + $this->caption_height );
			} // end if text
		
			# add padding on top of the actual width so that it is already figured in
			# to the total dimensions
			$this->gutters_height = $this->actual_height + ( 2 * $this->params->gutter );
			$this->gutters_width = $this->actual_width + ( 2 * $this->params->gutter );
			
			$this->_offsets();
			
			//Nerb::inspect( $this->_data, true );
			return $this;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		
		
		
		/**
		*	calculates the offsets for each of the stamp elements and adds them to $_data array
		*	-- the actuals become (0,0) and the rest of the elements are placed by default relative
		*	   to that starting point.  
		*
		*	@access		protected
		*	@return 	void
		*/
		protected function _offsets(){
		
			# actuals start as (0,0)
			# gutters = actuals - gutters
			$this->_data["gutters"]["x"] = 0-$this->params->gutter;
			$this->_data["gutters"]["y"] = 0-$this->params->gutter;

			# caption also starts at (0,0)
			$this->caption_y = 0;

			# title is caption+padding+title
			$this->title_y = $this->caption_height;
			# add a margin spacing if there is a caption
			if( $this->caption_content ){
				$this->title_y += $this->params->caption_spacing;
			}
		
			# outer frame is all of the above ( caption + title + 2 paddings)
			$this->outerFrame_y = $this->title_height + $this->title_y + $this->params->caption_spacing;

			# inner frame is outerframe offset by padding
			# assuming that the edge of the outerFrame is 0
			$this->innerFrame_x = $this->params->stamp_border_padding;
			$this->innerFrame_y = $this->outerFrame_y + $this->params->stamp_border_padding;

		
			# the stamp image should be centered in the outer frame
			# assuming that the edge of the outerFrame is 0
			$this->image_x = ( $this->outerFrame_width - $this->image_width ) / 2;
			$this->image_y = ( ( $this->outerFrame_height - $this->image_height ) / 2 ) + $this->outerFrame_y;

				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	


			
		
		/**
		*	adds a drop shadow to the stamp image 
		*
		*	NOTE - if drop shadows are used, then they are ignored in the final dimension calculations
		*		   seeing as that they fade and are supposed to be a background element
		*
		*	@access		protected
		*	@param		float $x
		*	@param		float $y
		*	@return 	void
		*/
		protected function _use_drop_shadows( $x, $y ){
		
			#	bottom shadow
			$this->_pdf->addJpegFromFile( 
					RESOURCES."/shadows/bottom.jpg", 
					$x+6,
					$y-8,
					$this->_stamp["actual_width"] - 6,
					8
				);
						
			#	right side
			$this->_pdf->addJpegFromFile( 
					RESOURCES."/shadows/right.jpg",
					$x + $this->_stamp["actual_width"],
					$y,
					7,
					$this->_stamp["height"]
				);
						
			#	lower left end
			$this->_pdf->addJpegFromFile( 
					RESOURCES."/shadows/lower-left.jpg", 
					$x,
					$y - 8,
					7,
					8
				);
				
			#	lower right end
			$this->_pdf->addJpegFromFile( 
					RESOURCES."/shadows/lower-right.jpg", 
					$x + $this->_stamp["actual_width"],
					$y - 8,
					7,
					8
				);
				
			#	upper right end
			$this->_pdf->addJpegFromFile( 
					RESOURCES."/shadows/upper-right.jpg", 
					$x + $this->_stamp["actual_width"],
					$y + $this->_stamp["height"],
					7,
					8
				);
						
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------




} // end class
?>
