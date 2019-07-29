<?php  /* 

/**
 *	class for authorizing a set of credentials against a database
 *
 *
 * @category   		SAPro
 * @package    		SAPro
 * @subpackage 		Title
 * @access			public
 * @version			1.0
 * @author			Derrick Haggerty <dhaggerty@gnert.com>
 * @copyright  	Copyright �2012  Gnert Software Studios, Inc. (http://www.gnert.com)
 * @license    		http://www.gnert.com/docs/license
 *
 */


class iStamp_Title {
		
		/**
		*	@var	array $_params
		*	@var	Nerb_Database_Row $_stamp
		*	@var	string $_title
		*	@var	bool $_child
		*	@var	bool $_variant
		*	@var	bool $_specialized
		*	@var	bool $_highlighted
		*	@var	bool $_image
		*	@var	bool $_debug flag for debugging mode
		*/
		protected $_params= array(
				'classDefault'=> "", // default class
				'classVariant'=> "variant", // default class for variants
				'classChild'=> "child", // default class for children of seten stamps
				'classSpecialized'=> "specialized", // default class for specialized
				'classMultiple'=> "multiple", // default class for multiple stamps
				'classHighlight'=> "highlight", // default class for highlighted stamps
				'classThumb'=> "thumb", // default class for thumbnails
				'classImage'=> "preview", // default class for images
				'strongTitle'=> false,
				'shortDescription'=> false,
				'shortDescriptionLength'=> 20,
				'showSubtitle'=> true,
				'showClass'=> false,
				'showCat'=> true,
				'showVariety'=> true,
				'showYear'=> false,
				'showIssueDate'=> false,
				'showIssueLocation'=> false,
				'showQty'=> false,
				'showDesigner'=> false,
				'showEngraver'=> false,
				'showPrinter'=> false,
				'showDenom'=> true,
				'showColor'=> true,
				'showPress'=> false,
				'showPaper'=> false,
				'showWatermark'=> false,
				'showPerf'=> false,
				'lightbox'=> true, // sets the flag if image is a lightbox item
				'numberVariants'=> false // determines whether variants include scott no
			);
		protected $_stamp = NULL;
		protected $_title = '';
		protected $_catalog = '';
		protected $_formatted = '';
		protected $_child = false;
		protected $_highlighted = false;
		protected $_multiple = false;
		protected $_useClasses = true; // determines if classes will be used for variants
		protected $_link = NULL;
		protected $_hasImage = false;
		
		// debugging variables
		protected $_debug = false;


