<?php setlocale(LC_MONETARY, 'de_DE'); ?>

<div id="container">

<?php
if ($today_closed) {
  echo MessageBox::info($today_closed_text);
} if ($no_data) {
  echo MessageBox::warning(_('Kein Angebot - Leider liegen uns keine Angebote vor.'));
}
if(!$today_closed && !$no_data){
  if($select_overview){ ?>
<h1><?= _('Übersicht') ?> - <?= date('l, j.F', strtotime($date)) ?></h1>
<?php
foreach ($canteens as $canteen) {
    $today_closed=false;
    if (!empty($canteen['meals'][$date])) {
        foreach ($canteen['meals'][$date] as $meals) {
            if (strpos($meals->name, 'geschlossen') !== false) {
                $today_closed=true;
                break;
            }
        }
    }
    if (!empty($canteen['meals'][$date]) && !$today_closed) { ?>

<h2><?= $canteen['info']->name ?></h2>

<form class="default meals">

<?php foreach ($canteen['meals'][$date] as $meal) {
  $notes='';
  foreach ($meal->notes as $note) {
    $notes.=$note.', ';
  }
  $notes=rtrim($notes, ', ');
?>

    <div class="meal">
      <fieldset>
        <legend>
          <div class="meal-item-header">
            <div class="left"><?= $meal->name ?></div>
            <div class="right"><?= $meal->category ?></div>
          </div>
        </legend>
<?php foreach ($meal->prices as $key => $item) { if ($item){ ?>
          <div class="meal-price"><span><?= $key ?></span><span style="float:right"><?= str_replace('EUR', chr(0xE2) . chr(0x82) . chr(0xAC), money_format('%.2n', $item)) ?></span></div>
<?php }} ?>
          <div class="meal-additives"><?= $notes ?></div>
      </fieldset>
    </div>

<?php } ?>

</form>

<?php } ?>
<?php } ?>


<?php }else{ ?>

<h1><?= $canteens[$id]['info']->name ?> - <?= date('l, j.F', strtotime($date)) ?></h1>

<form class="default meals">

<?php foreach ($canteens[$id]['meals'][$date] as $meal) {
  $notes='';
  foreach ($meal->notes as $note) {
    $notes.=$note.', ';
  }
  $notes=rtrim($notes, ', ');
?>

    <div class="meal">
      <fieldset>
        <legend>
          <div class="meal-item-header">
            <div class="left"><?= $meal->name ?></div>
            <div class="right"><?= $meal->category ?></div>
          </div>
        </legend>
<?php foreach ($meal->prices as $key => $item) { if ($item){ ?>
          <div class="meal-price"><span><?= $key ?></span><span style="float:right"><?= str_replace('EUR', chr(0xE2) . chr(0x82) . chr(0xAC), money_format('%.2n', $item)) ?></span></div>
<?php }} ?>
          <div class="meal-additives"><?= $notes ?></div>
      </fieldset>
    </div>

<?php } ?>
</form>
<?php }} ?>

</div>
