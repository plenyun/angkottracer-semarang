<?php

function get_edges($fetchPolylineData = false) {
	global $mysqli;
	
	$fieldList = array('*');
	if ($fetchPolylineData)
		$fieldList['polyline_data'] = 'AsText(polyline)';
	
	$fieldToSelect = _gen_fields($fieldList);
	
	$condition = array();
	$selectQuery = db_select('edges', $condition, $fieldToSelect);
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

function get_edge_by_id($idEdge) {
	global $mysqli;

	$fieldToSelect = _gen_fields(array(
			0 => '*',
			'polyline_data' => 'AsText(polyline)'
	));
	
	$condition = array('id_edge' => $idEdge);
	$selectQuery = db_select('edges', $condition, $fieldToSelect);
	$queryResult = mysqli_query($mysqli, $selectQuery);

	$row = mysqli_fetch_array($queryResult, MYSQLI_ASSOC);
	return $row;
}

function get_neighbor_edges($idNode, $joinNode = false) {
	global $mysqli;
	
	$selectQuery = "";
	
	// Generate subquery, ambil edge yang bertetangga
	$fieldToSelect = _gen_fields(array(
			0 => '*',
			'polyline_data' => 'AsText(polyline)',
			'polyline_dir' => sprintf('CASE WHEN (id_node_from=%d) THEN 1 ELSE -1 END',$idNode),
			'id_node_adj' => sprintf('CASE WHEN (id_node_from=%d) THEN id_node_dest ELSE id_node_from END',$idNode)
	));
	$subCondition = sprintf('(id_node_from=%d) OR (id_node_dest=%d)', $idNode, $idNode);
	$selectSubQuery = db_select('edges', $subCondition, $fieldToSelect);
	
	if ($joinNode) {
		$tableList = sprintf('(%s) AS v, nodes AS n', $selectSubQuery);
		
		$fieldToSelect = _gen_fields(array(
				0 => 'v.*',
				1 => 'n.node_name',
				'node_location_lng' => 'X(n.location)',
				'node_location_lat' => 'Y(n.location)'
		));
		$condition = "v.id_node_adj=n.id_node";
		$selectQuery = db_select($tableList, $condition, $fieldToSelect);
	} else {
		$selectQuery = $selectSubQuery;		
	}
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
 * Simpan record edge
 * @param array $edgeData Field edge yang ingin disimpan/diupdate
 * @param int $edgeId ID edge/sisi
 * @return int|NULL Kembali ID edge jika berhasil, atau NULL jika gagal.
 */
function save_edge($edgeData, $edgeId = -1) {
	global $mysqli;

	$edgeFields = array();
	foreach ($edgeData as $propKey => $propValue) {
		$edgeFields[$propKey] = $propValue;
	}
	$saveQuery = "";
	if ($edgeId > 0) {
		$saveQuery = db_update('edges', $edgeData, array('id_edge' => $edgeId));
	} else {
		if (!isset($edgeFields['date_created']))
			$edgeFields['date_created'] = _db_to_query(date('Y-m-d H:i:s'));
		$saveQuery = db_insert_into('edges', $edgeFields);
	}

	$queryResult = mysqli_query($mysqli, $saveQuery);
	if ($queryResult) {
		if ($edgeId > 0) {
			return $edgeId;
		} else {
			$newId = mysqli_insert_id($mysqli);
			return $newId;
		}
	} else return null;
}

/**
 * Hapus edge berdasar ID edge
 * 
 * @param ID busur $idEdge
 * @param bool $softDelete [Optional, default = FALSE] Soft delete?
 * @return bool TRUE jika berhasil, NULL jika gagal
 */
function delete_edge($idEdge, $softDelete = false) {
	global $mysqli;
	
	$idEdge = intval($idEdge);
	$deleteQuery = db_delete_where('edges', array('id_edge' => $idEdge));
	
	$queryResult = mysqli_query($mysqli, $deleteQuery);
	if ($queryResult) {
		return true;
	} else return null;
}