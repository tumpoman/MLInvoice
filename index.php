<?php
/*******************************************************************************
VLLasku: web-based invoicing application.
Copyright (C) 2010-2011 Ere Maijala

This program is free software. See attached LICENSE.

*******************************************************************************/

/*******************************************************************************
VLLasku: web-pohjainen laskutusohjelma.
Copyright (C) 2010-2011 Ere Maijala

Tämä ohjelma on vapaa. Lue oheinen LICENSE.

*******************************************************************************/

require_once 'sessionfuncs.php';
require_once 'navi.php';
require_once 'list.php';
require_once 'form.php';
require_once 'open_invoices.php';
require_once 'settings.php';
require_once 'localize.php';
require_once 'settings_list.php';

sesVerifySession();

ob_start(); // buffered so we can redirect later if necessary

$strFunc = getRequest('func', 'open_invoices');
$strList = getRequest('list', '');
$strForm = getRequest('form', '');

if (!$strFunc)
  $strFunc = 'open_invoices';

if ($strFunc == 'logout')
{
  header("Location: ". _PROTOCOL_ . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/logout.php");
  exit;
}

if (!$strFunc && $strForm)
  $strFunc = 'invoices';

$title = getPageTitle($strFunc, $strList, $strForm);

if ($strFunc == 'system' && getRequest('operation', '') == 'dbdump' && in_array($_SESSION['sesACCESSLEVEL'], array(90, 99)))
{
  create_db_dump();
  exit;
}

echo htmlPageStart(_PAGE_TITLE_ . " - $title");

$astrMainButtons = array (
    array("name" => "invoice", "title" => "locSHOWINVOICENAVI", 'action' => 'open_invoices', "levels_allowed" => array(1, 90) ),
    array("name" => "archive", "title" => "locSHOWARCHIVENAVI", 'action' => 'archived_invoices', "levels_allowed" => array(1, 90) ),
    array("name" => "company", "title" => "locSHOWCOMPANYNAVI", 'action' => 'companies', "levels_allowed" => array(1, 90) ),
    array("name" => "reports", "title" => "locSHOWREPORTNAVI", 'action' => 'reports', "levels_allowed" => array(1, 90) ),
    array("name" => "settings", "title" => "locSHOWSETTINGSNAVI", 'action' => 'settings', "action" => "settings", "levels_allowed" => array(1, 90) ),
    array("name" => "system", "title" => "locSHOWSYSTEMNAVI", 'action' => 'system', "levels_allowed" => array(90, 99) ),
    array("name" => "logout", "title" => "locLOGOUT", 'action' => 'logout', "levels_allowed" => array(1, 90) )
);

?>

<body>
  <div class="navi ui-widget-header">
<?php
foreach ($astrMainButtons as $button) 
{
  $strButton = '<a class="functionlink'; 
  if ($button['action'] == $strFunc || ($button['action'] == 'open_invoices' && $strFunc == 'invoices'))
    $strButton .= ' selected';
  $strButton .= '" href="?func=' . $button['action'] . '">';
  $strButton .= $GLOBALS[$button['title']] . '</a>';
      
  if (in_array($_SESSION['sesACCESSLEVEL'], $button['levels_allowed']) || $_SESSION['sesACCESSLEVEL'] == 99) 
  {
    echo "    $strButton\n";
  }
}

$level = 1;
if ($strList && ($strFunc == 'settings' || $strFunc == 'system'))
  ++$level;
if ($strForm) 
  ++$level;
$arrHistory = sesUpdateHistory($title, $_SERVER['QUERY_STRING'], $level);

$strBreadcrumbs = '';
foreach ($arrHistory as $arrHE)
{
  if ($strBreadcrumbs)
    $strBreadcrumbs .= '&gt; ';
  $strBreadcrumbs .= '<a href="index.php?' . str_replace('&', '&amp;', $arrHE['url']) . '">' . $arrHE['title'] . '</a>&nbsp;';
}

?>
  </div>
  <div class="breadcrumbs">
    <?php echo $strBreadcrumbs . "\n"?>
  </div>

<?php
if ($strFunc == 'system' && getRequest('operation', '') == 'export' && $_SESSION['sesACCESSLEVEL'] == 99)
{
  createFuncMenu($strFunc);
  require_once 'export.php';
  do_export();
}
elseif ($strFunc == 'system' && getRequest('operation', '') == 'import' && $_SESSION['sesACCESSLEVEL'] == 99)
{
  createFuncMenu($strFunc);
  require_once 'import.php';
  do_import();
}
else
{
  switch ($strFunc)
  {
  case 'reports':
    createFuncMenu($strFunc);
    switch ($strForm)
    {
    case 'invoice': 
      require_once 'invoice_report.php'; 
      $invoiceReport = new InvoiceReport;
      $invoiceReport->createReport(); 
      break;
    case 'product': 
      require_once 'product_report.php'; 
      $productReport = new ProductReport;
      $productReport->createReport(); 
      break;
    }
    break;
  default:
    if ($strForm)
    {
      if ($strFunc == 'settings')
        createFuncMenu($strFunc);
      createForm($strFunc, $strList, $strForm);
    }
    else
    {
      createFuncMenu($strFunc);
      if ($strFunc == 'open_invoices')
        createOpenInvoiceList();
      else
      {
        if ($strList == 'settings')
          createSettingsList();
        else
          createList($strFunc, $strList);
      }
    }
  }
}
?>
</body>
</html>
