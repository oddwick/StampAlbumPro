

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


class iStamp_Image  implements Iterator{
		
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
				'imageDir'=> "/var/www/web21/web/stamps/color",
				'imageUrl'=> "http://iAdmin.stampalbumpro.com/stamps/color",
				'thumbUrl'=> "http://iAdmin.stampalbumpro.com/stamps/thumbs",
				'noImageUrl'=> "http://iAdmin.stampalbumpro.com/stamps/image_noImageSmall.gif",
				'lightbox'=> true,
				'classImage'=> NULL,
				'classThumb'=> NULL
			);
		protected $_stamp_id = '';
		protected $_data = array();
		protected $_default = array();
		protected $_imageTable = NULL;
		protected $_pointer = 0;
		
		// debugging variables
		protected $_debug = false;


		/**
		*	Constructor initiates stamp title
		*
		*	@access	public
		*	@param	Nerb_Database_Row $row
		*	@return 	void
		*/
		public function __construct(&$imageTable, $stamp_id=NULL ){
				if(get_class($imageTable) == 'Nerb_Database_Table'){
					$this->_imageTable = $imageTable;
				} else {
					throw new Nerb_Error('No image table was defined');
				}
				
				// if initialized with a stamp, load data
				if($stamp_id) $this->addImage($stamp_id);

				return $this;
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
				return isset($this->_data[$this->_pointer][$key])?$this->_data[$this->_pointer][$key]:NULL;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		/**
		*	prints the current formatted thumbnail.
		*	operates on the assumption that a thumbnail will be displayed more often than the image
		*	or that an image will be linked from a thumbnail
		*
		*	@access	public
		*	@return 	mixed returns the whole parameters array with $class->PARAMETERS or $class->PARAMS
		*/
		public function __toString(){
			return $this->thumb();
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		/**
		*	determines if current image is the default image
		*
		*	@access	public
		*	@return 	bool
		*/
		public function isDefault(){
				return $this->_data[ $this->_pointer ]['default']?true:false;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			 
		/**
		*	returns count of images for the stamp
		*
		*	@access		public
		*	@return 		int
		*/
		public function count(){	
			return count($this->_data);
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------



		/**
		*	returns the url of the current stamp
		*
		*	@access		public
		*	@return 	     string 	stamp url
		*/
		public function current(){	
			// make sure that the pointer points to a valid row
			if($this->valid()){
				return $this->_data[$this->_pointer]['url'];
			} else {
				return false;
			}
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------



		/**
		*	validates the position of the pointer
		*
		*	@access		public
		*	@return 	bool
		*/
		public function valid(){	
			return $this->_pointer<count($this->_data);
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------



		/**
		*	advances the pointer 
		*
		*	@access	public
		*	@return 	int
		*/
		public function next(){	
			return ++$this->_pointer;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------



		/**
		*	decrements the pointer
		*
		*	@access		public
		*	@return 	int
		*/
		public function prev(){	
			return --$this->_pointer;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------



		/**
		*	returns the current position of the pointer
		*
		*	@access		public
		*	@return 	int
		*/
		public function key(){	
			return $this->_pointer;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------



		/**
		*	resets the pointer to 0
		*
		*	@access		public
		*	@return 	int
		*/
		public function rewind(){	
			return $this->_pointer=0;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
		/**
		*	Adds stamp image to the stack, clears values, formats urls
		*
		*	@access		public
		*	@return 		string 
		*/
		public function addImage($stamp_id){
		
				//reset the class
				$this->_data = array();
				$this->_pointer = 0;
				$this->_stamp_id = $stamp_id;
				
				// define where statement
				$where = " `stamp_id` = '$stamp_id' ORDER BY `image_id` ASC";
				// fetch rows
				$images = $this->_imageTable->fetchRows($where);
				
				// exit if no images found
				if($images->count() == 0) {
						$a['url'] = $this->_params['noImageUrl'];
						$a['thumb'] = $this->_params['noImageUrl'];
						$this->_default = $a;
						// disable lightbox
						//$this->_params['lightbox'] = false;
				}
				
				foreach($images as $image){
					$a = $image->__toArray();
					if( file_exists($this->_params['imageDir']."/".$a['image']) ){
						$a['url'] = $this->_params['imageUrl']."/".$a['image'];
						$a['thumb'] = $this->_params['thumbUrl']."/".$a['image'];
					} else {
						$a['url'] = $this->_params['noImageUrl'];
						$a['thumb'] = $this->_params['noImageUrl'];
					}
					$this->_data[] = $a;
					
					if($a['default'] == 1) $this->_default = $a;
				}
				
				return $this;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			
	
		/**
		*	returns formatted thumbnail image string
		*	if the lightbox parameter is true, will format image in a lightbox format
		*
		*	@access		public
		*	@return 		string 
		*/
		public function thumb(){
				return $this->_formatImage($this->_data[$this->_pointer]);
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		/**
		*	returns formatted thumbnail image string
		*	if the lightbox parameter is true, will format image in a lightbox format
		*
		*	@access		public
		*	@return 		string 
		*/
		public function image(){

			// define thumbnail 
				$thumb = "<img src='".$this->_data[$this->_pointer]['thumb']."' class='".$this->_params['classThumb']."'>";
			
			if($this->_params['lightbox']){
				$thumb = "<a rel='lightbox-thumbnail' " 
						   .($this->_data[$this->_pointer]['title']?"title='".$this->_data[$this->_pointer]['title']."' ":NULL)
						   ."href='".$this->_data[$this->_pointer]['url']."'>"
						   .$thumb
						   ."</a>";
			}
			return $thumb;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		
		/**
		*	returns the default image for this stamp
		*
		*	@access	public
		*	@return 	string
		*/
		public function getDefaultImage(){
				return $this->_formatImage($this->_default);
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			 
		/**
		*	returns the default image for this stamp
		*
		*	@access	public
		*	@return 	string
		*/
		public function getDefaultImageName(){
				return $this->_default;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			 
		/**
		*	returns the default thumbnail for this stamp
		*
		*	@access	public
		*	@return 	string
		*/
		public function getDefaultThumb(){
				return $this->_formatImage($this->_default);
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
	

			 
		/**
		*	returns formatted thumbnail image string
		*	if the lightbox parameter is true, will format image in a lightbox format
		*
		*	@access		public
		*	@return 	string 
		*/
		protected function _formatImage($data){

			// define thumbnail 
				$thumb = "<img src='".$data['thumb']."' class='".$this->_params['classThumb']."'>";
			
			if($this->_params['lightbox']){
				$thumb = "<a rel='lightbox-thumbnail' " 
						   .($data['title']?"title='".$this->_data[$this->_pointer]['title']."' ":NULL)
						   ."href='".$data['url']."'>"
						   .$thumb
						   ."</a>";
			}
			return $thumb;
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		

		
		

		
} /* end class */
?>
