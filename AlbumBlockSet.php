<?php 

/**
 * This is an Album page layout class that extends the an album for a specific layout
 * 
 * AlbumBlockSet creates a set of stamps at the top of the page with a large text block under it
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


class AlbumBlockSet extends Album {
		
		
		/**
		*	default layout of simple rows
		*
		*	@access	protected
		*	@return 	void 
		*/
		public function layout(){
			
				//$this->params->debug = true;
				// assign stamp object list to temp variable
				// and initialize the variables
				$groups = $this->_groups;
				$append = false;
				
				// define an empty page
				$page = new Page( $this->_pdf, $this->params, $this->params->max_x, $this->params->max_y );
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
					$maxRowWidth = $data["max_x"] - $data["minX"];
					$maxHeight = $data["max_y"] - $data["minY"];
					// create a new field object
					$field = new Field( $this->_pdf, $this->params, $data["minX"], $data["minY"], $data["max_x"],$data["max_y"]);
					// create a new row object
					$row = new Row( $this->_pdf, $this->params, $maxRowWidth, $maxHeight);
					// iterate through the stamps in this group 
					// and add one stamp, one text block per field
					for( $i = 0; $i < count( $stamps); $i++){
						// add stamp to row on fail, roll back index and start again
						if(!$row->addStamp( $stamps[$i] ) ){
							$field->add( $row );
							$row = new Row( $this->_pdf, $this->params, $maxRowWidth, $maxHeight);
							--$i;
						}
					
					}// end for each stamps
					
					// add last row to fiedls			
					$field->add( $row );
					// get max dimensions
					
					
					$maxTextHeight = $field->available() - $this->params->gutter;
					
					// text uses 3/4 of available area unless the stamp is larger, then 
					// the stamp width will be used
					$maxTextWidth = $maxRowWidth * 0.75 < $field->width() ? $field->width():$maxRowWidth * 0.75;
					
					$text = new Textblock( $this->_pdf, $this->params, $maxTextWidth, $maxTextHeight);

					$text->addText( $group->text(), $group->title(), $group->subtitle() );
					$field->add( $text );
					// write out the contents of the field to the page
					$field->write();
					$append = true;
					// increment the page count
					++$this->_page;
			
				} // end of groups
				//die;
		
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
} // end class
?>
