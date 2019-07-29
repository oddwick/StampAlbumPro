<?php   

/**
 *	Base class for the Stamp album professional that generates high quality pdf
 *	pages for stamp albums
 *
 *
 * @package    		Stamp Album Pro
 * @subpackage   	Album
 * @class    		Group
 * @version			1.0
 * @author			Dexter Oddwick <dexter@oddwick.com>
 * @copyright  		Copyright (c)2017
 * @license    		http://www.oddwick.com
 *
 * @todo    		
 *
 */



class Group extends Core {
		

		/**
		*	@var	array $_stamps
		*	@var	Czpdf $_pdf
		*	@var	Param $params
		*	@var	array $_versionInfo
		*/
		protected $_stamps = array(); // array of stamp objects object
		protected $_rows = array(); // array of stamp objects object
		protected $_inline = false;
		protected $_data = array(
				"title" => array( "content" => "",
								  "x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
				"subtitle" => array( "content" => "",
								     "x" => 0, 
									 "y" => 0, 
									 "width" => 0, 
									 "height" => 0 
								  ),
				"text" => array(  "content" => "",
								  "x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
				"header" => array("x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
				"actual" => array("x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
				"field" => array("x" => 0, 
								  "y" => 0, 
								  "width" => 0, 
								  "height" => 0 
								  ),
					);


		/**
		*	Constructor initiates Group object
		*
		*	@access		public
		*	@param		Param $param
		*	@param		Czpdf $pdf
		*	@param		array $group
		*	@return 	object self
		*/
		public function __construct( $group, $text = array(), $field ){
		
				#	transfer param and pdf to internal variables
				#  this line is required in all classes
				parent::__construct();

				$this->title_content = $group['title'];
				$this->subtitle_content = $group['subtitle'];
				$this->text_content = $group['text'];
				
				
				$this->_data["field"] = $field;
				
				# iterate through the stamps and create stamp objects
				foreach( $group["stamps"] as $stamp) {
					#create a new stamp object from a stamp id
					$this->_stamps[ $stamp ] = new Stamp ( (int) $stamp, $text[ $stamp ] );
				}
				
				# add stamps to rows and get the widths of the rows
				# this is used in the precalc function because the title, subtitle and text block wont
				# be longer than the row
				$this->_expand();
				
				# figure out the dimensions and locations of all of the elements
				$this->_precalc();
				
				return $this;
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		

	

		/**
		*	actually writes the group to the page
		*
		*	@access		public
		*	@param	 	float $x
		*	@param	 	float $y
		*	@return 	void 
		*/
		protected function _write( $x, $y ){ 
		
			/*
			unless otherwise specified, the group will be centered in the field
			
							field
					---------------------
					|		  |			|  
					|		  |			|
					| group-X |			|
					|     ----|-----	|
					|	  | header |	|
					|	  |	  |	   |	|
					|	  |	row-X  |	|
					|-----|--------|----|  <-- 1/2 ( field_width - group_width ) 
					|	  |	row-X  |	|
					|	  |	  |	   |	|
					|	  |	row-X  |	|
				  ^	|     +---|-----	|
				  |	|	  	  |			|
				  Y	|		  |			|  
					+--------------------
				origin	x->    ^-- 1/2 ( field_height - group_height ) 
			*/
			
			$this->actual_x = $x + ( $this->field_width - $this->actual_width ) / 2;
			$this->actual_y = $y + ( $this->field_height - $this->actual_height ) / 2;
			
			
			$this->header_x = $this->actual_x;
			$this->header_y = $this->actual_y + $this->actual_height - $this->header_height;
			
			# regardless of the page, the centerpoint is always the same
			$page_center = $this->params->max_x / 2;
				

			# because of the x/y axis on the pdf start at the lower left corner and a logical page
			# is the upper left corner, all of the dimensions are in reverse order and the location of
			# the header is figured first and all of the other rows are calculated from there
			#
			# the title starts at the top of the available area and is as wide as the field area.
			# the number of characters should be limited? 
			$this->title_y = $this->actual_y + $this->actual_height - $this->title_height;
			$this->title_x = ( $this->params->max_x / 2 ) - ( $this->title_width / 2 );
			$this->_writeTitle();


			$this->subtitle_y = $this->title_y - $this->subtitle_height - $this->params->group_subtitle_spacing;
			$this->subtitle_x = ( $this->params->max_x / 2 ) - ( $this->subtitle_width / 2 );
			$this->_writeSubtitle();


			$this->text_y = $this->subtitle_y - $this->text_height - $this->params->group_text_spacing;
			$this->text_x = 100;
			$this->text_x = (( $this->params->max_x - ( $this->params->max_x * $this->params->group_text_maxpercent ) ) / 2) ;
			//				- ( $this->text_width / 2 );		

			$this->_writeText();
			
			
			$start_y = $this->header_y -$this->params->group_header_spacing;
			
			# the actual area of the group
			
			foreach( $this->_rows as $row ){
				#center the row within the actual_width of the page
				$start_x = $page_center - ( $row->width() /2 );
				$row->write( $start_x, $start_y );
				$start_y = $start_y - $row->height() - $this->params->gutter;
			}
			
			//echo "why hello there...<pre>";
			//print_r( $this->_data );
			//die;
			
			

			# debugging mode on and will print out a square showing the available field size
			if(  $this->params->debug == "ALL" ||  $this->params->debug == "group" ){
				$this->_debug( $this->_data );
			}
			
			
		} 
		// end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

		
		
		/**
		*	gets the initial starting points by figuring out the height and width of 
		*	the group elements
		*
		*	@access		public
		*	@return 	mixed $value is returned 
		*/
		protected function _precalc(){ 
		
					
			# calculate the height of the rows + title and header
			# the height of the title
			if( $this->title_content ){
				$this->title_height = $this->getTextHeight( $this->title_content, 
														    $this->field_width, 
															$this->params->group_title_font_size 
														);
				$this->title_width = $this->_pdf->getTextWidth( $this->params->group_title_font_size, $this->title_content );
				$this->header_height = $this->title_height + $this->params->group_title_separation;
			}
						

			# the height of the subtitle
			if( $this->subtitle_content ){
				$this->subtitle_height = $this->getTextHeight( $this->subtitle_content, 
															   $this->field_width, 
															   $this->params->group_subtitle_font_size 
															);
				$this->subtitle_width = $this->_pdf->getTextWidth( $this->params->group_title_font_size, $this->subtitle_content  ); 
				$this->header_height += $this->subtitle_height;
			}
			
			# the height of the text
			if( $this->text_content ){
				$this->text_height = $this->getTextHeight( $this->text_content, 
														   $this->params->max_x * $this->params->group_text_maxpercent, 
														   $this->params->group_text_font_size 
													);
				$this->text_width = $this->params->max_x - ( $this->params->max_x * $this->params->group_text_maxpercent ); 
				$this->header_height += $this->text_height;
			}
			
			
			# set header width to the max width of the longest row
			$this->header_width = $this->actual_width;
			
			# calculate the total height
			$this->actual_height = $this->header_height;
			
			# if there is a header (height exists) then add the spacing between the header and first stamps
			if( $this->header_height > 0) $this->actual_height += $this->params->group_header_spacing;
			
			# iterate through the rows and add their height to the total
			foreach( $this->_rows as $row ){
				$this->actual_height += $row->height();
			}
			
			# add row spacings to the total height
			# the gutters are 1 less than the number of rows
			$this->actual_height += ( $this->params->gutter * ( count( $this->_rows ) - 1 ) );
			
			
			return this;


			
		} 
		// end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

		
		
		/**
		*	this expands the stamp list into either rows or columns depending what is delared
		*
		*	@access		public
		*	@param		Field $field
		*	@return 	string
		*/
		protected function _expand(){	
			
			
		    //Nerb::inspect( $this->_data, true, "data" );
			//die;
			
			# initialize counters
			# -- the number of rows in this group
			$row_count = 0;
			# -- the number of stamps in the group
			$stamp_count = 1;
			
			# create an initial row object
			$this->_rows[ $row_count ] = new Row( $this->field_max_width,  $this->field_max_height ); 
			
			
			# iterate through all of the stamps and add them to rows
			foreach( $this->_stamps as $stamp ){
				
				# add a stamp to the row 
				$status = $this->_rows[ $row_count ]->addStamp( $stamp );
				
				# if a stamp is bounced, then 
				if( $status == false ){
					
					# increment row count and create a new row
					$row_count++;
					$this->_rows[ $row_count ] = new Row( $this->field_max_width,  $this->field_max_height );
					
					# add offending stamp to the new row
					$status = $this->_rows[ $row_count ]->addStamp( $stamp );
				
				}// end if
				
				# calculate the longest row using a stone sort method
				# it will keep adding the widths to actual-width until the longest one
				# has been added to it
				if( $this->_rows[ $row_count ]->width() > $this->actual_width )
					$this->actual_width = $this->_rows[ $row_count ]->width();
				
				# increment the stamp count
				$stamp_count++;
				
			}// end for each
			
			return $this;
			
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------

 
 

		/**
		*	writes out the title for the group
		*
		*	@access		protected
		*	@param		float $x
		*	@param		float $y
		*	@return 	void
		*/
		protected function _writeTitle(){
							
			// reset color and stroke
			$this->_reset();			
			$this->_pdf->ezSetY( $this->title_y + $this->title_height - $this->_pdf->getFontDecender( $this->params->group_title_font_size ) );
			$this->_pdf->ezText( $this->title_content, 
								 $this->params->group_title_font_size, 
								 array( "justification" =>"center",
								 		"aleft" => $this->field_min_x,
								 		"aright" => $this->field_max_x
								  ) 
							);
				
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	
	
	

		/**
		*	writes out the title for the group
		*
		*	@access		protected
		*	@param		float $x
		*	@param		float $y
		*	@return 	void
		*/
		protected function _writeSubtitle(){
							
			// reset color and stroke
			$this->_reset();			
			$this->_pdf->ezSetY( $this->subtitle_y + $this->subtitle_height - $this->_pdf->getFontDecender( $this->params->group_subtitle_font_size )  );
			$this->_pdf->ezText( $this->subtitle_content, 
								 $this->params->group_subtitle_font_size, 
								 array( "justification" =>"center",
								 		"aleft" => $this->field_min_x,
								 		"aright" => $this->field_max_x
								  ) 
							);
				
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	



		/**
		*	writes out the text for the group
		*
		*	@access		protected
		*	@param		float $x
		*	@param		float $y
		*	@return 	void
		*/
		protected function _writeText(){
			
			$margin = ( $this->params->max_x - ( $this->params->max_x * $this->params->group_text_maxpercent ) ) / 2;		
			// reset color and stroke
			$this->_reset();			
			$this->_pdf->ezSetY( $this->text_y + $this->text_height - $this->_pdf->getFontDecender( $this->params->group_text_font_size )  );
			$this->_pdf->ezText( $this->text_content, 
								 $this->params->group_text_font_size, 
								 array( "justification" =>"center",
								 		"aleft" => $margin,
								 		"aright" => $this->params->max_x - $margin
								  ) 
							);
				
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

} // end class
?>
