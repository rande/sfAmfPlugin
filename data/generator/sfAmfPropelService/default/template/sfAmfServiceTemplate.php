[?php

/**
 * <?php echo $this->table->getClassname() ?> service.
 *
 * @package    ##PACKAGE_NAME##
 * @author     ##AUTHOR_NAME##
 */
class <?php echo $this->table->getClassname() ?>Service extends <?php echo (is_null($this->serviceParent) ? 'sfAmfService' : $this->serviceParent); ?> 
{

}
