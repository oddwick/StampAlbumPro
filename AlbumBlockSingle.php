<?php 

/**
 * This is an Album page layout class that extends the an album for a specific layout
 * 
 * AlbumBlockSingle creats a block with a single stamp at the top of the page with a larger text description underneath it
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


class AlbumBlockSingle extends Album {
		
		
		/**
		*	default layout of simple rows
		*
		*	@access	protected
		*	@return 	void 
		*/
		public function layout( $textData ){
			
				//$this->params->debug = true;
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
								
				
				// iterate through the groups, extract the stamps and create an array of row objects
				foreach( $groups as $group){
					
					// dump the groups stamps into an array
					$stamps = $group->stamps();
					
					// iterate through the stamps in this group 
					// and add one stamp, one text block per field
					for( $i = 0; $i < count( $stamps); $i++){
					
						// create a blank page				
						$data = $page->page( $append);
						$maxRowWidth = $data["max_x"] - $data["minX"];
						$maxHeight = $data["max_y"] - $data["minY"];
						
						// create a new row object
						$field = new Field( $this->_pdf, $this->params, $data["minX"], $data["minY"], $data["max_x"],$data["max_y"]);
						
						// create new row object
						$row = new Row( $this->_pdf, $this->params, $maxRowWidth, $maxHeight);
						
						// add stamp to row
						$row->addStamp( $stamps[$i] );
						
						// get max dimensions
						$maxTextHeight = $maxHeight - $this->params->gutter - $row->height();
						
						// text uses 3/4 of available area unless the stamp is larger, then 
						// the stamp width will be used
						$maxTextWidth = $maxRowWidth * 0.8 < $row->width() ? $row->width():$maxRowWidth * 0.8;
						
						$text = new Textblock( $this->_pdf, $this->params, $maxTextWidth, $maxTextHeight );

						$text->addText( $textData[$stamps[$i]->stamp_id],$group->title(), $group->subtitle() );
						
						$field->add( $row );
						$field->add( $text );
						$field->write();
						
						#	set append flag to add stamps to next page
						$append = true;
						
						# 	increment the page count
						++$this->_page;
						
					}			
				
				} // end of groups
		
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
} // end class
