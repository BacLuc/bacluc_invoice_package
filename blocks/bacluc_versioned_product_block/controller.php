<?php
namespace Concrete\Package\BaclucInvoicePackage\Block\BaclucVersionedProductBlock;

use Concrete\Core\Package\Package;
use Concrete\Package\BaclucAccountingPackage\Src\Account;
use Concrete\Package\BaclucEventPackage\Src\Event;
use Concrete\Package\BaclucInvoicePackage\Src\VersionedProduct;
use Concrete\Package\BaclucProductPackage\Src\Product;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\DropdownBlockOption;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\TableBlockOption;
use Concrete\Core\Block\BlockController;
use Concrete\Package\BasicTablePackage\Src\BasicTableInstance;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\TextBlockOption;
use Concrete\Package\BasicTablePackage\Src\BaseEntity;
use Concrete\Package\BasicTablePackage\Src\ExampleBaseEntity;
use Core;
use Concrete\Package\BasicTablePackage\Src\BlockOptions\CanEditOption;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\QueryBuilder;
use OAuth\Common\Exception\Exception;
use Page;
use User;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\Field as Field;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\SelfSaveInterface as SelfSaveInterface;
use Loader;

use Concrete\Package\BasicTablePackage\Block\BasicTableBlockPackaged\Test as Test;

class Controller extends \Concrete\Package\BasicTablePackage\Block\BasicTableBlockPackaged\Controller
{
    protected $btHandle = 'bacluc_versioned_product_block';
    /**
     * table title
     * @var string
     */
    protected $header = "BaclucProductBlock";

    /**
     * Array of \Concrete\Package\BasicTablePackage\Src\BlockOptions\TableBlockOption
     * @var array
     */
    protected $requiredOptions = array();

    /**
     * @var \Concrete\Package\BasicTablePackage\Src\BaseEntity
     */
    protected $model;


    /**
     * set blocktypeset
     * @var string
     */
    protected $btDefaultSet = 'bacluc_product_set';

    /**
     *
     * Controller constructor.
     * @param null $obj
     */
    function __construct($obj = null)
    {
        //$this->model has to be instantiated before, that session handling works right

        $this->model = new VersionedProduct();
        parent::__construct($obj);



        if ($obj instanceof Block) {
         $bt = $this->getEntityManager()->getRepository('\Concrete\Package\BasicTablePackage\Src\BasicTableInstance')->findOneBy(array('bID' => $obj->getBlockID()));

            $this->basicTableInstance = $bt;
        }


/*
 * add blockoptions here if you wish
        $this->requiredOptions = array(
            new TextBlockOption(),
            new DropdownBlockOption(),
            new CanEditOption()
        );

        $this->requiredOptions[0]->set('optionName', "Test");
        $this->requiredOptions[1]->set('optionName', "TestDropDown");
        $this->requiredOptions[1]->setPossibleValues(array(
            "test",
            "test2"
        ));

        $this->requiredOptions[2]->set('optionName', "testlink");
*/


    }



    /**
     * @return string
     */
    public function getBlockTypeDescription()
    {
        return t("Create, Edit or Delete Products");
    }

    /**
     * @return string
     */
    public function getBlockTypeName()
    {
        return t("Bacluc Product");
    }


    /**
     * if save is pressed, the data is saved to the sql table
     * @throws \Exception
     */
    function action_save_row($redirectOnSuccess = true)
    {



        if ($this->post('rcID')) {
            // we pass the rcID through the form so we can deal with stacks
            $c = Page::getByID($this->post('rcID'));
        } else {
            $c = $this->getCollectionObject();
        }
        //form view is over
        $v =  $this->checkPostValues();
        if($v === false){
            return false;
        }

        if ($this->editKey == null) {
            $model = $this->model;
        } else {
            $oldmodel = $this->getEntityManager()->getRepository(get_class($this->model))->findOneBy(array($this->model->getIdFieldName() => $this->editKey));
            $model = new VersionedProduct();
            $oldmodel->set("NewVersion", $model);
            $this->getEntityManager()->persist($oldmodel);
        }

        if($this->persistValues($model, $v) === false){
            return false;
        }

        $this->getEntityManager()->flush();


        $this->finishFormView();
        if($redirectOnSuccess) {
            $this->redirect($c->getCollectionPath());
        }


    }

    public function deleteRow()
    {
        $model = $this->getEntityManager()->getRepository(get_class($this->model))->findOneBy(array($this->model->getIdFieldName() => $this->editKey));
        $model->set("depricated", true);
        $this->getEntityManager()->persist($model);
        $this->getEntityManager()->flush();
        $r = true;
        $_SESSION[$this->getHTMLId()]['prepareFormEdit'] = false;
        if (isset($_SESSION[$this->getHTMLId() . "rowid"])) {
            unset($_SESSION[$this->getHTMLId() . "rowid"]);

        }
        $this->editKey = null;

        if ($r) {
            return true;
        } else {
            return false;
        }
    }

    public function addFilterToQuery(QueryBuilder $query, array $queryConfig = array())
    {
        //TODO add if show old version and depricated
        return $query;
    }


}
