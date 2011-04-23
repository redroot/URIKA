<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Mime Helper file, due to poor native support in php
 */
 
/* 
	Determine the mime type of the specified file by any means possible.
 */
function mime_get_type($filename) 
{
  // verify that the file exists and is readable
  if (!is_readable($filename))
    return false;

  // try the php GetImageSize() function
  $mt = mime_get_type_GetImageSize($filename);
  if ($mt) return mime_get_type_specialize($mt);

  // try the /etc/mime-magic file (if it exists)
  $mt = mime_get_type_etc_mime_magic($filename);
  if ($mt) return mime_get_type_specialize($mt);

  // try the /etc/mime.types file (if it exists)
  $mt = mime_get_type_etc_mime_types($filename);
  if ($mt) return mime_get_type_specialize($mt);

  // use the built-in mime type table
  $mt = mime_get_type_builtin($filename);
  if ($mt) return mime_get_type_specialize($mt);

  // failed
  return false;
}


/* Return array of known (popular, interesting) mime types.
 */
function mime_known_types() 
{
  return array(
		   "application/x-gtar", // gzipped tar file containing images
	       "application/x-tar",  // tar file containing images
	       "application/zip",    // zip file containing images
	       "image/gif",          // gif image; animated or not
	       "image/jpeg",         // jpeg image
	       "image/png",          // png image
	       "image/tiff",         // tiff image
	       "image/x-kdc",        // Kodak Digital Camera image
	       "image/x-pcd",        // Kodak PhotoCD image
	       "video/mpeg",         // mpeg video file
	       "video/x-mng",        // multi-image png file
	   );
}


/* Check that $mt is (syntactically) a valid mimetype.
 */
function mime_valid_type($mt) 
{
  $categories = array("application",
		      "audio",
		      "chemical",
		      "image",
		      "inode",
		      "message",
		      "model",
		      "multipart",
		      "text",
		      "video",
		      );
  if (!ereg("^([a-z][a-z]*)/([0-9A-Za-z][0-9A-Za-z.+-]*)$",$mt,$regs))
    return false;
  if (!in_array($regs[1],$categories))
    return false;
  return true;
}

// figure out mime type using GetImageSize() function
function mime_get_type_GetImageSize($filename) 
{
  $type_mapping = 
    array("1"=>"image/gif",
	  "2"=>"image/jpeg",
	  "3"=>"image/png",
	  "4"=>"application/x-shockwave-flash",
	  //"5"=>"PSD",
	  "6"=>"image/bmp");
  @$size = GetImageSize($filename);
  if ($size[2]&&$type_mapping[$size[2]])
    return $type_mapping[$size[2]];
  return false;
}

// figure out mime type from file data using /etc/mime-magic
function mime_get_type_etc_mime_magic($filename) 
{
  if (filesize($filename)<=0)
    return false;
  // WORK NEEDED HERE!
  return false;
}

// figure out mime type from filename extension using /etc/mime.types
function mime_get_type_etc_mime_types($filename) 
{
  // WORK NEEDED HERE!
  return false;
}

// figure out mime type from filename using built-in table
function mime_get_type_builtin($filename) 
{
  $extensions = 
    array("tgz"=>"application/x-gtar",
	  "tar.gz"=>"application/x-gtar",
	  "tar"=>"application/x-tar",
	  "zip"=>"application/zip",
	  "gif"=>"image/gif",
	  "jpeg"=>"image/jpeg",
	  "jpg"=>"image/jpeg",
	  "jpe"=>"image/jpeg",
	  "png"=>"image/png",
	  "tiff"=>"image/tiff",
	  "tif"=>"image/tiff",
	  "kdc"=>"image/x-kdc",
	  //""=>"image/x-pcd",
	  "mpeg"=>"video/mpeg",
	  "mpg"=>"video/mpeg",
	  "mpe"=>"video/mpeg",
	  "mng"=>"video/x-mng");
  reset($extensions);
  while (list($ext,$mt)=each($extensions))
    if (eregi("[.]".$ext."$",$filename))
      return $mt;
  return false;
}

// determine if mime type should be specialized
function mime_get_type_specialize($mt) 
{
  if ($mt=="image/tiff") {
    // check for image/x-kdc
  }
  return $mt;
}

/* End of file mime_helper.php */
/* Location: ./system/helpers/mime_helper.php */