<?php

/**
 * @var IndexController $this
 * @var Bezirksausschuss $ba
 * @var Antrag[] $antraege
 * @var array $geodata
 * @var Termin[] $termine_zukunft
 * @var Termin[] $termine_vergangenheit
 * @var Termin[] $termin_dokumente
 * @var int $tage_zukunft
 * @var int $tage_vergangenheit
 */

/**
 * @var Termin[] $termine
 * @return array[]
 */
function ba_gruppiere_termine($termine)
{
	$data = array();
	foreach ($termine as $termin) {
		$key = $termin->termin . $termin->sitzungsort;
		if (!isset($data[$key])) {
			$ts         = RISTools::date_iso2timestamp($termin->termin);
			$data[$key] = array(
				"datum"   => date("j.M, H:i", $ts),
				"gremien" => array(),
				"ort"     => $termin->sitzungsort,
				"tos"     => array(),
			);
		}
		$url = "http://www.ris-muenchen.de/RII2/BA-RII/ba_sitzungen_details.jsp?Id=" . $termin->id;
		if (!isset($data[$key]["gremien"][$termin->gremium->name])) $data[$key]["gremien"][$termin->gremium->name] = array();
		$data[$key]["gremien"][$termin->gremium->name][] = $url;
	}
	foreach ($data as $key => $val) ksort($data[$key]["gremien"]);
	return $data;
}

$this->pageTitle = Yii::app()->name . ": Bezirksausschuss " . $ba->ba_nr . " (" . $ba->name . ")";

$assets_base = $this->getAssetsBase();

?>


<h1>BA <?=$ba->ba_nr?> (<?=CHtml::encode($ba->name)?>)</h1>


<div id="mapholder">
	<div id="map"></div>
</div>
<div id="benachrichtigung_hinweis">
	<div id="ben_map_infos">
		<div class="nichts" style="font-style: italic;">
			<strong>Hinweis:</strong><br>
			Du kannst dich bei <strong>neuen Dokumenten mit Bezug zu einem bestimmten Ort</strong> per E-Mail benachrichtigen lassen.<br>Klicke dazu auf den Ort, bestimme dann den relevanten Radius.<br>
			<br>
		</div>
		<div class="infos" style="display: none;">
			<strong>Ausgewählt:</strong> <span class="radius_m"></span> Meter um "<span class="zentrum_ort"></span>" (ungefähr)<br>
			<br>Willst du per E-Mail benachrichtigt werden, wenn neue Dokumente mit diesem Ortsbezug erscheinen?
		</div>
		<form method="POST" action="<?= CHtml::encode($this->createUrl("benachrichtigungen/index")) ?>">
			<input type="hidden" name="geo_lng" value="">
			<input type="hidden" name="geo_lat" value="">
			<input type="hidden" name="geo_radius" id="geo_radius" value="">
			<input type="hidden" name="krit_str" value="">

			<div>
				<button class="btn btn-primary ben_add_geo" disabled name="<?= AntiXSS::createToken("ben_add_geo") ?>" type="submit">Benachrichtigen!</button>
			</div>
		</form>
	</div>
</div>

<script>
	yepnope({
		load: ["/js/Leaflet/leaflet.js", "/js/leaflet.fullscreen/Control.FullScreen.js", <?=json_encode($assets_base)?> +"/ba_features.js",
			"/js/Leaflet.draw/dist/leaflet.draw.js",
			"/js/OverlappingMarkerSpiderfier-Leaflet/oms.min.js",
			"/js/leaflet.textmarkers.js"
		],
		complete: function () {
			var $map = $("#map").AntraegeKarte({
				benachrichtigungen_widget: "benachrichtigung_hinweis",
				outlineBA: <?=$ba->ba_nr?>
			});
			$map.AntraegeKarte("setAntraegeData", <?=json_encode($geodata)?>);
		}
	});
</script>


<div class="row">
	<div class="col col-lg-4" id="stadtratsdokumente_holder">
		<? $this->renderPartial("ba_antraege_liste", array(
			"antraege"    => $antraege,
			"titel"       => "Dokumente der letzten $tage_vergangenheit Tage"
		)); ?>
	</div>
	<div class="col col-lg-4 keine_dokumente">
		<h3>Neue Sitzungsdokumente</h3>
		<?
		if (count($termin_dokumente) == 0) echo "<p class='keine_gefunden'>Keine neue BA-Dokumente in den letzten $tage_vergangenheit Tagen</p>";
		else {
			?>
			<ul class="antragsliste"><?
				foreach ($termin_dokumente as $termin) {
					$ts = RISTools::date_iso2timestamp($termin->termin);
					echo "<li><div class='antraglink'>" . CHtml::encode(date("j.M, H:i", $ts) . ", " . $termin->gremium->name) . "</div>";
					foreach ($termin->antraegeDokumente as $dokument) {
						echo "<ul class='dokumente'><li>";
						echo "<div style='float: right;'>" . CHtml::encode(date("j.M", RISTools::date_iso2timestamp($dokument->datum))) . "</div>";
						echo CHtml::link($dokument->name, $dokument->getOriginalLink());
						echo "</li></ul>";
					}
					echo "</li>";
				}
				?></ul>
		<? } ?>
	</div>

	<div class="col col-lg-4 keine_dokumente">
		<h3>Kommende Termine</h3>
		<?
		$data = ba_gruppiere_termine($termine_zukunft);
		if (count($data) == 0) echo "<p class='keine_gefunden'>Keine Termine in den nächsten $tage_zukunft Tagen</p>";
		else {
			?>
			<ul class="terminliste"><?

				foreach ($data as $termin) {
					echo "<li><div class='termin'>" . CHtml::encode($termin["datum"] . ", " . $termin["ort"]) . "</div><div class='termindetails'>";
					$gremien = array();
					foreach ($termin["gremien"] as $name => $links) {
						foreach ($links as $link) $gremien[] = CHtml::link($name, $link);
					}
					echo implode(", ", $gremien);
					echo "</div></li>";
				}
				?></ul>
		<? }
		?>

		<h3>Vergangene Termine</h3>
		<?
		$data = ba_gruppiere_termine($termine_vergangenheit);
		if (count($data) == 0) echo "<p class='keine_gefunden'>Keine Termine in den letzten $tage_vergangenheit Tagen</p>";
		else {
			?>
			<ul class="terminliste"><?
				foreach ($data as $termin) {
					echo "<li><div class='termin'>" . CHtml::encode($termin["datum"] . ", " . $termin["ort"]) . "</div><div class='termindetails'>";
					$gremien = array();
					foreach ($termin["gremien"] as $name => $links) {
						if (count($links) == 1) $gremien[] = CHtml::link($name, $links[0]);
						else {
							$str = CHtml::encode($name);
							for ($i = 0; $i < count($links); $i++) $str .= " [" . CHtml::link($i + 1, $links[$i]) . "]";
							$gremien[] = $str;
						}
					}
					echo implode(", ", $gremien);
					echo "</div></li>";
				}
				?></ul>
		<? }
		?>

	</div>
</div>
