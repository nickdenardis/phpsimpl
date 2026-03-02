<?php namespace Simpl;

/**
 * Base Folder Class
 *
 * Used to manipulate folders on the server
 *
 * @author 	Nick DeNardis <nick.denardis@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class Folder {
    /**
     * @var string
     */
    public $directory;
    /**
     * @var string
     */
    protected $folder_name;

    /**
     * Folder Constructor (PHP 5+)
     */
    public function __construct($folder_name, $directory='') {
        $this->Folder($folder_name, $directory);
    }

    /**
     * Folder Constructor (Legacy PHP 4)
     *
     * @param $folder_name String containing the folder name that is in question
     * @param \Simpl\The|string $directory The directory where the file is sitting
     * @return null
     */
    public function Folder($folder_name, $directory=''){
        Debug('Constructor(), Initializing values');
        $this->folder_name = $folder_name . ((substr($folder_name,-1) != '/')?'/':'');

        // If there is directory passed, set the directory
        if (isset($directory) && $directory != '')
            $this->directory = $directory . ((substr($directory,-1) != '/')?'/':'');
    }

    /**
     * Move the folder
     *
     * Move the folder to another location
     *
     * @param $new_directory string the directory to which we are moving the folder
     * @return bool
     */
    public function Move($new_directory){
        // If the new directory exists and is writable
        if (is_dir($new_directory) && (is_writable($new_directory))){
            // If no folder with the same name exists in the new directory
            if (!is_dir($new_directory . $this->folder_name) ){
                // Move the folder to the new directory
                if (rename($this->directory . $this->folder_name, $new_directory . $this->folder_name) ){
                    Debug('Move(), Moving the folder from ' . $this->directory . ' to ' . $new_directory);
                    if (chmod($new_directory . $this->folder_name, 0775)){
                        Debug('Move(), Changing permissions for folder ' . $this->folder . ' in directory ' . $new_directory);
                        $this->directory = $new_directory;
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Check if the function is writable
     *
     * The function checks if the folder exists and if it is writable
     *
     * @return bool
     */
    public function IsWritable(){
        // If the folder exists
        if (is_dir($this->directory . $this->folder_name)){
            // Check if it is writable
            if(is_writable($this->directory . $this->folder_name)){
                Debug('IsWritable(), The folder ' . $this->directory . $this->folder . ' is writable.');
                return true;
            }
        }
        return false;
    }

    /**
     * Format the Folder
     *
     * @return bool
     */
    public function Format(){
        // Remove the ending slash
        $folder_name = (substr($this->folder_name,-1) == '/')?substr($this->folder_name,0,-1):$this->folder_name;

        // Make sure there is a folder name set
        if (trim($folder_name) == '')
            return false;

        // Cut out bad chars
        //$fname = preg_replace("/[^A-Za-z0-9-]/i","_",$fname);

        // Cut out bad chars
        $bad_chars = array(' ', "'", '\'', '(', ')', '*', '!', '/', ',', '&', '|', '{', '}', '[', ']', '+', '=', '<', '>');
        $folder_name = str_replace($bad_chars, '_', trim($folder_name));

        // Remove doubles
        $this->folder_name = str_replace('__', '', $folder_name);

        return true;
    }

    /**
     * Make the folder writable
     *
     * @return bool
     */
    public function MakeWritable(){
        // If the folder exists
        if (@is_dir($this->directory . $this->folder_name)){
            // If it is already writable return true
            if (@is_writable($this->directory . $this->folder_name)){
                Debug('MakeWritable(), The folder ' . $this->directory . $this->folder . ' is already writable.');
                return true;
            }else{
                // Change permissions of the folder
                if (@chmod($this->directory . $this->folder_name, 0775)){
                    Debug('MakeWritable(), Changing permissions of folder ' . $this->directory . $this->folder);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Delete subfolders and files recursively
     *
     * Private function that deletes the sub files and sub-folders. the function is called from Delete function
     *
     * @param $directory directory to be deleted
     * @return bool
     */
    private function delete_recursive($directory){
        // Loop through for each directory/file that the function scandir returns
        foreach (scandir($directory) as $folderItem) {
            Debug('delete_recursive(), Looping through directory ' . $directory);
            // Skip for these two cases
            if ($folderItem != "." AND $folderItem != ".."){
                // If file is a directory
                if (is_dir($directory. $folderItem . '/')){
                    // Call the function recursively
                    $this->delete_recursive($directory . $folderItem . '/');
                }else{
                    // Delete the files within the directory
                    Debug('delete_recursive(), Deleting file ' . $directory . $folderItem);
                    unlink($directory . $folderItem);
                }
            }
        }
        // Delete the sub-directories and the directory itself
        Debug('delete_recursive(), Deleting directory ' . $directory);
        rmdir($directory);

        return true;
    }

    /**
     * Deletes the folder
     *
     * @todo on force check for subfolders also
     *
     * @param @force bool if the parameter is true the function deletes all the subfiles of the folder also
     * @return bool;
     */
    public function Delete($force=false){
        if ($force == false){
            //delete the directory
            if (@rmdir($this->directory . $this->folder_name)){
                Debug('Delete(), Deleting directory ' . $this->directory . $this->folder_name);
                return true;
            }
        }else{
            Debug('Delete(), Deleting sub-folders and sub-files recursively.');
            //call this function to delete the sub folders and sub files recursively
            if ($this->delete_recursive($this->directory . $this->folder_name) ){
                return true;
            }
        }
        return false;
    }

    /**
     * Directory Listing
     *
     * Lists the subfolders and files of directory
     *
     * @param NULL
     * @return array of the sub-folders and sub-files
     */
    public function DirList(){
        $files = scandir($this->directory . $this->folder_name);
        if (is_array($files)){
            foreach($files as $pos => $file){
                if (($file == '.') || ($file == '..'))
                    unset($files[$pos]);
            }
            Debug('DirList(), Getting a list of files in directory ' . $this->directory);
            return $files;
        }
        return false;
    }

    /**
     * Renames a folder
     *
     * @param $new_folder string name of the new folder
     * @return bool
     */
    public function Rename($new_folder){
        // If the folder exists
        if (is_dir($this->directory . $this->folder_name)){
            // If folder with the new name doesnt already exist
            if (!is_dir($this->directory . $new_folder)) {
                // Rename the folder to the new name
                if (rename($this->directory . $this->folder_name, $this->directory . $new_folder)){
                    Debug('Rename(), Renaming folder ' . $this->directory . $this->folder_name . ' to ' . $this->directory . $new_folder);
                    if (substr($new_folder,-1) == '/'){
                        $this->folder_name = $new_folder;
                    }else{
                        $this->folder_name = $new_folder . '/';
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Checks if the folder exists in the filesystem
     *
     * @param NULL
     * @return bool
     */
    public function Exists(){
        // If the folder exists
        Debug('Exists(), Checking if the folder ' . $this->folder_name . ' exists in the directory ' . $this->directory);
        if (@is_dir($this->directory . $this->folder_name))
            return true;

        return false;
    }

    /**
     * Set the folder name
     *
     * @param string $name
     * @return bool
     */
    public function SetFolderName($name){
        $this->folder_name = $name . ((substr($name,-1) != '/')?'/':'');
        return true;
    }

    /**
     * Get the folder name
     *
     * @return bool
     */
    public function GetFolderName(){
        return ((substr($this->folder_name,-1) != '/')?$this->folder_name:substr($this->folder_name,0,-1));
    }

    /**
     * Creates a folder
     *
     * Creates a folder and makes it writable
     *
     * @param NULL
     * @return bool
     */
    public function Create(){
        // If the folder exists make it writable
        if (@is_dir($this->directory . $this->folder_name)){
            Debug('Create(), The folder ' . $this->folder_name . ' already exists in the directory ' .$this->directory);
            // Change persmissions of folder
            if (@chmod($this->directory . $this->folder_name, 0775)){
                return true;
            }else{
                Debug('Create(), Cout not change permissions of ' . $this->folder_name . ' in directory ' . $this->directory);
            }
        }else{
            // Create the folder
            if (@mkdir($this->directory . $this->folder_name)){
                Debug('Create(), Created folder ' . $this->folder_name . ' in directory ' . $this->directory);
                // Change persmissions of the folder
                if (@chmod($this->directory . $this->folder_name, 0775)){
                    return true;
                }else{
                    Debug('Create(), Cout not change permissions of ' . $this->folder_name . ' in directory ' . $this->directory);
                }
            }
        }

        return false;
    }
}