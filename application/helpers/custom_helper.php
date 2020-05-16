<?php
function CheckFileUploaded($filename)
{
    if (file_exists(__FILE_UPLOAD_PATH__ . $filename)) {
        return true;
    } else {
        CheckFileUploaded($filename);
    }
}