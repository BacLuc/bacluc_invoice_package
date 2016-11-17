<?php
/**
 * Created by PhpStorm.
 * User: lucius
 * Date: 21.12.15
 * Time: 14:53
 */

namespace Concrete\Package\BaclucInvoicePackage\Src; //TODO change namespace
//TODO CHANGE use statemetns
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
 * Class Example
 * package Concrete\Package\BaclucInvoicePackage\Src
 * @Entity
 *  @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorEntry( value = "Concrete\Package\BaclucInvoicePackage\Src\InvoiceLine") //TODO change discriminator value
 * @Table(name="bacluc_invoice_line")//TODO change table name
 */
class InvoiceLine extends BaseEntity//TODO change class name
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
     * @Column(type="float", nullable=false)
     */
    protected $amount;


    /**
     * @var VersionedProduct
     * @ManyToOne(targetEntity="Concrete\Package\BaclucInvoicePackage\Src\VersionedProduct")
     */
    protected $Product;



    /**
     * @var Invoice
     * @ManyToOne(targetEntity="Concrete\Package\BaclucInvoicePackage\Src\Invoice", inversedBy="InvoiceLines")
     */
    protected $Invoice;








    public function __construct(){
        parent::__construct();


    }

    public function setDefaultFieldTypes()
    {
        parent::setDefaultFieldTypes();

    }


    public static function getDefaultGetDisplayStringFunction(){
        $function = function(InvoiceLine $item){//TODO change this function that it returns a unique string
            $returnString = '';
            if(strlen($item->id)>0){
                $returnString.=$item->id." ";
            }
            $product = $item->Product;
            $product = BaseEntity::getBaseEntityFromProxy($product);
            $displayStringFunction = Person::getDefaultGetDisplayStringFunction();
            $productString = $displayStringFunction($product);
            if(strlen($productString)>0){
                $returnString.=$productString." ";
            }
            if(strlen($item->amount)>0){
                $returnString.=$item->amount." ";
            }
            return $returnString;
        };
        return $function;
    }




}
