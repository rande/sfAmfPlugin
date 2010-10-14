[?php

/**
 * <?php echo $this->table->getClassnameToReturn() ?> service.
 *
 * @package    ##PACKAGE_NAME##
 * @author     ##AUTHOR_NAME##
 */
class <?php echo $this->table->getClassnameToReturn() ?>Service extends <?php echo (is_null($this->serviceParent) ? 'sfAmfService' : $this->serviceParent); ?> 
{

}
