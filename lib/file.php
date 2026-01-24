<?php namespace Simpl;

/**
 * Base File Class
 *
 * Used to manipulate files on the server
 *
 * @author Nick DeNardis <nick.denardis@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class File extends Folder {
    /**
     * @var string
     */
    public $filename;

    /**
     * File Constructor (PHP 5+)
     */
    public function __construct($filename, $directory='') {
        $this->File($filename, $directory);
    }

    /**
     * File Constructor (Legacy PHP 4)
     *
     * @param $filename    String containing the filename that is in question
     * @param string $directory
     * @internal param \Simpl\The $direcotry directory where the file is sitting
     * @return null
     */
    public function File($filename,$directory=''){
        Debug('Constructor(), Intitializing values');

        // Set the Local variables
        $this->filename = $filename;

        // If there is directory passed, set the directory
        if (isset($directory)) {
            // Set the Local variables
            $this->directory = $directory . ((substr($directory,-1) != '/')?'/':'');
        }
    }


    /**
     * Move the file
     *
     * Move the file to another location
     *
     * @param $new_directory string the directory to which we are moving the file
     * @return bool
     */
    public function Move($new_directory) {
        //if the file exists in the directory
        if(is_file($this->directory . $this->filename)) {
            //if a file with the same name does not exist in the new directory
            if(!is_file($new_directory . $this->filename)) {
                //move the file over to the new directory
                Debug('Move(), From: "' . $this->directory . '" To: "' . $new_directory . '"');
                if(rename($this->directory . $this->filename, $new_directory . $this->filename)) {
                    //change persmissions of the file in the new directory
                    Debug('Move(), Changing permissions of the file: ' . $new_directory . $this->filename);
                    if(chmod($new_directory . $this->filename, 0775)) {
                        //update the directory in the class
                        Debug('Move(), Updating the directory variable from: ' . $this->directory . ' To: ' . $new_directory);
                        $this->directory = $new_directory;
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Copy the file
     *
     * Copy the file in another directory
     *
     * @param $new_directory string the directory to which we a re moving the file
     * @return bool
     */
    public function Copy($new_directory) {
        //if the file exists in the current directory
        if(is_file($this->directory . $this->filename)) {
            //if a file with the same name doesnot exist in the new directory
            if(!is_file($new_directory . $this->filename)) {
                //copy the file to the new directory
                Debug('Copy(), From: "' . $this->directory . '" To: "' . $new_directory . '"');
                if(copy($this->directory . $this->filename, $new_directory . $this->filename)) {
                    //update the file permissions in the new directory
                    Debug('Copy(), Changing permissions of the file: ' . $new_directory . $this->filename);
                    if(chmod($new_directory . $this->filename, 0775)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Rename the file
     *
     * @param $new_filename string the name to which we want to rename the file
     * @result bool
     */
    public function Rename($new_filename) {
        //if the file exists in the current directory
        if(is_file($this->directory . $this->filename)) {
            //copy the filename into the variable
            $old_filename = $this->filename;
            //assign the new_filename to the class variable
            $this->filename = $new_filename;
            //format the new filename
            $this->FormatFilename();
            //rename the file
            Debug('Rename(), From: "' . $old_filename . '" To: "' . $this->filename . '"');
            if (rename($this->directory . $old_filename, $this->directory . $this->filename))
                return true;
        }
        return false;
    }

    /**
     * If the file is writable
     *
     * @param NULL
     * @return bool
     */
    public function IsWritable() {
        //if the file exists in the current directory
        if(is_file($this->directory . $this->filename)) {
            //if the file is writable
            if(is_writable($this->directory . $this->filename)) {
                Debug('IsWritable(), The file ' . $this->directory . $this->filename . ' is writable.');
                return true;
            }
        }
        return false;
    }

    /**
     * Make the file writable
     *
     * @param NULL
     * @result bool
     */
    public function MakeWritable() {
        if(is_file($this->directory . $this->filename)) {
            //if the file is writable return true
            if(is_writable($this->directory . $this->filename)) {
                Debug('MakeWritable(), The file ' . $this->directory . $this->filename . ' is already writable.');
                return true;
            }else{
                //change persmissions
                Debug('MakeWritable(), Changing permissions of file ' . $this->directory . $this->filename);
                if(chmod($this->directory . $this->filename, 0775)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get extension of the file
     *
     * @param NULL
     * @return string extension of the file
     */
    public function GetExtension() {
        if(is_file($this->directory . $this->filename)) {
            //get position of the last dot in the filename
            $pos = strrpos($this->directory . $this->filename, '.');
            //return whatever is there after the last dot in the filename
            Debug('GetExtension(), Getting extension of file ' . $this->directory . $this->filename);
            return substr($this->directory . $this->filename, $pos+1);
        }
        return false;
    }

    /**
     * Delete the file
     *
     * @param NULL
     * @result bool
     */
    public function Delete($force=false) {
        if(is_file($this->directory . $this->filename)) {
            //delete the file
            Debug('Delete(), Deleting file ' . $this->directory . $this->filename);
            if(!unlink($this->directory . $this->filename)){
                return false;
            }
        }
        return true;
    }

    /**
     * Date when the file was last modified
     *
     * Returns the date time in SQL format (as Y-m-d H:i:s)
     *
     * @param NULL
     * @result bool
     */
    public function LastModified() {
        // Make sure the file exists
        if(is_file($this->directory . $this->filename)) {
            Debug('LastModified(), Last modified time of file ' . $this->directory . $this->filename);
            return date("Y-m-d H:i:s",filemtime($this->directory . $this->filename));
        }
        return false;
    }

    /**
     * If the file with the given name exists
     *
     * @param NULL
     * @return bool
     *
     */
    public function Exists() {
        // If the file exists in the current directory
        if(is_file($this->directory . $this->filename)) {
            Debug('Esixts(), The file ' . $this->directory . $this->filename . ' exists.');
            return true;
        }
        return false;
    }

    /**
     * Get the contents of the file in a string
     *
     * @param NULL
     * @return bool
     */
    public function GetContents() {
        // If the file exists in the directory
        if(is_file($this->directory . $this->filename)) {
            // Return contents of the file in a string
            Debug('GetContents(), Get the contents of the file ' . $this->directory . $this->filename . ' into a string.');
            return file_get_contents($this->directory . $this->filename);
        }
        return false;
    }

    /**
     * Formats the filename
     *
     * @param NULL
     * @return bool
     */
    public function FormatFilename() {
        // Rip it apart
        $pieces = explode('.', $this->filename);
        $fext  = array_pop($pieces);
        $fname = basename($this->filename, '.'.$fext);

        // Cut out bad chars
        $fname = preg_replace("/[^A-Za-z0-9-]/i","_",$fname);

        Debug('FormatFilename(), Removing bad characters from the filename ' . $this->filename);

        $i = 1;
        while (file_exists($this->directory . $fname . '.' . $fext) ){
            // if already had a number appended cut it off
            if ($i > 1)
                $fname = substr($fname, 0, -2);

            // Add a number to the end of the file
            $fname =  $fname . '_' . $i;
            $i++;
        }
        Debug('FormatFilename(), Add a number to the end of the filename ' . $this->filename);
        // Recreate the file name with extention
        $this->filename = $fname . '.' . $fext;

        return true;
    }


    /**
     * Get the size of the file
     *
     * @param NULL
     * @return int size of file in bytes
     */
    public function Filesize() {
        //if the file exists in the directory
        if(is_file($this->directory . $this->filename)) {
            //return the size in number of bytes
            Debug('Filesize(), Get size of file ' . $this->directory . $this->filename);
            return filesize($this->directory . $this->filename);
        }
        return false;
    }
}