<?php
/**
 * Created by PhpStorm.
 * User: lucius
 * Date: 21.12.15
 * Time: 14:53
 */

namespace Concrete\Package\BaclucInvoicePackage\Src; //TODO change namespace
//TODO CHANGE use statemetns
use Concrete\Package\BaclucPersonPackage\Src\Person;
use Concrete\Package\BasicTablePackage\Src\EntityGetterSetter;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DirectEditAssociatedEntityField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DirectEditAssociatedEntityMultipleField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DropdownLinkField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DropdownMultilinkField;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DateField;
use Concrete\Package\BasicTablePackage\Src\BaseEntity;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DropdownField;

/*because of the hack with @DiscriminatorEntry Annotation, all Doctrine Annotations need to be
properly imported*/
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Concrete\Package\BasicTablePackage\Src\DiscriminatorEntry\DiscriminatorEntry;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\QueryBuilder;

/**
 * Class Invoice
 * package Concrete\Package\BaclucInvoicePackage\Src
 * @Entity
 *  @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorEntry( value = "Concrete\Package\BaclucInvoicePackage\Src\Invoice") //TODO change discriminator value
 * @Table(name="bacluc_invoice")//TODO change table name
 */
class Invoice extends BaseEntity//TODO change class name
{
    use EntityGetterSetter;

    //dontchange
    public static $staticEntityfilterfunction; //that you have a filter that is only for this entity
    /**
     * @var int
     * @Id @Column(type="integer")
     * @GEneratedValue(strategy="AUTO")
     */
    protected $id;





    /**
     * @Column(type="string", nullable=false)
     */
    protected $number;

    /**
     * @Column(type="float", nullable=false)
     */
    protected $amount;


    /**
     * @Column(type="date", nullable=false)
     */
    protected $invoicedate;

    /**
     * @Column(type="boolean", nullable=false)
     */
    protected $canceled;



    /**
     * @var Person
     * @ManyToOne(targetEntity="Concrete\Package\BaclucPersonPackage\Src\Person")
     */
    protected $Person;

    /**
     * @var InvoiceLine[]
     * @OneToMany(targetEntity="Concrete\Package\BaclucInvoicePackage\Src\InvoiceLine", mappedBy="Invoice")
     */
    protected $InvoiceLines;









    public function __construct(){
        parent::__construct();

        //TODO foreach Collection valued property, you have to set the ArrayCollection if it is null


        if($this->InvoiceLines == null){
            $this->InvoiceLines = new ArrayCollection();
        }

    }

    public function setDefaultFieldTypes()
    {
        parent::setDefaultFieldTypes();
        $this->fieldTypes['number']->setDefault();

        $this->fieldTypes['amount']->setShowInForm(false);


    }


    public static function getDefaultGetDisplayStringFunction(){
        $function = function(Invoice $item){//TODO change this function that it returns a unique string
            $item = BaseEntity::getBaseEntityFromProxy($item);
            $dateField = new DateField("test", "test", "test");
            $returnString ="";
            if(strlen($item->number)>0){
                $returnString.=$item->number." ";
            }


            if(strlen($item->amount)>0){
                $returnString.=$item->amount." ";
            }
            $person = $item->Person;
            $person = BaseEntity::getBaseEntityFromProxy($person);
            $displayStringFunction = Person::getDefaultGetDisplayStringFunction();
            $personString = $displayStringFunction($person);
            if(strlen($personString)>0){
                $returnString.=$personString." ";
            }
            if($item->invoicedate!=null){
                $dateField->setSQLValue($item->invoicedate);
                $returnString.= " ".$dateField->getTableView();
            }
            return $returnString;
        };
        return $function;
    }




}