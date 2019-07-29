<?php 

/**
 * This is an Album page layout class that extends the an album for a specific layout
 * 
 * AlbumColumn is a layout that creates a vertical column of stamps on one side of the page and larger text description on the other
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


class AlbumColumn extends Album {
		
		
		/**
		*	default layout of simple rows
		*
		*	@access	protected
		*	@return 	void 
		*/
		public function layout(){
			
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
								
				// iterate through the groups, adding exactly one group per page
				foreach( $groups as $group){
					
					// dump the groups stamps into an array
					$stamps = $group->stamps();
					
					// create a blank page				
					$data = $page->page( $append);
					$maxWidth = $data["max_x"] - $data["minX"];
					$maxHeight = $data["max_y"] - $data["minY"];
					// create a new field object
					// create a new row object
					$col = new Column( $this->_pdf, $this->params, $maxWidth, $maxHeight);
					// iterate through the stamps in this group 
					// and add one stamp, one text block per field
					for( $i = 0; $i < count( $stamps); $i++){
					
						// add stamp to row on fail, roll back index and start again
						$col->addStamp( $stamps[$i] );
						// end if
					
					}// end for each stamps
					
					
					// text uses 2/3 of available area for the text unless the stamp is larger
					$vert_third = $maxWidth/3;
					if( $col->width() > $vert_third){
						$maxTextWidth = $maxWidth - $this->params->gutter - $col->width();
					}else{
						$maxTextWidth = ( $vert_third * 2) - $this->params->gutter;
					}
					
					// create text block
					$text = new Textblock( $this->_pdf, $this->params, $maxTextWidth, $maxHeight);
					$text->addText( $group->text(), $group->title(), $group->subtitle() );
					
					
					//calculate x centers
					$totalWidth = $col->width() + $text->width() + $this->params->gutter;
					$textStartX = $data["minX"] + ( $maxWidth - $totalWidth )/2;
					$colStartX = $textStartX + $text->width() + $this->params->gutter;
					
					
					// calculate whichever is larger the text or stamps
					if(!$this->params->header_spacing){
						$yMargin = ( $maxHeight - ( $col->height() > $text->height()?$col->height():$text->height()))/2;
					} else {
						$yMargin = 0;
					}
					$topOfPage = $data["minY"] + $maxHeight -$yMargin;
					
					if( $topOfPage < ( $data["max_y"] * 0.8) ) $topOfPage = ( $data["max_y"] * 0.8);
					
					// write out the contents of the field to the page
					$text->write( $topOfPage - $text->height(), $textStartX );
					$col->write( $colStartX, $topOfPage);
					$append = true;
					// increment the page count
					++$this->_page;
			
				} // end of groups
				

				
				//die;
					
		
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
} // end class
