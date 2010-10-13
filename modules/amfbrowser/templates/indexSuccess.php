<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <link rel="stylesheet" type="text/css" href="<?php use_stylesheet('/sfAmfPlugin/css/sfAmfPluginBrowser.css') ?>" />
  <script type="text/javascript" src="<?php use_javascript('/sfAmfPlugin/js/mootools-core.js') ?>"></script>
  <script type="text/javascript" src="<?php use_javascript('/sfAmfPlugin/js/mootools-more.js') ?>"></script>
  <script type="text/javascript" src="<?php use_javascript('/sfAmfPlugin/js/sfAmfPluginBrowser.js') ?>"></script>
  <link rel="shortcut icon" href="/favicon.ico" />
</head>
<body>
<div id="global">
  <div class="methods">
      <h2>Service-Classes:</h2>
      <div id="outer">
<?php foreach ($services_reflections as $package_name=>$class): ?>
    <div class="inner service<?php echo $class->getName() == $service_name ? ' active' : ' is_fold' ?>">
      <h3>
        <a href="#" onclick="$(this).getParent().getParent().toggleClass('is_fold')">+</a>
        <?php echo $class->getName() ?>
      </h3>
      <ul>
    <?php $is_empty = true; ?>
    <?php foreach ($class->getMethods() as $method): ?>
    
      <?php if (!$method->isPublic() or $method->getDeclaringClass() != $class) continue;?>

      <?php $is_empty = false; ?>
      <li class="method"><?php echo link_to($method->getName(), 'amfBrowser/index?method='.urlencode($package_name.'::'.$method->getName()));?></li>
    <?php endforeach;?>

    <?php if ($is_empty):?>
      <li class="no_methods">No callable methods</li>
    <?php endif; ?>
      </ul>
    </div>
<?php endforeach;?>
    </div>
  </div>

  <div id="right">
<?php if ($service_method_name): ?>
    <div>
      <h3>Request: </h3>
      <h4><?php echo $service_method_name ?></h4>
      <dfn><?php echo nl2br($method_reflection->getDocComment()) ?></dfn>
      <form action="" method="post">
        <input type="hidden" name="method" value="<?php echo $service_method_name ?>" />
        <ul>
        <?php foreach ($method_reflection->getParameters() as $param): ?>
        <?php  $input_name = 'param['.$param->getName().']'; ?>
          <li>
            <label>
              <?php echo $param->getName() ?> :
              <input class="text" name="<?php echo $input_name ?>"
                     value="<?php echo htmlspecialchars($sf_request->getParameter($input_name)) ?>" />
            </label>
          </li>
        <?php endforeach; ?>
        </ul>
        <input class="submit" type="submit" value="Call" />
      </form>
    </div>
    <hr />
<?php endif;?>
  

<?php foreach ($errors as $err): ?>
    <p style="color:red; font-weight:bold; font-size: 120%;">! <?php echo $err;?></p>
<?php endforeach; ?>

    <div class="response">
      <h3>Response:</h3>
      <ul class="result_views_menu">
        <li style="display:none;"></li>
      </ul>
<?php
  if ($service_method_name and $sf_request->isMethod('post'))
  {
    $resp = $sf_data->getRaw('method_return_resp');
    $str = method_exists($resp, 'getRawValue') ? $resp->getRawValue() : $resp;
?>
      <div class="result_view" id="method_view">
        <h5>Raw Method Return</h5>
        <pre>
<?php
    if (is_scalar($str) or is_null($str))
      var_dump($str);
    else
      print_r($str);
?>
        </pre>
      </div>

      <div class="result_view selected" id="tree_view">
        <h5>AMF Treeview</h5>
        <table class="tree_view">
          <?php echo sfAmfTreeViewer::display('Result', $sf_data->getRaw('amf_return_resp')) ?>
        </table>
      </div>
<?php
  }
  else
    echo '<em>No method called.</em>';
?>
    </div>
  </div>
</div>
</body>
</html>