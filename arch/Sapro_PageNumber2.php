<?php  /* 

 /**
 *	Class for automatically creating linked page numbers
 *
 * @category   	Nerb
 * @package    	Nerb
 * @access		public
 * @version		1.0
 * @author		Derrick Haggerty <dhaggerty@nerbal.com>
 * @copyright  	Copyright 2006  Nerb Software Studios, Inc. (http://www.gnert.com)
 * @license    	http://www.gnert.com/docs/license
 */


class Sapro_PageNumber2 extends Nerb_PageNumber {
		
		/** 
		*/
		
		
		/**
		*	uses output buffering to display page numbers
		*
		*	@access		public
		*	@return 	string
		*/
		public function __toString(){
		
			// calculate the total number of pages based on default values given
			$this->_totalPages();
			if($this->_totalPages <= 1) return "";
				
			//initialize string variable
			$string = NULL;
			
			// iterate through pages
			// checks to see if the results exceede the truncation point and if there is to 
			// be truncation, otherwise the results will be rolled out normally 
			// truncation will show results in three formats
			//  1 [2] 3 4 ... XX  -- if the page is less than 1/2 the truncation value
			//  1...5 6 [7] 8 9 ... XX -- if the current page is inbetween the extremes
			//  1... 22 23 [24] 25  -- if the current page is greater than Max-1/2 truncation 
			
			
			//echo $this->_totalPages;
			$mid = floor($this->_params['truncate']/2);
			$startPage = 1;
			
			/*--------- truncation page_rules --------------*/
			// if current page is greater than 2, set the current page in the middle of the group
			if( $this->_currentPage - $mid > 2){ $startPage = $this->_currentPage - $mid; }
			// if current page is greater than max pages - truncation, will set the max pages so that it cannot be exceeded
			if(  $this->_currentPage > $this->_totalPages - $mid - 2 ){ $startPage = $this->_totalPages - $mid -2; }
			// if startpage is < 0 startpage = 1
			if(  $startPage < 1 ){ $startPage = 1; }
			
			// iterate through the pages
			for($i = $startPage; $i < $this->_params['truncate'] + $startPage && $i <=  $this->_totalPages; $i++){
				if($i == $this->_currentPage){
					// replaces the * with the current page number in the currentPageLocator string
					$string .= "<li class='current'><a href='#'>".$i."</a></li>";
				} else {
					// detect if link has variables
					//$link = $this->_link.(strstr($this->_link,"?")?"&":"?")."page=".$i;
					// creates a page link
					$string .= $this->_jslink($i);

				}// end  if
				
				$string .= $this->_params['divider'];
			}// end for

			
			// additional string addons
			// adds a perminant link to the first page
			if( $this->_currentPage - $mid > 2){ 
				$string = $this->_jslink(1)."<li class='unavailable'>&hellip;</li>".$string;
			}
			
			// adds a perminant link to the last page
			if(  $this->_currentPage < $this->_totalPages - $mid - 1){ 
				$string .= "<li class='unavailable'>&hellip;</li>".$this->_jslink($this->_totalPages);
			}
						
			return "<ul class='pagination'>".$string."</ul>";
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		
		/**
		*	Formats a page number link in a javascript format for ajax requests
		*
		*	@access		public
		*	@param		string $page the page number being added
		*	@param		string $text optional argument if the string text differs from the page number, e.g. "next" etc.
		*	@return 		string
		*/
		protected function _jslink($page, $text = NULL){
		
			$click = $this->_link.$page;
			
			$string = '<li><a class="'.$this->_params['anchorClass'].'" href="'.$click."\">".($text?$text:$page)."</a></li>";
			return $string;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		



		
		
} /* end class */
?>
