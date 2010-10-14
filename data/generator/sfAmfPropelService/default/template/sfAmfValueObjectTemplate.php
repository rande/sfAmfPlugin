[?php

/**
 * <?php echo $this->table->getClassname() ?> value object.
 *
 * @package    ##PACKAGE_NAME##
 * @author     ##AUTHOR_NAME##
 */
class <?php echo $this->table->getClassname() ?>ValueObject
{
<?php
$columns = $this->table->getColumns();

foreach ($columns as $column)
{
echo '  public $' . lcfirst($column->getPhpName()). ";\n";
}
?>
}
