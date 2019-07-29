<?php 

/**
 * This is an Album page layout class that extends the an album for a specific layout
 * 
 * AlbumRows creats a linear block of horizontal rows of stamp groups
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


class AlbumRows extends Album {
		
		
		/**
		*	default layout of simple rows
		*
		*	@access	protected
		*	@return 	void 
		*/
		public function layout(){
				
				// assign stamp object list to temp variable
				// and initialize the variables
				$groups = $this->_groups;
				$stamps = array();
				$rows = array();
				
				// define an empty page
				$page = new Page( $this->_pdf, $this->_params, $this->_params->max_x, $this->_params->max_y);
				// get the available dimensions of the field
				// and calculate the max dimensions
				$data = $page->page();
				$maxRowWidth = $data["max_x"] - $data["minX"];
				$maxHeight = $data["max_y"] - $data["minY"];
								
				// iterate through the groups, extract the stamps and create an array of row objects
				foreach( $groups as $group){
					
					// dump the groups stamps into an array
					$stamps = $group->stamps();
					// create a new row object
					$row = new Row( $this->_pdf, $this->_params, $maxRowWidth, $maxHeight);
					
					// iterate through the stamps in this group
					for( $i = 0; $i < count( $stamps); $i++){
					
						// if this is the first row, add the group titles
						if(!$i){
							$row->addTitles( $group->title(), $group->subtitle());
							if( $this->_params->group_text_location == "above") $row->addTextBlock( $group->text());
						} 

						// add stamp to the row
						// if the row is full, add the row to the rows[] array, create a new row, 
						// and reset the count so as to attempt to add the same stamp to the next row
						if( !$row->addStamp( $stamps[$i] ) ){
							$rows[] = $row;
							$row = new Row( $this->_pdf, $this->_params, $maxRowWidth, $maxHeight);
							$i--;
						}
					}			
				
					// add the remaining stamps or uncompleted row to the rows[] array
					$rows[] = $row;
					if(!$this->_params->group_text_location  == "above" ) $row->addTextBlock( $group->text());
				} // end of groups
				
				// begin adding the rows to pages
				
				// increment page number counter.
				///++$this->_page;
				//$data = $page->page( $append = true);
				$field = new Field( $this->_pdf, $this->_params, $data["minX"], $data["minY"], $data["max_x"],$data["max_y"]);
				
				
				// iterate through the rows and add to the fields
				for( $i = 0;  $i < count( $rows); $i++){
					
					// add stamps to the field and if the field is full,
					// break and print to pdf, create a new page, 
					// increment the page counter, and roll back the row counter
					// so as to try adding the row to a new page
					if( !$field->add( $rows[$i] )){
						// write out the field contents
						$field->write();
						// new page in append mode
						$page->page(true);
						// increment the page count
						++$this->_page;
						// create a new field
						$field = new Field( $this->_pdf, $this->_params, $data["minX"], $data["minY"], $data["max_x"],$data["max_y"]);
						// roll back the row counter
						--$i;
					}
				}
				
				// write out the last page
				$field->write();
				
				//die;
		
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
} // end class
?>
