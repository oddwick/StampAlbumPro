<?php 

/**
 *	Base class for the Stamp album professional that generates high quality pdf
 *	pages for stamp albums
 *
 *
 * @package    		Stamp Album Pro
 * @class    		Album
 * @version			1.0
 * @author			Dexter Oddwick <dexter@oddwick.com>
 * @copyright  		Copyright (c)2017
 * @license    		http://www.oddwick.com
 *
 * @uses			Page    		
 * @uses			Textblock    		
 * @uses			Title    		
 * @uses			Border    		
 * @uses			Field    		
 * @uses			Group    		
 * @uses			Row    		
 * @uses			Stamp    		
 * @uses			Column    		
 * @uses			Detail    		
 * @uses			Field_Rows    		
 * @uses			Field_TextRows    		
 * @uses			Groups    		
 * @uses			Params    		
 *
 * @todo    		
 *
 */

# include required libraries 
require_once( LIB."/Cezpdf.php" );
require_once( LIB."/Core.php" );
require_once( LIB."/Album.php" );
require_once( LIB."/Page.php" );
require_once( LIB."/Border.php" );
require_once( LIB."/Field.php" );
require_once( LIB."/Stamp.php" );
require_once( LIB."/Title.php" );
require_once( LIB."/Params.php" );
require_once( LIB."/Group.php" );
require_once( LIB."/Row.php" );
require_once( LIB."/Textblock.php" );
require_once( LIB."/Column.php" );
			
			

class Album {
		
		/**
		*	@var	Border $_border
		*	@var	Param $params
		*	@var	Ezpdf $_pdf
		*	@var	int $_x  current x position of the cursor
		*	@var	int $_y  current y position of the cursor
		*	@var	int $_min_x
		*	@var	int $_min_y
		*	@var	int $_page keeps track of the number of pages 
		*/
		protected	$_border; // border object
		protected 	$params; // param object
		protected 	$_pdf; // ezpdf object
		protected 	$_page; // page object
		protected 	$_fields = array(); // dataset for pages
		protected 	$_groups = array(); // dataset for stamps
		protected 	$_x = 0;
		protected 	$_y = 0;
		protected 	$_min_x = 0;
		protected 	$_min_y = 0;
		protected 	$_page_number = 1; // keeps track of the number of pages 


