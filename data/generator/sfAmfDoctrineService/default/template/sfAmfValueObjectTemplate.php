[?php

SabreAMF_ClassMapper::registerClass('<?php echo $this->getFullVoPackage() ?>','<?php echo $this->table->getClassnameToReturn() ?>ValueObject');

/**
 * <?php echo $this->table->getClassnameToReturn() ?> value object.
 *
 * @package    ##PACKAGE_NAME##
 * @author     ##AUTHOR_NAME##
 */
class <?php echo $this->table->getClassnameToReturn() ?>ValueObject
{
<?php
$columns = $this->table->getColumnNames();

foreach ($columns as $column)
{
echo '  public $' . $column. ";\n";
}
?>
}
