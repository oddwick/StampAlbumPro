<?php  

/**
 *	Base class for the Stamp album professional that generates high quality pdf
 *	pages for stamp albums
 *
 *
 * @package    		Stamp Album Pro
 * @subpackage   	Album
 * @class    		StampAlt
 * @version			1.0
 * @author			Dexter Oddwick <dexter@oddwick.com>
 * @copyright  		Copyright (c)2017
 * @license    		http://www.oddwick.com
 *
 * @todo    		
 *
 */



class StampAlt {
		
		/**
		*	@var	Param $_params
		*	@var	array $_data
		*	@var	array $_versionInfo
		*/
		protected $_params; // params object
		protected $_stamp; // Nerb_Database_Row item containing stamp data
		protected $_data= array(
							'fileName' => '',
							'stamp_id' => '',
							'height' => '',
							'hidth' => '',
							'imageHeight' => '',
							'imageWidth' => '',
							'actual_height' => '',
							'actual_width' => '',
							'boxHeight' => '',
							'boxWidth' => '',
							'text' => false,
					);


		/**
		*	Constructor initiates Stamp object
		*
		*	@access		public
		*	@param		Param $params the paramater data object
		*	@param		Cezpdf $pdf the pdf that is being created
		*	@param		int $stamp_id corresponds to the stamp_id in the database
		*	@return 	Stamp
		*/
		public function __construct( $params, $pdf, $stamp_id, $text=false){
			
				// retireve a copy of the stamp table and get stamp information
				$stamps = Nerb::fetch("stamps");
				if(!$this->_stamp = $stamps->fetchRow( $stamp_id)) return FALSE;
				//$this->_data = $stamps->fetchArray( $stamp_id);
				
				// assign prameters and pdf to variables
				$this->_params = $params;
				$this->_pdf = $pdf;
				
				
				// set the filename of the stamp image
				$this->_data['stamp_id'] =  $stamp_id;
				$this->_data['fileName'] =  $this->_params->directory.$this->_stamp->image.".jpg";
				
				// determine if the stamp is going to print with text
				$this->_data['text'] =  $text;
				
				// this gets the actual dimensions of the stamp, with border and caption
				$this->_getActualDimensions();
				
				return $this;
			
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		
		/**
		*	get object data
		*
		*	@access		public
		*	@param	 	string $key
		*	@return 	mixed $value is returned 
		*/
		public function __get( $key){
					
			// return value
			return $this->_data[$key];
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		
		/**
		*	forces the stamp to be certain width
		*
		*	@access		public
		*	@param	 	float $width
		*	@return 		void
		*/
		public function forceWidth( $width){
					
			// get the ratio of the current width to the new width before it is changed
			$ratio = $this->_data['actual_width']/$width;
			
			// change to the new width
			$this->_data['actual_width'] = $width;
			//calculate the proprotional height
			$this->_data['actual_height'] = $this->_data['actual_height']/$ratio;
			
			// work backwards to calculate the new height and width 
			// based on the new max width
			$this->_data['width'] = $this->_data['actual_width'] - ( $this->_params->stamp_border_padding * 2);
			$this->_data['height'] = $this->_data['actual_height']  - ( $this->_params->stamp_border_padding * 2)
			- $this->_params->caption_size - $this->_params->caption_spacing - 5;
			
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		
		/**
		*	forces the stamp to be certain width
		*
		*	@access		public
		*	@param	 	float $width
		*	@return 		void
		*/
		public function forceHeight( $height){
					
			// get the ratio of the current width to the new width before it is changed
			$ratio = $this->_data['actual_height']/$height;
			
			// change to the new width
			$this->_data['actual_height'] = $height;
			//calculate the proprotional height
			$this->_data['actual_width'] = $this->_data['actual_width']/$ratio;
			
			// work backwards to calculate the new height and width 
			// based on the new max width
			$this->_data['width'] = $this->_data['actual_width'] - ( $this->_params->stamp_border_padding * 2);
			$this->_data['height'] = $this->_data['actual_height']  - ( $this->_params->stamp_border_padding * 2)
			- $this->_params->caption_size - $this->_params->caption_spacing - 5;
			
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		
		/**
		*	creates a caption under the stamp
		*
		*	@access		public
		*	@return 		string formatted caption is returned
		*/
		protected function _caption(){
				// start building caption
				// scott # + variant +seten +class
				Nerb::loadClass('Stamp_Title', __LIB__ );
				$title = new Stamp_Title();
				
				
				$title->showCat = $this->_params->num?true:false; 
				$title->showColor = $this->_params->color?true:false; 
				$title->showDenom = $this->_params->denom?true:false; 
				$title->showVariety = $this->_params->variety?true:false; 
				$title->showYear = $this->_params->year?true:false; 
				$title->showPress = $this->_params->detail_press?true:false; 
				$title->showPerf = $this->_params->detail_perf?true:false; 
				
				
				$title->addStamp( $this->_stamp);
				$title = strip_tags( $title->__toString());
				//$title =  html_entity_decode( $title, ENT_NOQUOTES);
				if( $this->_params->num) $title = "#".$title;
				return $title;
					
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		/**
		*	adds a block of text under the stamp
		*
		*	@access		protected
		*	@return 		void
		*/
		protected function _text(){
					
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		
		/**
		*	calculates the actual print dimensions of the stamp
		*
		*	@access		private
		*	@return 		void 
		*/
		private function _getActualDimensions(){
			
			
			// retireve a copy of the mount table and get information
			$mounts = Nerb::fetch("mounts");
			
			// if no mount size is given, "E" (regular issue) is assumed
			// to keep the sizes small.  otherwise the stamp will be placed on the page at image size
			// and it will take up almost all of the page
			if(!$this->_stamp->showgard_type){
				$mount = $mounts->fetchRow("E");
			} else {
				$mount = $mounts->fetchRow( $this->_stamp->showgard_type);
			}
				
			// the dimensions of the stamp are 
			// Mount size in MM * MM to points  the mount size is fixed and constant.
			$this->_data['height'] = ( $mount->actual_height * $this->_params->mm2point);
			$this->_data['width'] = ( $mount->actual_width * $this->_params->mm2point);
			// the actuals include the caption and spacing and frame
			$this->_data['actual_width'] = $this->_data['width'] + ( $this->_params->stamp_border_padding * 2);
			$this->_data['actual_height'] = $this->_data['height']  + ( $this->_params->stamp_border_padding * 2)
			+ $this->_params->caption_size + $this->_params->caption_spacing + 5;
			// match the bounding box with the acutals
			$this->_data['boxWidth'] = $this->_data['actual_width'];
			$this->_data['boxHeight'] = $this->_data['actual_height'];
			
			
			// if the stamp has a text block with it, calculate the height and width;
			if( $this->_data['text']){
				
				$this->_data['boxWidth'] += 100;
				$this->_data['boxHeight'] += $this->_data['actual_height'] ;
				//echo $_SESSION['text'][$this->_stamp->stamp_id];
				//die;
			}
			
		}
		
		
		
		
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
		*	@access		public
		*	@param		float $x
		*	@param		float $y
		*	@return 	bool 
		*/
		public function write( $x, $y){
		
				// fudge factor for offset because of caption
				$y = $y+10;
				$x = $x+5;
		
				
				// debugging
				$this->_pdf->setStrokeColor(0,1,0);
				$this->_pdf->rectangle( $x, $y, $this->_data['boxWidth'], $this->_data['boxHeight']);

				
				
				
				
				// create drop shadows before anything else so they are under the image
				if( $this->_params->use_drop_shadows == true){
					//lower
					$this->_pdf->addJpegFromFile(__IMAGES__."/shadows/bottom.jpg", $x+6,$y-8,$this->_data['actual_width']-14,8);
					//right
					$this->_pdf->addJpegFromFile(__IMAGES__."/shadows/right.jpg", $x + $this->_data['actual_width']-10,$y,7,$this->_data['height']);
					// lower left
					$this->_pdf->addJpegFromFile(__IMAGES__."/shadows/lower-left.jpg", $x,$y-8,7,8);
					// lower right
					$this->_pdf->addJpegFromFile(__IMAGES__."/shadows/lower-right.jpg", $x + $this->_data['actual_width']-10,$y-8,7,8);
					//upper right
					$this->_pdf->addJpegFromFile(__IMAGES__."/shadows/upper-right.jpg", $x + $this->_data['actual_width']-10,$y+$this->_data['height'],7,8);
				}//end drop shadows 

				// create frame background to knock out background image or watermark inside the frame itself
				$this->_pdf->setColor(1,1,1);
				$this->_pdf->filledRectangle( $x, $y, $this->_data['width']+$this->_params->stamp_border_padding, $this->_data['height']+$this->_params->stamp_border_padding);

				// create the centering border which will be light gray and the actual mount size
				$this->_pdf->setStrokeColor(0.9,0.9,0.9);
				$this->_pdf->setLineStyle(0.5);
				$this->_pdf->rectangle( $x + ( $this->_params->stamp_border_padding/2), $y + ( $this->_params->stamp_border_padding/2), $this->_data['width'], $this->_data['height']);
				
				// the actual frame is x number of points (stamp_border_padding) larger than the mount itself
				$this->_pdf->setStrokeColor( $this->_params->stamp_border_color, $this->_params->stamp_border_color, $this->_params->stamp_border_color);
				$this->_pdf->rectangle( $x, $y, $this->_data['width'] + $this->_params->stamp_border_padding, $this->_data['height']+$this->_params->stamp_border_padding);
				
				
				
				// if image exists
				if(file_exists( $this->_data['fileName'])){
				
					// fetch the image file and get its dimensions
					$file = getimagesize( $this->_data['fileName']);
					$this->_data['imageWidth']=$file[0];
					$this->_data['imageHeight']=$file[1];
				
					//shrink image to fit inside of frame as a percentage of the frame
					$imageWidth = $this->_data['width'] * $this->_params->padding;
					$ratio = $this->_data['imageWidth']/$this->_data['imageHeight'];
					$imageHeight = ( $this->_data['width'] / $ratio)  * $this->_params->padding;
					
					
					// add the image to the page 
					$this->_pdf->addJpegFromFile( $this->_data['fileName'], 
						 $x + ( $this->_data['width'] - $imageWidth + $this->_params->stamp_border_padding) /2, 
						 $y + ( $this->_data['height'] - $imageHeight + $this->_params->stamp_border_padding) /2, 
											 $imageWidth, 
											 $imageHeight);
											 
					// add a diagonal slash in lower left hand corner to "cancel" stamp per USPS regs 
					// when reproducing stamp in color
				if( $this->_params->use_stamp_cancel){
					$this->_pdf->setStrokeColor(1,1,1);
					$this->_pdf->setLineStyle(1);
					$this->_pdf->line( $x + 20 + ( $this->_data['width'] - $imageWidth + $this->_params->stamp_border_padding) /2, 
						 			  $y -2 + ( $this->_data['height'] - $imageHeight + $this->_params->stamp_border_padding) /2, 
									  $x -2 + ( $this->_data['width'] - $imageWidth + $this->_params->stamp_border_padding) /2, 
						 			  $y + 20 + ( $this->_data['height'] - $imageHeight + $this->_params->stamp_border_padding) /2);
				 }// end if cancel stamps
					
				}// end if file exists



				// reset color and stroke
				$this->_pdf->setColor(0,0,0);
				$this->_pdf->setLineStyle(1);
				
				$caption = $this->_caption();
				
				// for legibility purposes, the default caption font is always helvetica and black
				$this->_pdf->setColor(0,0,0);
				$this->_pdf->selectFont(__FONTS__.'/Helvetica.afm');
				
				// if the image is greater than 500px, then place the caption 90deg on the side
				if( $totalHeight > 500){
					// adds vertical caption to bottom of image
					$this->_pdf->addText( $x+$totalWidth+3, $y + $this->_pdf->getTextWidth( $this->_params->caption_size, $caption), $this->_params->caption_size, $caption, 90);
					// adds vertical caption to top of image (alternate)
					//$this->_pdf->addText( $x+$totalWidth+3, $y + $totalHeight, $this->_params->caption_size, $caption, 90);
				} else {
					// set the margins as image width
					$this->_pdf->ezSetmargins(0,
											  0,
											  $x - $this->_params->stamp_border_padding,
											  $this->_params->max_x - $x - $this->_data['actual_width']);
	
					$this->_pdf->ezSetY( $y - $this->_params->caption_spacing);
					$this->_pdf->ezText( $caption, 
										$this->_params->caption_size, 
										array('text_justify'=>'center'));
				} // end if totalheight
				
				if( $this->_data['text']){
					$this->_text();
				}
			

		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
} // end class
?>
