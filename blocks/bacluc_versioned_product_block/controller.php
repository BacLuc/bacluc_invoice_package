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


    protected $showOldAndDepricated = false;

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

        if(isset($_SESSION[$this->getHTMLId() . "showDepricated"])){
            $this->setShowOldAndDepricated($_SESSION[$this->getHTMLId() . "showDepricated"]);
        }





    }

    /**
     * @return boolean
     */
    public function isShowOldAndDepricated()
    {
        return $this->showOldAndDepricated;
    }

    /**
     * @param boolean $showOldAndDepricated
     * @return $this
     */
    public function setShowOldAndDepricated($showOldAndDepricated)
    {
        $_SESSION[$this->getHTMLId() . "showDepricated"] = $showOldAndDepricated;
        $this->showOldAndDepricated = $showOldAndDepricated;
        return $this;
    }

    public function action_show_depricated($args){
        $this->setShowOldAndDepricated(true);
    }

    public function action_hide_depricated($args){
        $this->setShowOldAndDepricated(false);
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
        $v['depricated']=false;

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

    public function restoreRow()
    {
        $model = $this->getEntityManager()->getRepository(get_class($this->model))->findOneBy(array($this->model->getIdFieldName() => $this->editKey));
        $model->set("depricated", false);
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

    function getActions($object, $row = array())
    {
        //".$object->action('edit_row_form')."
        $string = "
    	<td class='actioncell'>
    	<form method='post' action='" . $object->action('edit_row_form') . "'>
    		<input type='hidden' name='rowid' value='" . $row['id'] . "'/>
    		<input type='hidden' name='action' value='edit' id='action_" . $row['id'] . "'>";

            $string .= $this->getEditActionIcon($row);
            $string .= $this->getDeleteActionIcon($row);
            $string.= $this->getRestoreActionIcon($row);


        $string .= "</form>
    	</td>";
        return $string;
    }


    /**
     * Returns the HTML for the edit button
     * @param $row
     * @return string
     */
    function getEditActionIcon($row)
    {
        if($row['depricated']==false && is_null($row['NewVersion']) ) {
            return parent::getEditActionIcon($row);
        }
        return '';
    }

    /**
     * Returns the HTML for the delete button
     * @param $row
     * @return string
     */
    function getDeleteActionIcon($row)
    {
        if($row['depricated']==false && is_null($row['NewVersion']) ) {
            return parent::getDeleteActionIcon($row);
        }
        return '';
    }

    /**
     * Returns the HTML for the edit button
     * @param $row
     * @return string
     */
    function getRestoreActionIcon($row)
    {
        if($row['depricated']==true && is_null($row['NewVersion']) ) {
            return static::getActionButton($row,"restore", "btn inlinebtn actionbutton add", "restore","fa fa-history");
        }else{
            return '';
        }
    }

    function action_edit_row_form()
    {
        $u = new User();
        if ($this->requiresRegistration()) {
            if (!$u->isRegistered()) {
                $this->redirect('/login');
            }
        }

        //get the editkey
        $this->editKey = $_POST['rowid'];
        //save it in the session
        $_SESSION[$this->getHTMLId() . "rowid"] = $this->editKey;
        $row = $this->getRowValues();

        if ($_POST['action'] == 'edit' && strlen($this->getEditActionIcon($row))>0) {
            $this->prepareFormEdit();
        } elseif ($_POST['action'] == 'delete' && strlen($this->getDeleteActionIcon($row))>0 ) {
            $this->deleteRow();
        }elseif ($_POST['action'] == 'restore' && strlen($this->getRestoreActionIcon($row))>0) {
            $this->restoreRow();
        }
    }

    public function addFilterToQuery(QueryBuilder $query, array $queryConfig = array())
    {
        if($this->isShowOldAndDepricated()){
            $firstEntityName = $queryConfig['fromEntityStart']['shortname'];
            $newversion = $queryConfig['NewVersion']['shortname'];
            $query->orWhere(
                $query->expr()->orX(
                    $query->expr()->eq($firstEntityName.".depricated", ":BlockVersionendProductdepricated")
                    ,
                    $query->expr()->isNotNull($newversion)
                )


            );
            $query->setParameter(":BlockVersionendProductdepricated", true);
        }
        return $query;
    }

    public function getFields()
    {
        $fields =  parent::getFields();
        if($this->isShowOldAndDepricated()){
            $fields['depricated']->setShowInTable(true);
            $fields['NewVersion']->setShowInTable(true);
        }else{
            $fields['NewVersion']->setShowInTable(false);
        }
        return $fields;
    }


}
