<?php
/**
 * Created by PhpStorm.
 * User: lucius
 * Date: 01.02.16
 * Time: 23:08
 */
namespace Concrete\Package\BaclucInvoicePackage\Src;
use Concrete\Core\Html\Object\Collection;
use Concrete\Package\BaclucProductPackage\Src\Product;
use Concrete\Package\BasicTablePackage\Src\BaseEntity;
use Concrete\Package\BasicTablePackage\Src\EntityGetterSetter;
use Concrete\Package\BasicTablePackage\Src\Exceptions\ConsistencyCheckException;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\BooleanField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DateField as DateField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DirectEditAssociatedEntityField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DropdownField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\FileField as FileField;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\WysiwygField as WysiwygField;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Query\Expr;
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DropdownLinkField;
use Concrete\Package\BaclucPersonPackage\Src\Address;
use Concrete\Package\BaclucPersonPackage\Src\PostalAddress;
use Concrete\Core\Package\Package;


/*because of the hack with @DiscriminatorEntry Annotation, all Doctrine Annotations need to be
properly imported*/
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Concrete\Package\BasicTablePackage\Src\DiscriminatorEntry\DiscriminatorEntry;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\QueryBuilder;

/**
 * Class VersionedProduct
 * Package  Concrete\Package\BaclucProductPackage\Src
 * @DiscriminatorEntry(value="Concrete\Package\BaclucInvoicePackage\Src\VersionedProduct")
 * @Entity
@Table(name="bacluc_versioned_product"
)
 *
 */
class VersionedProduct extends Product
{
    use EntityGetterSetter;

    /**
     * @var bool
     * @Column(type="boolean")
     */
    protected $depricated = false;
    /**
     * @var VersionedProduct
     * @ManyToOne(targetEntity="Concrete\Package\BaclucInvoicePackage\Src\VersionedProduct")
     */
    protected $NewVersion;









    public function __construct(){
        parent::__construct();



        if($this->OldVersions == null){
            $this->OldVersions = new ArrayCollection();
        }
        $this->setDefaultFieldTypes();
    }
    public function setDefaultFieldTypes(){
        parent::setDefaultFieldTypes();

        $this->fieldTypes['NewVersion'] = new DropdownLinkField("NewVersion", "Old Version of","postNewVersion");
        $this->fieldTypes['NewVersion']->setLinkInfo($this,"NewVersion",get_class($this),null,static::getDefaultGetDisplayStringFunction() );
        $this->fieldTypes['NewVersion']->setShowInForm(false);

        $this->fieldTypes['depricated'] = new BooleanField("depricated", "Depricated", "postdepricated");
        $this->fieldTypes['depricated']->setShowInForm(false);
        $this->fieldTypes['depricated']->setShowInTable(false);
    }


    /**
     * Returns the function, which generates the String for LInk Fields to identify the instance. Has to be unique to prevent errors
     * @return \Closure
     */
    public static function getDefaultGetDisplayStringFunction(){
        $function = function(VersionedProduct $item){
            $item = BaseEntity::getBaseEntityFromProxy($item);
            $returnString = '';
            if(strlen($item->code) >0){
                $returnString.= $item->code." ";
            }
            if(strlen($item->name) >0){
                $returnString.= $item->name." ";
            }
            if(strlen($item->price) !=0){
                $returnString.= $item->price." ";
            }
            return $returnString;
        };
        return $function;
    }






}

VersionedProduct::$staticEntityfilterfunction = function(QueryBuilder $query, array $queryConfig = array()){
    $firstEntityName = $queryConfig['fromEntityStart']['shortname'];
    $newversion = $queryConfig['NewVersion']['shortname'];
    $query->andWhere(
        $query->expr()->andX(
            $query->expr()->eq($newversion.".depricated", ":VersionendProductdepricated"),
            $query->expr()->isNull($newversion)
            )


    );
    $query->setParameter(":VersionendProductdepricated", false);
    return $query;
};