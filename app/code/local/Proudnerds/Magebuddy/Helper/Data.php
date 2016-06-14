<?php
class Proudnerds_Magebuddy_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $remotePath = '/var/www/vhosts/magebuddy/testdump';
    protected $remoteUrl = '10.0.0.12';
    protected $username = "wilbert";
    protected $password = "wolf3111";

    protected $endPoints = array();
    protected $files = array();
    protected $fileIgnore = array('.', '..', '.gitignore');

    public function investigateDirectories($path, $originalPath, $i = 0)
    {
        $subdirectories = array();

        foreach(glob($path . '/{,.}*', GLOB_BRACE) as $file) {
            if(in_array(basename($file), $this->fileIgnore))
                continue;

            $type = filetype($file);

            if ($type == 'dir' || $type == 'link') {
                $subdirectories[] = $file;
            }
            else
                $this->files[$path][] = $file;
        }

        if(count($subdirectories))
            foreach($subdirectories as $dir) {
                $this->investigateDirectories($dir, $originalPath, $i + 1);
            }
        else {
            $localDir = str_replace($originalPath, '', $path);
            $localDir = trim($localDir, '/');
            $this->endPoints[] = $localDir;
        }
    }

    public function sftpWalkFiles($originalPath)
    {
        $time_pre = microtime(true);

        set_include_path(get_include_path().PS.Mage::getBaseDir('lib').DS.'Magebuddy'.DS.'phpseclib');

        require_once(Mage::getBaseDir('lib').DS.'Magebuddy'.DS.'phpseclib'.DS."Net".DS."SFTP.php");

        $sftp = new  Net_SFTP($this->remoteUrl);

        echo 'Loggin in with user "'.$this->username.'"<br>';
        if (!$sftp->login($this->username, $this->password)) {
            echo "(false)";
            exit;
        }

        $sftp->chdir($this->remotePath . DIRECTORY_SEPARATOR);

        $this->investigateDirectories($originalPath, $originalPath);

        foreach($this->endPoints as $dir)
        {
            $sftp->mkdir($dir, -1, true);
        }

        foreach($this->files as $dir => $files)
        {
            $localDir = str_replace($originalPath, '', $dir);
            $localDir = trim($localDir, '/');

            $sftp->chdir($this->remotePath . DS . $localDir);

            foreach($files as $file)
                $sftp->put(basename($file), $file, NET_SFTP_LOCAL_FILE);
        }

        $time_post = microtime(true);
        $exec_time = $time_post - $time_pre;

        echo "<br>";
        echo $exec_time;
        echo "<br>";
    }
}
