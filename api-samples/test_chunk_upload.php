<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('API_SERVER', 'https://dev.opendrive.com/api/');

//vars
$session_id = '';	//Required Session ID

//path to your file
$file_name1 = dirname(__FILE__) . '/test_upload/test_chunk_uploada'; // 51200
$file_name2 = dirname(__FILE__) . '/test_upload/test_chunk_uploadb'; // 19989
$file_chunk1_size = filesize($file_name1);
$file_chunk2_size = filesize($file_name2);

$file_size = $file_chunk1_size + $file_chunk2_size;


function checkResponse($ch, $responseData, $step)
{
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if (isset($responseData['error']) || $code != 200) {
		$message = isset($responseData['error']['message']) ? $responseData['error']['message'] : 'Upload error';
		echo "$step error: $code $message";
		exit;
	}
}

// 1. Create file
$postData = array(
	'session_id' => $session_id,
	'folder_id' => '0',					//0 - root folder, otherwise valid folder id
	'file_name' => 'test2_Sunset.jpg',
	'file_size' => $file_size
);

// 1. Setup cURL, create file
$ch = curl_init( API_SERVER . 'v1/upload/create_file.json');
curl_setopt_array($ch, array(
	CURLOPT_POST => TRUE,
	CURLOPT_RETURNTRANSFER => TRUE,
	CURLOPT_HTTPHEADER => array(
		'Content-Type: application/json'
	),
	CURLOPT_POSTFIELDS => json_encode($postData)
));

// Send the request
$response = curl_exec($ch);

// Check for errors
if($response === FALSE){
	die(curl_error($ch));
}

// Decode the response
$responseData = json_decode($response, TRUE);

echo "-- Step 1 --\n";
print_r($responseData);
checkResponse($ch, $responseData, 'create_file');
curl_close($ch);

// 2. Open File for Upload
$file_id = $responseData['FileId'];
$file_time = isset($responseData['DirUpdateTime']) ? $responseData['DirUpdateTime'] : time();

$postData = array(
	'session_id' => $session_id,
	'file_id' => $file_id,
	'file_size' => $file_size
);

// Setup cURL
$ch = curl_init(API_SERVER . 'v1/upload/open_file_upload.json');
curl_setopt_array($ch, array(
	CURLOPT_POST => TRUE,
	CURLOPT_RETURNTRANSFER => TRUE,
	CURLOPT_HTTPHEADER => array(
		'Content-Type: application/json'
	),
	CURLOPT_POSTFIELDS => json_encode($postData)
));

// Send the request
$response = curl_exec($ch);


// Check for errors
if($response === FALSE){
	die(curl_error($ch));
}

// Decode the response
$responseData = json_decode($response, TRUE);

echo "-- Step 2 --\n";
print_r($responseData);

checkResponse($ch, $responseData, 'open_file_upload');
curl_close($ch);

// 3. Send Chunk 

$temp_location = $responseData['TempLocation'];

// Add file
$cfile = new CURLFile($file_name1,'application/octet-stream','test_chunk_uploada.part');

/* 
 * Set some data manually but it can be loaded with fsize() 
 * file_data should contain file
 *
 * Api method expects multipart/form-data ;
 *
 */
$postData = array(
	'session_id' => $session_id,
	'file_id' => $file_id,
	'temp_location' => $temp_location,
	'chunk_offset' => 0,
	'chunk_size' => $file_chunk1_size,
	'file_data'=>$cfile
);


// Setup cURL
$ch = curl_init(API_SERVER . 'v1/upload/upload_file_chunk.json');
curl_setopt_array($ch, array(
	CURLOPT_POST => TRUE,
	CURLOPT_RETURNTRANSFER => TRUE,
	CURLOPT_HTTPHEADER => array(
		'Expect:'
	), 
	CURLOPT_POSTFIELDS => $postData
));

//Send the request
$response = curl_exec($ch);

if($response === FALSE){
	die(curl_error($ch));
}

// Decode the response
$responseData = json_decode($response, TRUE);
echo "-- Step 3 --\n";
print_r($responseData);

checkResponse($ch, $responseData, 'upload_file_chunk step1');
curl_close($ch);

if($responseData['TotalWritten'] != $file_chunk1_size){
	echo 'upload_file_chunk step 1 error: chunk incorrectly uploaded';
	exit;
}


// 4. SEND another chunk

$cfile2 = new CURLFile($file_name2,'application/octet-stream','test_chunk_uploada.part');

$postData = array(
	'session_id' => $session_id,
	'file_id' => $file_id,
	'temp_location' => $temp_location,
	'chunk_offset' => $file_chunk1_size,
	'chunk_size' => $file_chunk2_size,
	'file_data'=>$cfile2
);

// Setup cURL
$ch = curl_init(API_SERVER . 'v1/upload/upload_file_chunk.json');
curl_setopt_array($ch, array(
	CURLOPT_POST => TRUE,
	CURLOPT_RETURNTRANSFER => TRUE,
	CURLOPT_HTTPHEADER => array(
		'Expect:'
	), 
	CURLOPT_POSTFIELDS => $postData
));

// Send the request
$response = curl_exec($ch);

// Check for errors
if($response === FALSE){
	die(curl_error($ch));
}

// Decode the response
$responseData = json_decode($response, TRUE);

echo "-- Step 4 --\n";
print_r($responseData);

checkResponse($ch, $responseData, 'upload_file_chunk step 2');
curl_close($ch);

// 5. Close File Upload
$postData = array(
	'session_id' => $session_id,
	'file_id' => $file_id,
	'file_size' => $file_size,
	'temp_location' => $temp_location,
	'file_time' => $file_time
);

// Setup cURL
$ch = curl_init(API_SERVER . 'v1/upload/close_file_upload.json');
curl_setopt_array($ch, array(
	CURLOPT_POST => TRUE,
	CURLOPT_RETURNTRANSFER => TRUE,
	CURLOPT_HTTPHEADER => array(
		'Content-Type: application/json'
	),
	CURLOPT_POSTFIELDS => json_encode($postData)
));

// Send the request
$response = curl_exec($ch);

// Check for errors
if($response === FALSE){
	die(curl_error($ch));
}

// Decode the response
$responseData = json_decode($response, TRUE);

echo "-- Step 5 --\n";
print_r($responseData);

checkResponse($ch, $responseData, 'close_file_upload');
curl_close($ch);