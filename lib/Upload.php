<?php

namespace Simpl;

/**
 * Base Upload Class
 *
 * Upload Class Used for Uploading all kinds of files, can be extended to fit specific needs
 *
 * @author Nick DeNardis <nick.denardis@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class Upload extends File
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $tmp_name;
    /**
     * @var int
     */
    private $error;
    /**
     * @var int
     */
    private $size;

    /**
     * Class Constructor
     *
     * Creates a upload object from form data
     *
     * @param $data
     * @param string $directory
     * @internal param $mixed
     * @return \Simpl\Upload
     */
    public function __construct(array $data, $directory='')
    {
        $this->filename = a($data, 'name');
        $this->type = a($data, 'type');
        $this->tmp_name = a($data, 'tmp_name');
        $this->error = a($data, 'error');
        $this->size = a($data, 'size');
        $this->directory = $directory;
    }

    /**
     * Checks the FILE data for and errors, overloads the Form CheckData
     * $acceptedTypes = array of acceptable file types
     * $max_size = int size of file in bytes
     * <code>
     * <?php
     * define(MAX_SIZE,1500000);
     * $image_types = array('image/jpeg','image/gif','image/png','image/pjpeg');
     * ?>
     * </code>
     * @param $acceptedTypes
     * @param $max_size
     * @internal param $array , int in bytes
     * @return array
     */
    public function CheckData($acceptedTypes, $max_size)
    {
        // Make sure that we have something to check first
        if ($this->filename != '') {
            Debug('CheckData(), Checking File data for errors.');
            // Check to see if the dirstory is writable
            if (!is_writable($this->directory)) {
                // Try to make it writable
                $path = '';
                foreach (explode('/', $this->directory) as $data) {
                    $path .= $data . '/';
                    if (!file_exists($path)) {
                        umask(000);
                        mkdir($path, 0775);
                    }
                }
            }
            if (!is_writable($this->directory)) {
                $error[] = 'Directory is not writable, please check with the systems administrator';
            }

            // Check file types
            if (!in_array($this->type, $acceptedTypes)) {
                $error[] = 'This type of file is not permitted, Please try again';
            } elseif ($this->size == 0) {
                $error[] = 'The filesize is zero, Please try again';
            } elseif ($this->size > $max_size) {
                $error[] = 'The filesize is too large, ' . $max_size . ' bytes is filesize limit, Please try again';
            }
        } else {
            $error[] = 'No file provided.';
        }

        // Return the Errors
        return $error;
    }

    /**
     * Upload File
     *
     * Moves the file from its temp location and puts it into the directory that is set
     *
     * @return bool
     */
    public function UploadFile()
    {
        // Format the filename to make sure it is directory safe
        $this->FormatFilename();

        // Upload the file
        if (move_uploaded_file($this->tmp_name, $this->directory . $this->filename)) {
            Debug('UploadFile(), File moved successfully to: "' . $this->directory . $this->filename . '"');
            // Make sure it is in the right spot
            if (file_exists($this->directory . $this->filename)) {
                // Change the permissions so the file is not locked
                chmod($this->directory . $this->filename, 0775);
                // Return if the file is uploaded successfully
                if (is_writable($this->directory . $this->filename)) {
                    Debug('UploadFile(), Is Writable! "' . $this->directory . $this->filename . '"');
                    return true;
                } else {
                    Debug('UploadFile(), Is Not Writable! "' . $this->directory . $this->filename . '"');
                }
            }
        }

        // There must have been some issue
        return false;
    }

    /**
     * Upload File Form
     *
     * @param string $options
     * @param string $config
     * @return string
     */
    public function Form($options='', $config='')
    {
        return '<input name="" id="" type="" size="" maxlength="" value="" />';
    }
}
