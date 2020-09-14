<?php
    use Studip\Button;
use Studip\LinkButton;

$sidebar = Sidebar::get();
    $actions = new ActionsWidget();
    $actions->addLink(_("Cache leeren"), PluginEngine::getLink('openmensa/admin/clear'), Icon::create('refresh', Icon::ROLE_CLICKABLE));
    $actions->addLink(_("Einstellungen löschen"), PluginEngine::getLink('openmensa/admin/reset'), Icon::create('trash+remove', Icon::ROLE_CLICKABLE));
    $sidebar->addWidget($actions);
?>
<table>
  <tr>
    <td style="vertical-align: top;">
      <form class="conf-form" action="<?= PluginEngine::getLink('openmensa/admin/update') ?>" method=post>
          <?= CSRFProtection::tokenTag() ?>
          <fieldset class="conf-form-field">
              <legend><?=_("OpenMensa Einstellungen")?></legend>
<?php
if (!empty($canteens_tmp)) {
    echo '<p>'._('Folgende IDs können nicht mehr gefunden werden:').json_encode($canteens).'</p>';
}
?>
              <label for="canteens">Wählen Sie Ihre Mensen aus:</label>
              <br>
              <select id="canteens" name="canteens[]" multiple>
<?php
$canteens_tmp=$canteens;
$canteen_names=[];
foreach ($public_canteens as $canteen) {
    echo '<option value="'.$canteen->id.'"'.(in_array($canteen->id, $canteens_tmp) ? ' selected':'').'>'.$canteen->name.'</option>';
    $canteen_names[$canteen->id]=$canteen->name;
    if (in_array($canteen->id, $canteens_tmp)) {
        if (($key = array_search($canteen->id, $canteens_tmp)) !== false) {
            unset($canteens_tmp[$key]);
        }
    }
}
?>
              </select>
              <br>
<?php if ($canteens) { ?>
              <label for="default_canteen">Wählen Sie Ihre Default Mensa aus:</label>
              <br>
              <select id="default_canteen" name="default_canteen">
              <option value="0">Übersicht</option>
<?php
foreach ($canteens as $canteen) {
    echo '<option value="'.$canteen.'"'.($default_canteen==$canteen ? ' selected':'').'>'.$canteen_names[$canteen].'</option>';
}
?>
              </select>
              <br>
              <label for="default_canteen">Übersicht aktivieren:</label>
              <input type="checkbox" id="overview" name="overview"<?php echo($overview ? ' checked' : '') ?>>
<?php } ?>
              <div>
               <?= Button::createAccept(utf8_encode(_('Übernehmen'))) ?>
               <?= LinkButton::createCancel(_('Abbrechen'), PluginEngine::getLink('openmensa/admin/index')) ?>
              </div>

          </fieldset>
      </form>
    </td>
    <td style="width:500px">
      <p>Wählen sie die Marker mit der rechten Maustaste aus,<br> um die Mensa zu Ihren Mensen hinzuzufügen.</p>
      <div id="map"></div>
    </td>
  </tr>
</table>
<script>
$(function() {
  $("#canteens").chosen();
  $("#default_canteen").chosen();

  var map = L.map("map").setView([51.0, 10.4], 5);

  L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);

  var markers = L.markerClusterGroup().on("contextmenu", groupClick);
  var marker;

  <?php
  foreach ($public_canteens as $canteen) {
      $name=$canteen->name;
      $name=str_replace("'", "\'", $name);
      echo 'marker = L.marker(['.(isset($canteen->coordinates[0]) && $canteen->coordinates[0] && is_numeric($canteen->coordinates[0]) ? $canteen->coordinates[0]:'51.0').','.(isset($canteen->coordinates[1]) && $canteen->coordinates[1] && is_numeric($canteen->coordinates[1]) ? $canteen->coordinates[1]:'10.4').']).bindPopup(\''.$name.'\');';
      echo 'marker.marker_id = '.$canteen->id.';';
      echo 'markers.addLayer(marker);';
  }
  ?>

  map.addLayer(markers);

  function groupClick(event) {
    if($('#canteens option[value=' + event.layer.marker_id + ']').attr('selected')){
      $('#canteens option[value=' + event.layer.marker_id + ']').attr('selected', false);
    }else{
      $('#canteens option[value=' + event.layer.marker_id + ']').attr('selected', true);
    }
    $("#canteens").trigger("chosen:updated");
  }

});
</script>

<style>
#canteens{
  width: 500px;
}
#default_canteen{
  width: 500px;
}
#map {
    height: 500px;
    width: 500px;
}
</style>
