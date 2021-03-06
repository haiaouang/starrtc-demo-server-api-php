<?php
//关联聊天功能
$dir = dirname(dirname(__FILE__));
require_once($dir . '/config.php');

//ID是channelId+chatroomId， 共32位
$ID      = trimInput(array_key_exists('ID', $_REQUEST) ? $_REQUEST['ID'] : 0);
$Name    = trimInput(array_key_exists('Name', $_REQUEST) ? $_REQUEST['Name'] : 0);
$Creator = trimInput(array_key_exists('Creator', $_REQUEST) ? $_REQUEST['Creator'] : 0);

if(empty($ID) || empty($Name) || empty($Creator)){	 
	echoErr('missing args');
}	


$ret = store($g_writeMdb, $Name, $Creator, $ID);
if($ret != 0){
	echoErr('store:'.$ret);
}else{
	echoK('success');
}

function store($mdb, $Name, $Creator, $ID){	
	$ctime = date('Y-m-d H:i:s');	
	
	$roomId    = mb_substr($ID, -16, 16, "UTF-8");
	$channelId = mb_substr($ID, 0, -16,  "UTF-8");
	try{	
		$sql = "select id from live_lists where uuid = ? limit 1";
		if(!($pstmt = $mdb->prepare($sql))){
			return 12;					
		}
		
		if($pstmt->execute(array($ID))){
			$result = $pstmt->fetchAll();        
			$resNum = count($result);	
			if($resNum == 1){			
				return 16;				
			}else{	
				$ip_addr = getIp();
				$os_type = get_os_type();
				
				$sql = "insert into live_lists (uuid, channelId, roomId, name, userId, os_type, ip_addr, ctime) values (?,?,?,?,?,?,?,?)";
				if(!($pstmt = $mdb->prepare($sql))){           
					return 15;    
				} 
				if($pstmt->execute(array($ID, $channelId, $roomId, $Name, $Creator, $os_type, $ip_addr, $ctime))){				
					return 0;
				}else{
					return 14;
				}
			}			
		}else{
			return 13;
		}				
	}catch(PDOException $e){
		return 11;
	}	
	return 10;
}