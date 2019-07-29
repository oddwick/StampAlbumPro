<?php 

/**
 *	Base class for the Stamp album professional that generates high quality pdf
 *	pages for stamp albums
 *
 *
 * @package    		Stamp Album Pro
 * @subpackage   	Album
 * @class    		Field_Rows
 * @see				Field
 * @version			1.0
 * @author			Dexter Oddwick <dexter@oddwick.com>
 * @copyright  		Copyright (c)2017
 * @license    		http://www.oddwick.com
 *
 * @todo    		
 *
 */


class Field_Rows extends Field implements Iterator {
		



		public function current (  ){
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

		
		
		public function key (  ){
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

		
		
		public function next (  ){
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

		
		
		public function rewind (  ){
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

		
		
		public function valid (  ){
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

		
		
		

		/**
		*	returns the images in rows
		*
		*	@access		public
		*	@param		array $stamps
		*	@return 	string 
		*/
		public function add( $stamps){
				
				//determine if the total height is greater than the available height
				$totalHeight = 0;
				
				// calculate the maximum available with for the row
				$maxRowWidth = $this->_max_x - $this->_min_x;
				$maxHeight = $this->_max_y - $this->_min_y;
				
				
				// initialize variables
				$row = 0;
				$rows = array();
				$rowHeight = 0;
				$rowWidth = 0;
				
				
				// create rows while there are still stamps and the rows do not exceed the max allowed height
				// of the working area
				while(count( $stamps) > 0 && $totalHeight < $maxHeight){

					// cycle through the array to get information
					$row = new Row( $this->_pdf, $this->params, $maxRowWidth, $maxHeight);
					
					$rollback = $stamps;
					// add stamps to row until filled 
					while( $row->addStampToRow( $stamps[0]) == true ){
						// if stamp fits into the row, pop the array
						array_shift( $stamps);
					} // end while
					
					// when filled, add row object to rows array and increment height
					if( $totalHeight + $row->height() + (count( $rows)>0 ? $this->params->gutter : 0) > $maxHeight){
						$stamps = $rollback;
						break;
					} else {
						$totalHeight += $row->height() + (count( $rows)>0 ? $this->params->gutter : 0);
						$rows[] = $row;
					} // end if
					
					
				} // end while
				
				// calculate the bottom of the first row
				$start = $this->_max_y - ( $maxHeight/2) + ( $totalHeight/2);
				
/*				// debugging
				$this->debug();
				$center = $this->_max_y - ( $maxHeight/2);
				$this->_pdf->setLineStyle(1);
				$this->_pdf->setStrokeColor(1,0,0);
				$this->_pdf->line(0, $start, 700, $start);
				$this->_pdf->setStrokeColor(0,1,0);
				$this->_pdf->line(0, $center, 700, $center);
*/				
				// cycle rows array write each row
				foreach( $rows as $row){
					// increment the starting point of the next row
					$start = $start - $row->height();
					// write out contents of row
					$row->write( $start);
					// increment the starting point of the next row
					$start = $start - $this->params->gutter;
				}
				
				
				// returns unused portion of the stamps
				return $stamps;
				
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

		
		
		/**
		*	returns the images in rows
		*
		*	@access		public
		*	@param		array $groups
		*	@return 	string 
		*/
		public function addGroups( $groups){
				
				//$this->debug();
				
				//print_r( $groups);
				//determine if the total height is greater than the available height
				$totalHeight = 0;
				
				// calculate the maximum available with for the row
				$maxRowWidth = $this->_max_x - $this->_min_x;
				$maxHeight = $this->_max_y - $this->_min_y;
				
				
				// initialize variables
				$row = NULL;
				$rows = array();
				$rowHeight = 0;
				$rowWidth = 0;
				$groupCount = 0;
				
				
				// create rows while there are still stamps and the rows do not exceed the max allowed height
				// of the working area
				foreach( $groups as $group){
				
					$stamps = $group['stamps'];
					// counter to determine how many rows are in the group.
					// if a row has a title and subtitle and more than one row, only the first row
					// gets the title.
					$groupRow = 0; 
					
					while(count( $stamps) > 0 && $totalHeight < $maxHeight){
	
						// cycle through the array to get information
						$row = new Row( $this->_pdf, $this->params, $maxRowWidth);
						
						// if the row is the first one, add titles
						if( $groupRow == 0){
							if( $group['title'] || $group['subtitle']){
								$row->addTitles( $group['title'], $group['subtitle']);
							}
							
							
							// if spacing is set and it is not the first group, add spacing	
							if( $groupCount > 0) {
								$row->space( $this->params->group_spacing);
							}
								
							// if spacing is set to true, add a dummy title to separate the groups	
							//}elseif(!$group['title'] && $this->params->use_group_separator == true) {
							//	$row->addTitles("   ");

						}
						
						$rollback = $stamps;
						// add stamps to row until filled 
						while( $row->addStampToRow( $stamps[0]) == true ){
							// if stamp fits into the row, pop the array
							array_shift( $stamps);
						} // end while

						// when filled, add row object to rows array and increment height
						if( $totalHeight + $row->height() + (count( $rows)>0 ? $this->params->gutter : 0) > $maxHeight){
							$stamps = $rollback;
							break;
						} else {
							$totalHeight += $row->height() + (count( $rows)>0 ? $this->params->gutter : 0);
							$rows[] = $row;
						} // end if
						
						$groupRow++;
					} // end while
					
					// reassign the remainder of the stamps back to the group
					$groups[$groupCount]['stamps'] = $stamps;
					$groupCount++;
					
				}// end foreach groups
					
					
				// calculate the bottom of the first row
				$start = $this->_max_y - ( $maxHeight/2) + ( $totalHeight/2);
				
				// cycle rows array write each row
				foreach( $rows as $row){
					// increment the starting point of the next row
					$start = $start - $row->height();
					// write out contents of row
					$row->write( $start);
					// increment the starting point of the next row
					$start = $start - $this->params->vert_gutter;
				}
					
				
				foreach( $groups as $group){
					if(count( $group['stamps']) > 0){
						$tempgroup[] = $group;
					}
				}
			
				//die;
//				die;
				// returns unused portion of the stamps
				return $tempgroup;
				
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

		
		
} // end class
?>
