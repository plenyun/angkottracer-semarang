<?php

function _base_url($_path) {
	return "/angkottracer".$_path;
}

function _complete_url($_path) {
	//return "http://usf.fsm.undip.ac.id"._base_url($_path);
	return "http://localhost"._base_url($_path);
}

function template($filePath, $data = null) {
	require APP_PATH."/view/skin/header.php";
	require APP_PATH."/view/".$filePath;
	require APP_PATH."/view/skin/footer.php";
}

function template_admin($filePath, $data = null) {
	require APP_PATH."/view/admin/skin/admin_header.php";
	if (!isset($data['useSimple']))
		require APP_PATH."/view/admin/skin/admin_sidebar.php";
	require APP_PATH."/view/".$filePath;
	require APP_PATH."/view/admin/skin/admin_footer.php";
}

// Fungsi mengecek sesi. Jika tidak sesuai dengan $sessionType, maka program akan
//	meredirect ke halaman yang sesuai.
// $sessionType = -1, berarti tidak ada sesi (tidak sedang Login)
function cek_sesi_aktif($sessionType = -1, $redirectPage = true) {
	if (isset($_SESSION['ec_usertype'])) { // ada sesi aktif
		if ($_SESSION['ec_usertype'] == $sessionType) { // Cek jenis sesi...
			return true; // Sesuai
		} else {
			// Jenis sesi tidak sesuai, maka dialihkan ke halaman dasbor untuk akun masing-masing
			if ($redirectPage) { // redirect ke halaman dasbor
				$loginPath = 'home';
				if		($_SESSION['ec_usertype'] == 1)		{$loginPath = 'akun';}
				else if ($_SESSION['ec_usertype'] == 99)	{$loginPath = 'admin.dashboard';}
				$redirPage = _base_url("/?p=".$loginPath);
				header("Location: ".$redirPage);
			}
		}
	} else { // tidak ada sesi aktif
		if ($sessionType == -1) return true;
		else if ($redirectPage) {
			$loginPath = 'home';
			if		($sessionType == 1)		{$loginPath = 'login';}
			else if ($sessionType == 99)	{$loginPath = 'admin.login';}
			$redirPage = _base_url("/?p=".$loginPath."&next=".urlencode($_SERVER['REQUEST_URI']));
			header("Location: ".$redirPage);
		}
	}
	return false;
}

function cek_sesi_admin($redirectPage = true) {
	return (cek_sesi_aktif(99, $redirectPage));
}

// Mengecek privilege admin yang sedang login...
/*
function admin_check_prev($bitMask = -1, $redirectPage = true) {
	if (!cek_sesi_aktif(99, $redirectPage)) return false;
	if ($bitMask == -1) return true;
	if ($_SESSION['usfAdminPrev'] & $bitMask) {
		return true;
	} else {
		if ($redirectPage) header("Location: "._base_url("/?p=admin.dashboard"));
	}
	return false;
}
*/

function format_rupiah($harga) {
	return "Rp ".number_format($harga, 2, ',', '.');
}
// Format : date('D, d M Y', strtotime('06/04/1993'));
function tanggal_indonesia($tanggal) {
 
    $format = array(
        'Sun' => 'Minggu',
        'Mon' => 'Senin',
        'Tue' => 'Selasa',
        'Wed' => 'Rabu',
        'Thu' => 'Kamis',
        'Fri' => 'Jumat',
        'Sat' => 'Sabtu',
        'Jan' => 'Januari',
        'Feb' => 'Februari',
        'Mar' => 'Maret',
        'Apr' => 'April',
        'May' => 'Mei',
        'Jun' => 'Juni',
        'Jul' => 'Juli',
        'Aug' => 'Agustus',
        'Sep' => 'September',
        'Oct' => 'Oktober',
        'Nov' => 'November',
        'Dec' => 'Desember'
    );
 
    return strtr($tanggal, $format);
}

//=============== DATABASE FUNCTIONS ============================================

/**
 * Generate string query untuk objek
 * @param object $object Objek yang ingin dikonversi
 * @return string String query
 */
function _db_to_query($object) {
	if (!is_null($fValue)) {
		return "'" . mysqli_escape_string($mysql, $fValue) . "'";
	} else {
		return 'NULL';
	}
}
/**
 * Generate query INSERT INTO
 * @param string $tableName Nama tabel
 * @param array $fields Array asosiatif dari field-field untuk diinsert
 * @return string Query hasil generate
 * @author Nur Hardyanto
 */
function db_insert_into($tableName, $fields = null) {
	$queryString = "INSERT INTO ".$tableName;

	if (!empty($fields) && is_array($fields)) {
		$keys = "";
		$values = "";

		foreach ($fields as $fName => $fValue) {
			$keys .= $fName.',';
			$values .= _db_to_query($fValue).",";
		}
		$keys = trim($keys, ',');
		$values = trim($values, ',');

		$queryString .= ' (' . $keys . ') VALUES';
		$queryString .= ' (' . $values . ')';

	}
	return $queryString;
}

/**
 * Generate query UPDATE &lt;table&gt; SET
 * @param string $tableName Nama tabel
 * @param array $fields Array asosiatif dari field-field untuk diupdate
 * @return string Query hasil generate
 * @author Nur Hardyanto
 */
function db_update($tableName, $fields, $conditions = null) {
	$queryString = "UPDATE ".$tableName. " SET ";

	foreach ($fields as $fName => $fValue) {
		$queryString .= $fName .'='. _db_to_query($fValue) . ",";
	}
	$queryString = trim($queryString, ',');

	if (!empty($conditions)) {
		$queryString .= ' WHERE ';
		if (is_array($conditions)) {
			foreach ($conditions as $fName => $fValue) {
				$queryString .= '(' . $fName . '=';
				$queryString .= _db_to_query($fValue) . ") AND ";
			}
			$qLength = strlen($queryString);
			$queryString = substr($queryString, 0, $qLength-5);
		} else if (is_string($conditions)) {
			$queryString .= $conditions;
		}
	}
	return $queryString;
}