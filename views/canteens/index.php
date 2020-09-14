<?php setlocale(LC_MONETARY, 'de_DE'); ?>
<style>
#container{
  font-size: 16px;
}
#notes{
  font-size:13px;
  font-style:italic;
}
</style>
<div id="container">
<?php if (!$today_closed && !$no_data && !$select_overview) { ?>
  <h1><?= $canteens[$id]['info']->name ?> - <?= date('l, j.F', strtotime($date)) ?></h1>
  <table style="width:100%">
    <tbody>
      <tr>
        <td colspan="2"></td>
<?php
$canteen_price_keys=[];
foreach ($canteens[$id]['meals'][$date] as $meal) {
    foreach ($meal->prices as $key => $item) {
        if (!in_array($key, $canteen_price_keys) && is_numeric($item)) {
            $canteen_price_keys[]=$key;
        }
    }
}
foreach ($canteen_price_keys as $price_keys) {
    echo '<th style="text-align: left;">'._($price_keys).'</th>';
}
?>
      </tr>
<?php
foreach ($canteens[$id]['meals'][$date] as $meal) {
    $prices=[];
    foreach ($meal->prices as $key => $item) {
        $price=money_format('%.2n', $item);
        $price=str_replace('EUR', chr(0xE2) . chr(0x82) . chr(0xAC), $price);
        $prices[$key]=$price;
    } ?>
      <tr>
        <td rowspan="2"><?= $meal->category ?></td>
        <td><?= $meal->name ?></td>
<?php
foreach ($canteen_price_keys as $key) {
        echo '<td rowspan="2">'._($prices[$key]).'</td>';
    } ?>
      </tr>
      <tr id="notes">
        <td><?php $notes='';
    foreach ($meal->notes as $note) {
        $notes.=$note.', ';
    }
    echo rtrim($notes, ', '); ?>&nbsp;</td>
      </tr>
<?php
}
?>

    </tbody>
  </table>
<?php } elseif ($today_closed) { ?>
<p><?= $today_closed_text?></p>
<?php } elseif ($no_data) { ?>
<p><?= _('Kein Angebot - Leider liegen uns keine Angebote vor.') ?></p>
<?php
} elseif ($select_overview) {
    echo '<h1>'._('Übersicht').' - '.date('l, j.F', strtotime($date)).'</h1>';
    foreach ($canteens as $canteen) {
        $today_closed=false;
        foreach ($canteen['meals'][$date] as $meals) {
            if (strpos($meals->name, 'geschlossen') !== false) {
                $today_closed=true;
                break;
            }
        }
        if (!empty($canteen['meals'][$date]) && !$today_closed) {
            ?>
  <h2><?= $canteen['info']->name ?></h2>
  <table style="width:100%">
    <tbody>
      <tr>
        <td colspan="2"></td>
<?php
$canteen_price_keys=[];
            foreach ($canteen['meals'][$date] as $meal) {
                foreach ($meal->prices as $key => $item) {
                    if (!in_array($key, $canteen_price_keys) && is_numeric($item)) {
                        $canteen_price_keys[]=$key;
                    }
                }
            }
            foreach ($canteen_price_keys as $price_keys) {
                echo '<th style="text-align: left;">'.$price_keys.'</th>';
            } ?>
      </tr>
<?php
foreach ($canteen['meals'][$date] as $meal) {
                $prices=[];
                foreach ($meal->prices as $key => $item) {
                    $price=money_format('%.2n', $item);
                    $price=str_replace('EUR', chr(0xE2) . chr(0x82) . chr(0xAC), $price);
                    $prices[$key]=$price;
                } ?>
      <tr>
        <td rowspan="2"><?= $meal->category ?></td>
        <td><?= $meal->name ?></td>
<?php
foreach ($canteen_price_keys as $key) {
                    echo '<td rowspan="2">'._($prices[$key]).'</td>';
                } ?>
      </tr>
      <tr id="notes">
        <td><?php $notes='';
                foreach ($meal->notes as $note) {
                    $notes.=$note.', ';
                }
                echo rtrim($notes, ', '); ?>&nbsp;</td>
      </tr>
<?php
            } ?>

    </tbody>
  </table>
<?php
        }
    }
}
?>
</div>
