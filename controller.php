<?php
namespace Concrete\Package\BaclucInvoicePackage;
defined('C5_EXECUTE') or die(_("Access Denied."));
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Package\Package;
use Concrete\Core\Foundation\ClassLoader;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Punic\Exception;
use Loader;
use Core;
use BlockTypeSet;
class Controller extends Package
{
    protected $pkgHandle = 'bacluc_invoice_package';
    protected $appVersionRequired = '5.7.4';
    protected $pkgVersion = '0.0.1';
    protected $pkgAutoloaderRegistries = array(
        //  'src/FieldTypes/Statistics' => '\BasicTablePackage\FieldTypes'
        'src'                      => 'Concrete\Package\BaclucInvoicePackage\Src'
    );
    public function getPackageName()
    {
        return t("Bacluc Invoice Package");
    }
    public function getPackageDescription()
    {
        return t("Package to create Invoices in Concrete5");
    }
    public function install()
    {
        $pre_pkg = Package::getByHandle('basic_table_package');
        if (!is_object($pre_pkg)){
            throw new Exception (t('To Install BaclucInvoicePackage, you have to Install BasicTablePackage first.
            @see <a href=\'https://github.com/BacLuc/basic_table_package\'>https://github.com/BacLuc/basic_table_package</a>'));
        }
        $pre_pkg = Package::getByHandle('bacluc_person_package');
        if (!is_object($pre_pkg)){
            throw new Exception (t('To Install BaclucInvoicePackage, you have to Install BaclucPersonPackage first.
            @see <a href=\'https://github.com/BacLuc/basic_table_package\'>https://github.com/BacLuc/basic_person_package</a>'));
        }
        $pre_pkg = Package::getByHandle('bacluc_product_package');
        if (!is_object($pre_pkg)){
            throw new Exception (t('To Install BaclucInvoicePackage, you have to Install BaclucProductPackage first.
            @see <a href=\'https://github.com/BacLuc/basic_table_package\'>https://github.com/BacLuc/basic_product_package</a>'));
        }
        $em = $this->getEntityManager();
        //begin transaction, so when block install fails, but parent::install was successfully, you don't have to uninstall the package
        $em->getConnection()->beginTransaction();
        try {

            /**
             * @var EntityManager $em
             */


            //add basic_table_package/Src to the folder to look for entitiies
            $em = $this->getEntityManager();

            /**
             * @var Configuration $conf
             */
            $conf = $em->getConfiguration();

            /**
             * @var AnnotationDriver $driver
             */
            $driver = $conf->getMetadataDriverImpl();

            $driver->addPaths(array(__DIR__."/../basic_table_package/src"));
            $pkg = parent::install();
            //add blocktypeset
            if (!BlockTypeSet::getByHandle('bacluc_invoice_set')) {
                BlockTypeSet::add('bacluc_invoice_set', 'Invoices', $pkg);
            }
            $db = Core::make('database');

            //remove unversioned product block


            BlockType::installBlockType("bacluc_versioned_product_block", $pkg);
            $productPackage = Package::getByHandle("bacluc_product_package");
            $db->query("DELETE FROM BlockTypes WHERE pkgID = ? AND btHandle = ?", array($productPackage->getPackageID(),"bacluc_product_block"));
            //convert all Products to versioned products
            //TODO convert to ORM query
            $db->query("UPDATE bacluc_product SET discr = ?", array("Concrete\\Package\\BaclucInvoicePackage\\Src\\VersionedProduct"));
            //insert for every product which is not yet a versioned product a row in verioned product

            BlockType::installBlockType("bacluc_invoice_block", $pkg);

            $em->getConnection()->commit();
        }catch(Exception $e){
            $em->getConnection()->rollBack();
            throw $e;
        }
    }
    public function uninstall()
    {
        $em = $this->getEntityManager();
        //begin transaction, so when block install fails, but parent::install was successfully, you don't have to uninstall the package
        $em->getConnection()->beginTransaction();
        try{

            $db = Core::make('database');

            //delete of blocktype not in orm way, because there is no entity BlockType
            $db->query("DELETE FROM BlockTypes WHERE pkgID = ?", array($this->getPackageID()));
            $productPackage = Package::getByHandle("bacluc_product_package");
            BlockType::installBlockType("bacluc_product_block", $productPackage);
            //convert all versioned products back to products
            //TODO convert to ORM query
            $db->query("UPDATE bacluc_product SET discr = ?", array("Concrete\\Package\\BaclucProductPackage\\Src\\Product"));




            parent::uninstall();
            $em->getConnection()->commit();
        }catch(Exception $e){
            $em->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * @return EntityManager
     * @overrides Package::getEntityManager
     * if the Package is installed, this function calls \Concrete\Package\BasicTablePackage\Controller::addDiscriminatorListenerToEm on the EntityManager
     * To add support for @DiscriminatorEntry Annotation
     * Only after Installation, because else the Classes to Support this are not found
     * This function needs to be present in every Package Controller which wants to use @DiscriminatorEntry
     */
    public function getEntityManager()
    {
        $em = parent::getEntityManager(); // TODO: Change the autogenerated stub

        if(parent::isPackageInstalled()) {
            \Concrete\Package\BasicTablePackage\Controller::addDiscriminatorListenerToEm($em);
        }
        return $em;
    }

}