		/**
		*	Constructor initiates Album object
		*
		*	@access		public
		*	@return 	Album
		*/
		public function __construct(){
			
			# Params object is mandatory and will fail withou it
			if( !Nerb::isRegistered( "params" ) ){
				throw new Nerb_Error( "Object <code>Params</code> is not registered." );
			}


			# assign parameter object to $_param variable
			$this->params = Nerb::fetch( "params" );
			

			# get paper size and determine dimensions
			$paper = $this->params->value( "paper_size", $this->params->paper_size );
			

			# convert the paper size in mm to points and set the max x|y variables 
			$this->params->max_x = $paper["width"] * $this->params->mm2point; // set width
			$this->params->max_y = $paper["height"] * $this->params->mm2point; // set height
			
			
			# flip x|y variables to match page orientation
			if( $this->params->orientation == "landscape"){
				$swap = $this->params->max_y;
				$this->params->max_y  = $this->params->max_x;
				$this->params->max_x  = $swap;
			} // end if 
			
			
			# instantiate new pdf object 
			# the paper height and width are given in mm and passed as an array,
			# and the orientation of the page is is determined by $params->orientation
			# by default, the orientation is portrait unless otherwise specified
			$this->_pdf = new Cezpdf( array( $paper["width"], $paper["height"] ) , $this->params->orientation );
			
			
			# embed publisher information
			$this->_pdf->addInfo( $this->params->value( "info" )  );
			
			
			# register the pdf object for other objects to access
			Nerb::register( $this->_pdf, "pdf" );	
					
				
			# set system font and calculate the actual size of a caption
			$this->_pdf->selectFont( RESOURCES."/fonts/".$this->params->system_font );
			$this->params->caption_height = $this->_pdf->getFontHeight( $this->params->caption_font_size );
			
			
			# instantiate and register the border for other classes to use
			Nerb::register( $this->_border = new Border(),  "border" );
			
			# set base font
			$this->_pdf->selectFont( RESOURCES."/fonts/".$this->params->default_font.".afm", array( "encoding" => "WinAnsiEncoding" ) );
			
			
			# create a new page object
			$this->_page = new Page();
				
			# get current cursor position
			$this->_y = $this->_pdf->ezGetY();
			
			return $this;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		


			
		/**
		*	Streams the contents of the pdf object to the browser
		*
		*	@access		public
		*	@return 	void
		*/
		public function show(){
			
			#set header and stream pdf contents
			header("Content-type: application/pdf"); 	
			$this->_pdf->ezStream();
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		

	

		/**
		*	returns the number of pages in the document to this point
		*
		*	@access		public
		*	@return 	int
		*/
		public function pages(){
			
			return $this->_page_number;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		

		
		/** 
		*	uses imagemagick to create a pdf thumbnail
		*	returns the image as filename-0.jpg, filename-1.jpg etc where -0 is the coverpage
		*
		*	@access		public
		*	@param		string $dir
		*	@param		string $filenaem 
		*	@return 	void
		*/
		public function thumbnail( $dir, $filename, $size = 100 ){
			
			//$pdf = $filename.".pdf";
			//$jpg = $thumbname.".jpg";
			return exec( "convert ".$dir."/".$filename.".pdf -colorspace sRGB -resize ".$size." -background white -alpha remove ".$dir."/".$filename.".jpg" );
			//$imagick = new Imagick(); 
			//$imagick->readImage("myfile.pdf[0]"); 
			//$imagick = $imagick->flattenImages(); 
			//$imagick->writeFile("pageone.jpg"); 
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		

		
		/**
		*	Writes the contents of the pdf object to the file specified
		*
		*	@access		public
		*	@param		string $file full path and name of the file being written
		*	@return 	bool
		*/
		public function write( $file ){
			
			// open file for writing
			if( $handle = fopen( $file, "w")){
				//empty contents
				$contents = $this->_pdf->ezOutput();
				// write contents
				$result = @fwrite( $handle, $contents);
				// close and return
				@fclose( $handle);
				return $result;
			
			} else {
				return false;
			}
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		
		/**
		*	writes the coversheet to the first page of the pdf
		*
		*	this MUST be called before any of the pages are generated, but after
		*	the stamps and groups are added to the album
		*
		*	@access		public
		*	@return 	Album 
		*/
		public function coversheet(){
		
			# set the insert mode, move the pointer to the beginning of the pdf & create new page
			$this->_pdf->ezInsertMode();		
			$this->_pdf->ezNewPage();
			
			
			# initial page setup
			# force borderstyle
			$this->_border->write( 6 );
			
			
			# determine what is 1/3 of the page so that no matter the orientation, the title will
			# always appear in the upper third of the page
			$vert_third = $this->params->max_y - ( $this->params->max_y / 3 );
			$horiz_third = ( $this->params->max_x / 3 );
			
			
			# get title image and image dimensions
			$image = RESOURCES.$this->params->splash_image;
			$image_info = @getimagesize( $image);
			$width = ceil( $image_info[0] / 1.3 ); //set width
			$height = ceil( $image_info[1] / 1.3 ); //set height
			
			
			# add splash image
			$this->_pdf->addJpegFromFile( $image, 
										  ( $this->params->max_x / 2 ) - ( $width / 2 ), // calculate center of page and subtract image center offset
										  $vert_third - ( $height / 2 ), 
										  $width, 
										  $height );
			
			
			# add logo
			if( $this-> params-> show_logo ){
				$this->_pdf->addJpegFromFile( RESOURCES."/etc/logo.jpg", $this->params->max_x - 170, $this->params->min_y + 50, 100 );
			}
			
			# orce title and text margins regardless of what user sets margins at
			$this->_pdf->ezSetmargins( $vert_third - $height,
									   60,
									   $horiz_third,
									   $horiz_third);
			
			# reset the cursor to the top the column
			$this->_pdf->ezSetY( $vert_third - ( $height /1.5 ) );
			$this->_pdf->ezSetDy( -10 );
			
			
			// set font and color and write out the page specifications
			$this->_pdf->setColor(0, 0, 0);
			$this->_pdf->selectFont( RESOURCES."/fonts/Helvetica.afm" );
			$this->_pdf->ezText( $this->params->title, $this->params->default_font_size + 8, array("text_justify" =>"center") );				
			$this->_pdf->ezSetDy( -15 );
			$this->_pdf->ezText( "Generated: ".$this->params->value( "info", "CreationDate" ), $this->params->default_font_size, array("text_justify" =>"center"));	
			
			
			if( $this->params->page_number && $this->_page_number > 1){
				$pages = "  (".( $this->params->page_number - $this->_page_number)."-".( $this->params->page_number - 1 ).")";
			} // end if
			
			$this->_pdf->ezText( $this->_page_number." Page(s)".$pages, $this->params->default_font_size, array( "text_justify" => "center" ) );		
			
			
			# print out a list of the stamps included in this page
			$this->_pdf->ezSetDy(-15);
			$this->_pdf->ezSetmargins( 0, 80, 100, 100 ); 
			$this->_pdf->ezColumnsStart( array( "num" => 2, "gap" => 10) );
			
			
			foreach ( $this->_groups as $group ){
				foreach( $group as $stamp ){
					
					$this->_pdf->ezText( $stamp->caption(), 6, array( "text_justify" => "left" ) );
					
				} // end for each stamp
			} // end for each groups
					
			$this->_pdf->ezColumnsStop();
			
			return $this;
				
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------

		
		
		
		
		
		/**
		 *	default layout of simple rows.  this method is overriden in other layouts that extend the Album object
		 *
		 * @access public
		 * @param array $groups
		 * @param array $text (default: array())
		 * @return void
		 */
		public function layout( array $groups, $text = array() ){
			
			# get the available dimensions of the field
			# and calculate the max dimensions
			$this->_page->page();
			
			# add the stamps from the session to the album
			$this->_makeGroups( $groups, $text );
			
			# iterate through the groups, extract the stamps and create an array of row objects
			foreach( $this->_groups as $group){
				$group->write( $this->_page->field( "min_x" ) , $this->_page->field( "min_y" ) );
			} // end of groups
			
			// begin adding the rows to pages
			
			// increment page number counter.
			///++$this->_page_number;
			//$data = $page->page( $append = true);
			
			
			
			
			return;
			
			
			
			
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
					++$this->_page_number;
					// create a new field
					$field = new Field( $data );
					// roll back the row counter
					--$i;
				}
			}
			
			// write out the last page
			$field->write();
			
			return $this;
			//die;
		
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		
		
		
		/**
		*	adds stamps to the album data object
		*
		*	@access		public
		*	@param		array $groups $_SESSION[groups]
		*	@param		string $text
		*	@return 	Album
		*/
		protected function _makeGroups( array $groups, $text = array() ){
			
			# iterate groups array and convert them into group objects
			foreach( $groups as $id => $group ){
				
				# transfer stamps into group object array
				$this->_groups[$id] = new Group( $group, $text, $this->_page->field() );
			
			}// end foreach

			return $this;
			
		} // end function -----------------------------------------------------------------------------------------------------------------------------------------------
		

		
		
		
} // end class
?>
