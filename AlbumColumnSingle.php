<?php 

/**
 * This is an Album page layout class that extends the an album for a specific layout
 * 
 * AlbumColumnSingle is a single stamp layout where groups are ignored and a single stamp is placed on a page with a large block of text and a detailed caption under the stamp
 *
 *
 * @package    		Stamp Album Pro
 * @class    		AlbumRows
 * @extends			Album
 * @version			1.0
 * @author			Dexter Oddwick <dexter@oddwick.com>
 * @copyright  		Copyright (c)2017
 * @license    		http://www.oddwick.com
 *
 * @todo    		
 *
 */
 
// load required libraries
require_once( LIB."/Detail.php" );



class AlbumColumnSingle extends Album {
		
		
		/**
		*	default layout of simple rows
		*
		*	@access	protected
		*	@return 	void 
		*/
		public function layout( $textData ){
			
				//$this->params->debug = true;
				$this->params->cols = 1;
				// assign stamp object list to temp variable
				// and initialize the variables
				$groups = $this->_groups;
				$append = false;
				
				// define an empty page
				$page = new Page( $this->_pdf, $this->params, $this->params->max_x, $this->params->max_y);
				// get the available dimensions of the field
				// and calculate the max dimensions
				
				// adjust page count
				--$this->_page;
				//print_r( $textData);				
				//die;
				
				// create data array
				$stamps = array();
				//iterate through the groups and extract stamps
				foreach( $groups as $group){
					// dump the groups stamps into an array
					$data = $group->stamps();
					foreach( $data as $stamp){
						$stamps[] = $stamp;
					}
				}
					
					
				// iterate through the stamps, adding exactly one stamp per page
				foreach( $stamps as $stamp ){
					// create a blank page				
					$data = $page->page( $append);
					$maxWidth = $data["max_x"] - $data["minX"];
					$maxHeight = $data["max_y"] - $data["minY"];

					// create a new column object
					$col = new Column( $this->_pdf, $this->params, $maxWidth, $maxHeight);
					// and add one stamp, one text block per field
					$col->addStamp( $stamp );
					
					// text uses 2/3 of available area for the text unless the stamp is larger
					$vert_third = $maxWidth/3;
					if( $col->width() > $vert_third){
						$maxTextWidth = $maxWidth - $this->params->gutter - $col->width();
					}else{
						$maxTextWidth = ( $vert_third * 2) - $this->params->gutter;
					}
					
					
					// create text block
					$text = new Textblock( $this->_pdf, $this->params, $maxTextWidth, $maxHeight);
					$text->addText( $textData[$stamp->stamp_id], $stamp->data(description), $stamp->caption() );
					
					
					//calculate x centers
					$totalWidth = $col->width() + $text->width() + $this->params->gutter;
					$textStartX = $data["minX"] + ( $maxWidth - $totalWidth )/2;
					$colStartX = $textStartX + $text->width() + $this->params->gutter;
					
					// if the header spacing is set, use header spacing, otherwise
					// auto is assumed and the text will be centered around the 80% mark
					// calculate whichever is larger the text or stamps
					if(!$this->params->header_spacing){
						$yMargin = ( $maxHeight - ( $col->height() > $text->height()?$col->height():$text->height()))/2;
					} else {
						$yMargin = 0;
					}
					$topOfPage = $data["minY"] + $maxHeight -$yMargin;
					if( $topOfPage < ( $data["max_y"] * 0.8) ) $topOfPage = ( $data["max_y"] * 0.8);
					
					// create a detail object
					$detail = new Detail( $stamp, $this->_pdf, $this->params, $col->width());
					
					// write out the contents of the field to the page
					$text->write( $topOfPage - $text->height(), $textStartX );
					$col->write( $colStartX, $topOfPage);
					$detail->write( $colStartX, $topOfPage - $col->height()-15);
					
					
					// increment the page count and append pages
					++$this->_page;
					$append = true;
			
				} // end of groups
				

				
				//die;
					
		
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
} // end class
