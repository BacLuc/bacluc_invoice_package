<div class="bacluc_versioned_product_block">
    <?php
    /**
     * @var \Concrete\Package\BaclucInvoicePackage\Block\BaclucVersionedProductBlock\Controller $controller
     */
    if(!$controller->displayForm()) {
        if ($controller->isShowOldAndDepricated()) {
            ?>
            <form method='post' action='<?php echo $view->action('hide_depricated') ?>'>
                <button type='submit'
                        value='1'
                        class='btn inlinebtn actionbutton'
                >
                    <?php echo t("Hide old and depricated Products") ?>
                </button>
            </form>
            <?php
        } else {
            ?>
            <form method='post' action='<?php echo $view->action('show_depricated') ?>'>
                <button type='submit'
                        value='1'
                        class='btn inlinebtn actionbutton'
                >
                    <?php echo t("Show old and depricated Products") ?>
                </button>
            </form>
            <?php
        }
    }
    include($controller->getBasicTablePath().'/view.php');
?>

  </div>