		/**
		*	Constructor initiates stamp title
		*
		*	@access	public
		*	@param	Nerb_Database_Row $row
		*	@return 	void
		*/
		public function __construct($data = NULL){
				if($data && get_class($data) == 'Nerb_Database_Row'){
					$this->addStamp($data);
				}
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		
		/**
		*	Set object operating parameters
		*
		*	@access	public
		*	@param	string $key
		*	@param	mixed $value
		*	@return 	mixed old is returned 
		*	@throws 	Nerb_Error
		*/
		public function __set($key, $value){
				
				// must pass valid key or error will be thrown
				if(!array_key_exists($key, $this->_params)){
					$error = "<b>'$key'</b> is an invalid parameter. <br /><br />Parameters for this class are:<br /><code style='color:red'>";
					foreach($this->_params as $key=>$value){
						$error .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$key."<br />";
					}
					$error .= "</code>";				
					throw new Nerb_Error($error);
				} // end if		
				
				// get original value
				$old = $this->_params[$key];
				
				// set new value
				$this->_params[$key] = $value;
				
				// return old value
				return $old;
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		/**
		*	Get object operating parameters
		*
		*	@access	public
		*	@param	string $key
		*	@return 	mixed returns the whole parameters array with $class->PARAMETERS or $class->PARAMS
		*/
		public function __get($key){
				// PARAMETERS and PARAMS are Nerb reserved words
				if($key == 'PARAMETERS' || $key == 'PARAMS'){ 
					return $this->_params;
				} else {
					return isset($this->_params[$key])?$this->_params[$key]:NULL;
				}
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		/**
		*	Get object operating parameters
		*
		*	@access	public
		*	@return 	mixed returns the whole parameters array with $class->PARAMETERS or $class->PARAMS
		*/
		public function __toString(){
				$this->_format();

				// adds link to formatted string
				if($this->_link){
					return "<a href='".$this->_link."'>".$this->_formatted."</a>";
				} else {
					return $this->_formatted;
				}	

		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		/**
		*	Get object operating parameters
		*
		*	@access	public
		*	@param	string $key
		*	@return 	mixed returns the whole parameters array with $class->PARAMETERS or $class->PARAMS
		*/
		public function title(){
				return $this->_title;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		/**
		*	Adds stamp data to the stack, clears values, formats title
		*
		*	@access	public
		*	@return 	string 
		*/
		public function addStamp($data, $highlight=false){
				if($data && get_class($data) == 'Nerb_Database_Row'){
					$this->_stamp = $data;
					$this->_multiple = $this->_stamp->multiple==1?true:false;
					$this->_link = NULL;
					
					// format title string
					$this->_title();
					
				}
				return $this;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		
		/**
		*	sets flag if stamp is highlighted
		*
		*	@access		public
		*	@param		bool $flag default true
		*	@return 	Stamp_Title 
		*/
		public function highlight($flag=true){
				$this->_highlighted = (bool) $flag;
				return $this;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		
		/**
		*	sets flag if classes are used
		*
		*	@access		public
		*	@param		bool $flag default true
		*	@return 	Stamp_Title 
		*/
		public function useClasses($flag=false){
				$this->_useClasses = (bool) $flag;
				return $this;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		
		/**
		*	adds a link to the title
		*
		*	@access		public
		*	@param		string $link
		*	@return 	Stamp_Title 
		*/
		public function addLink($link){
				$this->_link = $link;
				return $this;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		
		/**
		*	Turns the debugging mode on and off
		*
		*	@access	public
		*	@param 	bool $flag switch
		*	@return 	Nerb_Auth 
		*/
		public function debug($flag=true){
				$this->_debug = (bool) $flag;
				return $this;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		

		
		
		

		/**
		*	returns formatted catalog number for this stamp
		*
		*	@access		public
		*	@return 	string 
		*/
		public function cat(){
				return $this->_catalog;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		

		/**
		*	returns unformatted titlestring for this stamp
		*
		*	@access		public
		*	@return 	string 
		*/
		public function rawTitle(){
				return strip_tags($this->_title);
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		

		/**
		*	formats the catalog number into a string
		*
		*	@access		protected
		*	@return 	void 
		*/
		protected function _cat(){
			$this->_catalog = $this->_params['showClass']?strtoupper($this->_stamp->class):NULL;
			$this->_catalog .= $this->_stamp->catalogNumber.($this->_stamp->seten?"-".str_pad($this->_stamp->seten, 2, "0",STR_PAD_LEFT):NULL);
			$this->_catalog .= $this->_stamp->var?$this->_stamp->var:NULL;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		

		/**
		*	creates the raw title string
		*
		*	@access		public
		*	@return 		void 
		*/
		protected function _title(){
			
			// clear title
			$this->_cat();
			$this->_title = NULL;
			
			// define scott numbering if cat is set
			if($this->_params['showCat']){
					$this->_title .= $this->_stamp->catalogNumber.($this->_stamp->var?$this->_stamp->var:NULL)." ";	
			}
			
			if($this->_params['showYear']) $this->_title .= $this->_stamp->year." ";
			if($this->_params['showDenom']){
				if($this->_stamp->denomination == 0.005){
					$this->_title .= "&frac12;&cent; ";	
				}elseif($this->_stamp->denomination < 1){
					$cur = ($this->_stamp->denomination*100)."&cent; ";	
					$this->_title .= str_replace(".5", "&frac12;", $cur);
				} else {
					$this->_title .= "$".number_format($this->_stamp->denomination, 2)." ";	
				}
			} 
			
			$desc = stripslashes($this->_stamp->title);
			$desc = $this->_params['strongTitle']?"<b>".$desc."</b>":$desc;
			$this->_title .= $desc;
			if($this->_params['showSubtitle'] && $this->_stamp->subtitle) $this->_title .= ", <i>".stripslashes($this->_stamp->subtitle)."</i>";
			
			
			if($this->_params['showColor'] 
				&& $this->_stamp->color 
				&& !stristr($this->_stamp->color, 'mult')) $this->_title .= ", <i>".stripslashes($this->_stamp->color)."</i>";
			
			if($this->_params['showPress'] && $this->_stamp->press) $this->_title .= ", <i>".stripslashes($this->_stamp->press)."</i>";
			if($this->_params['showPerf'] && $this->_stamp->h_perf>0) $this->_title .= ", <i>perf&nbsp;".stripslashes($this->_stamp->h_perf)."</i>";
			if($this->_params['showPaper'] && $this->_stamp->paper) $this->_title .= ", <i>".stripslashes($this->_stamp->paper)."</i>";
			if($this->_params['showWatermark'] && $this->_stamp->watermark && $this->_stamp->watermark != "Unwatermarked") $this->_title .= ", <i>".stripslashes($this->_stamp->watermark)." watermark</i>";
			
			if($this->_params['showIssueDate'] && $this->_stamp->issue_date) $this->_title .= ", <i>".stripslashes($this->_stamp->issue_date)."</i>";
			if($this->_params['showIssueLocation'] && $this->_stamp->issue_location) $this->_title .= ", <i>".stripslashes($this->_stamp->issue_location)."</i>";
			if($this->_params['showDesigner'] && $this->_stamp->designer) $this->_title .= ", Designer: <i>".stripslashes($this->_stamp->designer)."</i>";
			if($this->_params['showEngraver'] && $this->_stamp->engraver) $this->_title .= ", Engr: <i>".stripslashes($this->_stamp->engraver)."</i>";
			if($this->_params['showQty'] && $this->_stamp->issue_qty) $this->_title .= ", <i>".stripslashes($this->_stamp->issue_qty)." issued</i>";
			if($this->_params['showPrinter'] && $this->_stamp->printer) $this->_title .= ", <i>".stripslashes($this->_stamp->printer)."</i>";
			
			
			
			
			
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		

		/**
		*	formats the title string with anchors and classes
		*
		*	@access		public
		*	@return 	void 
		*/
		protected function _format(){

			// define classes
			$class = ''; 
			if($this->_useClasses && ( $this->_highlighted || $this->_multiple	)){
				$class .= $this->_highlighted?" ".$this->_params['classHighlight']:NULL;
				$class .= $this->_multiple?" ".$this->_params['classMultiple']:NULL;
			} else {
				$class = $this->_params['classDefault']; 
			}
			
			// reset the values of the title
			$this->_formatted = NULL;
			
			// defines beginning of stamp
			$this->_formatted .= "<span class='$class'>".$this->_title."</span>";
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		
} /* end class */
?>
