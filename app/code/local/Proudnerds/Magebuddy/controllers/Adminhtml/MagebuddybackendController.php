<?php
class Proudnerds_Magebuddy_Adminhtml_MagebuddybackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Very well"));
	   $this->renderLayout();
    }

    public function databaseAction()
    {
        require_once(Mage::getBaseDir('lib') . '/Magebuddy/MySQLDump/mysqldump.php');

        $database = (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname');
        $host = (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/host');
        $username = (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/username');
        $password = (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/password');

        $dump = new Ifsnop\Mysqldump\Mysqldump(
            'mysql:host=' . $host . ';dbname=' . $database,
            $username,
            $password,
            array(
                'compress'          => Ifsnop\Mysqldump\Mysqldump::GZIP,
                'add-drop-table'    => TRUE
            )
        );

        $dump->start(Mage::getBaseDir('var') . '/db_dump.gz');


        $this->loadLayout();
        $this->_title($this->__("Very well"));
        $this->renderLayout();
    }

    public function sourceAction()
    {
        Mage::helper('magebuddy/data')->sftpWalkFiles(Mage::getBaseDir());
    }
}