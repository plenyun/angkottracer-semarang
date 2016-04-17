<?php

/**
 * Ambil list node dengan kriteria tertentu
 * @param integer $idArea Filter ID Area, -1 untuk nonaktifkan filter ini
 * @return array|FALSE Kembali array multidimensi asosiatif dari record, atau FALSE jika gagal.
 */
function get_nodes($idArea = -1) {
	global $mysqli;
	
	$condition = array();
	if ($idArea > 0) $condition['id_area'] = _db_to_query($idArea);
	$selectQuery = db_select('nodes', $condition, '*, X(location) AS location_lng, Y(location) AS location_lat');
	$queryResult = mysqli_query($mysqli, $selectQuery);
	
	if (!$queryResult) return false;
	$index = 0;
	$listRecord = array();
	
	while ($row = mysqli_fetch_array($queryResult, MYSQLI_ASSOC)) {
		$listRecord[$index] = $row;
		$index++;
	}
	return $listRecord;
}

/**
 * Ambil data record node dengan id tertentu
 * @param integer $nodeId Node ID
 * @return array|FALSE Kembali array asosiatif dari record, atau FALSE jika gagal.
 */
function get_node_by_id($nodeId) {
	global $mysqli;

	$selectQuery = db_select('nodes', array('id_node' => $nodeId), '*, X(location) AS location_lng, Y(location) AS location_lat');
	
	$result = mysqli_query($mysqli, $selectQuery);
	
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	return $row;
}
/**
 * Simpan record node
 * @param array $nodeData Data node, field sesuai database
 * @param integer $nodeId ID node. -1 untuk insert
 * @return boolean TRUE jika berhasil, sebaliknya FALSE
 */
function save_node($nodeData, $nodeId = -1) {
	global $mysqli;
	
	$nodeFields = array();
	foreach ($nodeData as $propKey => $propValue) {
		$nodeFields[$propKey] = $propValue;
	}
	$saveQuery = "";
	if ($nodeId > 0) {
		$saveQuery = db_update('nodes', $nodeData, array('id_node' => $nodeId));
	} else {
		if (!isset($nodeFields['date_created']))
			$nodeFields['date_created'] = _db_to_query(date('Y-m-d H:i:s'));
		$saveQuery = db_insert_into('nodes', $nodeFields);
	}
	
	$queryResult = mysqli_query($mysqli, $saveQuery);
	return $queryResult;
}
