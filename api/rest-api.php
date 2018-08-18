<?php


/**
 * @author Sasweblabs <develop@sasweblabs.com>
 * @copyright Desaku.web.id 2018
 * @package open_sid
 * 
 * 



/** CONFIG:START **/
$config["host"] 		= "localhost" ; 		//host
$config["user"] 		= "username mysql" ; 		//Username SQL
$config["pass"] 		= "passwordmysql" ; 		//Password SQL
$config["dbase"] 		= "namadatabsemysql" ; 		//Database
$config["utf8"] 		= true ; 		//turkish charset set false
$config["abs_url_images"] 		= "{urlopensid}/desa/upload/artikel" ; 		//Absolute Images URL
$config["abs_url_videos"] 		= "{urlopensid}/media/media/" ; 		//Absolute Videos URL
$config["abs_url_audios"] 		= "{urlopensid}/media/media/" ; 		//Absolute Audio URL
$config["abs_url_files"] 		= "{urlopensid}/media/file/" ; 		//Absolute Files URL
$config["default_image"]        = "{urlopensid}/assets/images/404-image-not-found.jpg";
$config["image_allowed"][] 		= array("mimetype"=>"image/jpeg","ext"=>"jpg") ; 		//whitelist image
$config["image_allowed"][] 		= array("mimetype"=>"image/jpg","ext"=>"jpg") ; 		
$config["image_allowed"][] 		= array("mimetype"=>"image/png","ext"=>"png") ; 		
$config["file_allowed"][] 		= array("mimetype"=>"text/plain","ext"=>"txt") ; 		
$config["file_allowed"][] 		= array("mimetype"=>"","ext"=>"tmp") ; 		
/** CONFIG:END **/

$set['kategori_berita']=1;
$set['kategori_agenda']=4;
$set['kategori_peraturan']=5;
$set['halaman_sejarah']=99;
$set['halaman_pemerintah']=92;
$set['halaman_visimisi']=93;


if(isset($_SERVER["HTTP_X_AUTHORIZATION"])){
	list($_SERVER["PHP_AUTH_USER"],$_SERVER["PHP_AUTH_PW"]) = explode(":" , base64_decode(substr($_SERVER["HTTP_X_AUTHORIZATION"],6)));
}
$rest_api=array("data"=>array("status"=>404,"title"=>"Not found"),"title"=>"Error","message"=>"Routes not found");

/** connect to mysql **/
$mysql = new mysqli($config["host"], $config["user"], $config["pass"], $config["dbase"]);
if (mysqli_connect_errno()){
	die(mysqli_connect_error());
}


if(!isset($_GET["json"])){
	$_GET["json"]= "route";
}
if((!isset($_GET["form"])) && ($_GET["json"] == "submit")) {
	$_GET["json"]= "route";
}

if($config["utf8"]==true){
	$mysql->set_charset("utf8");
}

$get_dir = explode("/", $_SERVER["PHP_SELF"]);
unset($get_dir[count($get_dir)-1]);
$main_url = "http://" . $_SERVER["HTTP_HOST"] . implode("/",$get_dir)."/";


switch($_GET["json"]){	
	// TODO: -+- Listing : berita
	case "berita":
		$rest_api=array();
		$where = $_where = null;
		// TODO: -+----+- statement where
		$_where[] = " id_kategori='".$set['kategori_berita']."' ";
		$_where[] = " enabled='1' ";
		if(isset($_GET["id"])){
			if($_GET["id"]!="-1"){
				$_where[] = "`id` LIKE '%".$mysql->escape_string($_GET["id"])."%'";
			}
		}
		if(isset($_GET["judul"])){
			if($_GET["judul"]!="-1"){
				$_where[] = "`judul` LIKE '%".$mysql->escape_string($_GET["judul"])."%'";
			}
		}
		if(isset($_GET["gambar"])){
			if($_GET["gambar"]!="-1"){
				$_where[] = "`gambar` LIKE '%".$mysql->escape_string($_GET["gambar"])."%'";
			}
		}
		if(isset($_GET["isi"])){
			if($_GET["isi"]!="-1"){
				$_where[] = "`isi` LIKE '%".$mysql->escape_string($_GET["isi"])."%'";
			}
		}
		if(is_array($_where)){
			$where = " WHERE " . implode(" AND ",$_where);
		}
		// TODO: -+----+- orderby
		$order_by = "`id`";
		$sort_by = "DESC";
		if(!isset($_GET["order"])){
			$_GET["order"] = "`id`";
		}
		// TODO: -+----+- sort asc/desc
		if(!isset($_GET["sort"])){
			$_GET["sort"] = "desc";
		}
		if($_GET["sort"]=="asc"){
			$sort_by = "ASC";
		}else{
			$sort_by = "DESC";
		}
		if($_GET["order"]=="id"){
			$order_by = "`id`";
		}
		if($_GET["order"]=="judul"){
			$order_by = "`judul`";
		}
		if($_GET["order"]=="gambar"){
			$order_by = "`gambar`";
		}
		if($_GET["order"]=="isi"){
			$order_by = "`isi`";
		}
		if($_GET["order"]=="random"){
			$order_by = "RAND()";
		}
		$limit = 100;
		if(isset($_GET["limit"])){
			$limit = (int)$_GET["limit"] ;
		}
		// TODO: -+----+- SQL Query
		$sql = "SELECT * FROM `artikel` ".$where." ORDER BY tgl_upload  desc LIMIT 0, ".$limit." " ;
		if($result = $mysql->query($sql)){
			$z=0;
			while ($data = $result->fetch_array()){
				if(isset($data['id'])){$rest_api[$z]['id'] = $data['id'];}; # id
				if(isset($data['judul'])){$rest_api[$z]['judul'] = $data['judul'];}; # heading-1
				
				$abs_url_images = $config['abs_url_images'].'/';
				$abs_url_videos = $config['abs_url_videos'].'/';
				$abs_url_audios = $config['abs_url_audios'].'/';
				if(!isset($data['gambar'])){$data['gambar']='undefined';}; # images
				if((substr($data['gambar'], 0, 7)=='http://')||(substr($data['gambar'], 0, 8)=='https://')){
					$abs_url_images = $abs_url_videos  = $abs_url_audios = '';
				}
				
				if(substr($data['gambar'], 0, 5)=='data:'){
					$abs_url_images = $abs_url_videos  = $abs_url_audios = '';
				}
				
			    if($data['gambar'] != ''){
					$rest_api[$z]['gambar'] = $abs_url_images . 'sedang_'.$data['gambar']; # images
				}else{
					$rest_api[$z]['gambar'] = $config["default_image"] ; # images
				}
				if(isset($data['isi'])){$rest_api[$z]['isi'] = $data['isi'];}; # to_trusted
				$z++;
			}
			$result->close();
			if(isset($_GET["id"])){
				if(isset($rest_api[0])){
					$rest_api = $rest_api[0];
				}
			}
		}

		break;
	case "agenda":
		$rest_api=array();
		$where = $_where = null;
		// TODO: -+----+- statement where
			$_where[] = " id_kategori='".$set['kategori_agenda']."' ";
		$_where[] = " enabled='1' ";
		if(isset($_GET["id"])){
			if($_GET["id"]!="-1"){
				$_where[] = "`id` LIKE '%".$mysql->escape_string($_GET["id"])."%'";
			}
		}
		if(isset($_GET["judul"])){
			if($_GET["judul"]!="-1"){
				$_where[] = "`judul` LIKE '%".$mysql->escape_string($_GET["judul"])."%'";
			}
		}
		if(isset($_GET["gambar"])){
			if($_GET["gambar"]!="-1"){
				$_where[] = "`gambar` LIKE '%".$mysql->escape_string($_GET["gambar"])."%'";
			}
		}
		if(isset($_GET["isi"])){
			if($_GET["isi"]!="-1"){
				$_where[] = "`isi` LIKE '%".$mysql->escape_string($_GET["isi"])."%'";
			}
		}
		if(is_array($_where)){
			$where = " WHERE " . implode(" AND ",$_where);
		}
		// TODO: -+----+- orderby
		$order_by = "`id`";
		$sort_by = "DESC";
		if(!isset($_GET["order"])){
			$_GET["order"] = "`id`";
		}
		// TODO: -+----+- sort asc/desc
		if(!isset($_GET["sort"])){
			$_GET["sort"] = "desc";
		}
		if($_GET["sort"]=="asc"){
			$sort_by = "ASC";
		}else{
			$sort_by = "DESC";
		}
		if($_GET["order"]=="id"){
			$order_by = "`id`";
		}
		if($_GET["order"]=="judul"){
			$order_by = "`judul`";
		}
		if($_GET["order"]=="gambar"){
			$order_by = "`gambar`";
		}
		if($_GET["order"]=="isi"){
			$order_by = "`isi`";
		}
		if($_GET["order"]=="random"){
			$order_by = "RAND()";
		}
		$limit = 100;
		if(isset($_GET["limit"])){
			$limit = (int)$_GET["limit"] ;
		}
		// TODO: -+----+- SQL Query
		$sql = "SELECT * FROM `artikel` ".$where." ORDER BY tgl_upload  desc LIMIT 0, ".$limit." " ;
		if($result = $mysql->query($sql)){
			$z=0;
			while ($data = $result->fetch_array()){
				if(isset($data['id'])){$rest_api[$z]['id'] = $data['id'];}; # id
				if(isset($data['judul'])){$rest_api[$z]['judul'] = $data['judul'];}; # heading-1
				
				$abs_url_images = $config['abs_url_images'].'/';
				$abs_url_videos = $config['abs_url_videos'].'/';
				$abs_url_audios = $config['abs_url_audios'].'/';
				if(!isset($data['gambar'])){$data['gambar']='undefined';}; # images
				if((substr($data['gambar'], 0, 7)=='http://')||(substr($data['gambar'], 0, 8)=='https://')){
					$abs_url_images = $abs_url_videos  = $abs_url_audios = '';
				}
				
				if(substr($data['gambar'], 0, 5)=='data:'){
					$abs_url_images = $abs_url_videos  = $abs_url_audios = '';
				}
				
			    if($data['gambar'] != ''){
					$rest_api[$z]['gambar'] = $abs_url_images . 'sedang_'.$data['gambar']; # images
				}else{
					$rest_api[$z]['gambar'] = $config["default_image"] ; # images
				}
				if(isset($data['isi'])){$rest_api[$z]['isi'] = $data['isi'];};
				if(isset($data['tgl_upload'])){$rest_api[$z]['tgl_upload'] = $data['tgl_upload'];}; # to_trusted
				$z++;
			}
			$result->close();
			if(isset($_GET["id"])){
				if(isset($rest_api[0])){
					$rest_api = $rest_api[0];
				}
			}
		}

		break;	
	case "peraturan":
		$rest_api=array();
		$where = $_where = null;
		// TODO: -+----+- statement where
			$_where[] = " id_kategori='".$set['kategori_peraturan']."' ";
		$_where[] = " enabled='1' ";
		if(isset($_GET["id"])){
			if($_GET["id"]!="-1"){
				$_where[] = "`id` LIKE '%".$mysql->escape_string($_GET["id"])."%'";
			}
		}
		if(isset($_GET["judul"])){
			if($_GET["judul"]!="-1"){
				$_where[] = "`judul` LIKE '%".$mysql->escape_string($_GET["judul"])."%'";
			}
		}
		if(isset($_GET["gambar"])){
			if($_GET["gambar"]!="-1"){
				$_where[] = "`gambar` LIKE '%".$mysql->escape_string($_GET["gambar"])."%'";
			}
		}
		if(isset($_GET["isi"])){
			if($_GET["isi"]!="-1"){
				$_where[] = "`isi` LIKE '%".$mysql->escape_string($_GET["isi"])."%'";
			}
		}
		if(is_array($_where)){
			$where = " WHERE " . implode(" AND ",$_where);
		}
		// TODO: -+----+- orderby
		$order_by = "`id`";
		$sort_by = "DESC";
		if(!isset($_GET["order"])){
			$_GET["order"] = "`id`";
		}
		// TODO: -+----+- sort asc/desc
		if(!isset($_GET["sort"])){
			$_GET["sort"] = "desc";
		}
		if($_GET["sort"]=="asc"){
			$sort_by = "ASC";
		}else{
			$sort_by = "DESC";
		}
		if($_GET["order"]=="id"){
			$order_by = "`id`";
		}
		if($_GET["order"]=="judul"){
			$order_by = "`judul`";
		}
		if($_GET["order"]=="gambar"){
			$order_by = "`gambar`";
		}
		if($_GET["order"]=="isi"){
			$order_by = "`isi`";
		}
		if($_GET["order"]=="random"){
			$order_by = "RAND()";
		}
		$limit = 100;
		if(isset($_GET["limit"])){
			$limit = (int)$_GET["limit"] ;
		}
		// TODO: -+----+- SQL Query
		$sql = "SELECT * FROM `artikel` ".$where." ORDER BY tgl_upload  desc LIMIT 0, ".$limit." " ;
		if($result = $mysql->query($sql)){
			$z=0;
			while ($data = $result->fetch_array()){
				if(isset($data['id'])){$rest_api[$z]['id'] = $data['id'];}; # id
				if(isset($data['judul'])){$rest_api[$z]['judul'] = $data['judul'];}; # heading-1
				
				$abs_url_images = $config['abs_url_images'].'/';
				$abs_url_videos = $config['abs_url_videos'].'/';
				$abs_url_audios = $config['abs_url_audios'].'/';
				if(!isset($data['gambar'])){$data['gambar']='undefined';}; # images
				if((substr($data['gambar'], 0, 7)=='http://')||(substr($data['gambar'], 0, 8)=='https://')){
					$abs_url_images = $abs_url_videos  = $abs_url_audios = '';
				}
				
				if(substr($data['gambar'], 0, 5)=='data:'){
					$abs_url_images = $abs_url_videos  = $abs_url_audios = '';
				}
				
			    if($data['gambar'] != ''){
					$rest_api[$z]['gambar'] = $abs_url_images . 'sedang_'.$data['gambar']; # images
				}else{
					$rest_api[$z]['gambar'] = $config["default_image"] ; # images
				}
				if(isset($data['isi'])){$rest_api[$z]['isi'] = $data['isi'];}; # to_trusted
				$z++;
			}
			$result->close();
			if(isset($_GET["id"])){
				if(isset($rest_api[0])){
					$rest_api = $rest_api[0];
				}
			}
		}

		break;	
	case "sejarah":
		$rest_api=array();
		$where = $_where = null;
		// TODO: -+----+- statement where
			$_where[] = " id='".$set['halaman_sejarah']."' ";
		$_where[] = " enabled='1' ";
		if(isset($_GET["id"])){
			if($_GET["id"]!="-1"){
				$_where[] = "`id` LIKE '%".$mysql->escape_string($_GET["id"])."%'";
			}
		}
		if(isset($_GET["judul"])){
			if($_GET["judul"]!="-1"){
				$_where[] = "`judul` LIKE '%".$mysql->escape_string($_GET["judul"])."%'";
			}
		}
		if(isset($_GET["gambar"])){
			if($_GET["gambar"]!="-1"){
				$_where[] = "`gambar` LIKE '%".$mysql->escape_string($_GET["gambar"])."%'";
			}
		}
		if(isset($_GET["isi"])){
			if($_GET["isi"]!="-1"){
				$_where[] = "`isi` LIKE '%".$mysql->escape_string($_GET["isi"])."%'";
			}
		}
		if(is_array($_where)){
			$where = " WHERE " . implode(" AND ",$_where);
		}
		// TODO: -+----+- orderby
		$order_by = "`id`";
		$sort_by = "DESC";
		if(!isset($_GET["order"])){
			$_GET["order"] = "`id`";
		}
		// TODO: -+----+- sort asc/desc
		if(!isset($_GET["sort"])){
			$_GET["sort"] = "desc";
		}
		if($_GET["sort"]=="asc"){
			$sort_by = "ASC";
		}else{
			$sort_by = "DESC";
		}
		if($_GET["order"]=="id"){
			$order_by = "`id`";
		}
		if($_GET["order"]=="judul"){
			$order_by = "`judul`";
		}
		if($_GET["order"]=="gambar"){
			$order_by = "`gambar`";
		}
		if($_GET["order"]=="isi"){
			$order_by = "`isi`";
		}
		if($_GET["order"]=="random"){
			$order_by = "RAND()";
		}
		$limit = 100;
		if(isset($_GET["limit"])){
			$limit = (int)$_GET["limit"] ;
		}
		// TODO: -+----+- SQL Query
		$sql = "SELECT * FROM `artikel` ".$where." ORDER BY tgl_upload  desc LIMIT 0, ".$limit." " ;
		if($result = $mysql->query($sql)){
			$z=0;
			while ($data = $result->fetch_array()){
				if(isset($data['id'])){$rest_api[$z]['id'] = $data['id'];}; # id
				if(isset($data['judul'])){$rest_api[$z]['judul'] = $data['judul'];}; # heading-1
				
				$abs_url_images = $config['abs_url_images'].'/';
				$abs_url_videos = $config['abs_url_videos'].'/';
				$abs_url_audios = $config['abs_url_audios'].'/';
				if(!isset($data['gambar'])){$data['gambar']='undefined';}; # images
				if((substr($data['gambar'], 0, 7)=='http://')||(substr($data['gambar'], 0, 8)=='https://')){
					$abs_url_images = $abs_url_videos  = $abs_url_audios = '';
				}
				
				if(substr($data['gambar'], 0, 5)=='data:'){
					$abs_url_images = $abs_url_videos  = $abs_url_audios = '';
				}
				
			    if($data['gambar'] != ''){
					$rest_api[$z]['gambar'] = $abs_url_images . 'sedang_'.$data['gambar']; # images
				}else{
					$rest_api[$z]['gambar'] = $config["default_image"] ; # images
				}
				if(isset($data['isi'])){$rest_api[$z]['isi'] = $data['isi'];}; # to_trusted
				$z++;
			}
			$result->close();
			if(isset($_GET["id"])){
				if(isset($rest_api[0])){
					$rest_api = $rest_api[0];
				}
			}
		}

		break;	
	case "pemerintah":
		$rest_api=array();
		$where = $_where = null;
		// TODO: -+----+- statement where
			$_where[] = " id='".$set['halaman_pemerintah']."' ";
		$_where[] = " enabled='1' ";
		if(isset($_GET["id"])){
			if($_GET["id"]!="-1"){
				$_where[] = "`id` LIKE '%".$mysql->escape_string($_GET["id"])."%'";
			}
		}
		if(isset($_GET["judul"])){
			if($_GET["judul"]!="-1"){
				$_where[] = "`judul` LIKE '%".$mysql->escape_string($_GET["judul"])."%'";
			}
		}
		if(isset($_GET["gambar"])){
			if($_GET["gambar"]!="-1"){
				$_where[] = "`gambar` LIKE '%".$mysql->escape_string($_GET["gambar"])."%'";
			}
		}
		if(isset($_GET["isi"])){
			if($_GET["isi"]!="-1"){
				$_where[] = "`isi` LIKE '%".$mysql->escape_string($_GET["isi"])."%'";
			}
		}
		if(is_array($_where)){
			$where = " WHERE " . implode(" AND ",$_where);
		}
		// TODO: -+----+- orderby
		$order_by = "`id`";
		$sort_by = "DESC";
		if(!isset($_GET["order"])){
			$_GET["order"] = "`id`";
		}
		// TODO: -+----+- sort asc/desc
		if(!isset($_GET["sort"])){
			$_GET["sort"] = "desc";
		}
		if($_GET["sort"]=="asc"){
			$sort_by = "ASC";
		}else{
			$sort_by = "DESC";
		}
		if($_GET["order"]=="id"){
			$order_by = "`id`";
		}
		if($_GET["order"]=="judul"){
			$order_by = "`judul`";
		}
		if($_GET["order"]=="gambar"){
			$order_by = "`gambar`";
		}
		if($_GET["order"]=="isi"){
			$order_by = "`isi`";
		}
		if($_GET["order"]=="random"){
			$order_by = "RAND()";
		}
		$limit = 100;
		if(isset($_GET["limit"])){
			$limit = (int)$_GET["limit"] ;
		}
		// TODO: -+----+- SQL Query
		$sql = "SELECT * FROM `artikel` ".$where." ORDER BY tgl_upload  desc LIMIT 0, ".$limit." " ;
		if($result = $mysql->query($sql)){
			$z=0;
			while ($data = $result->fetch_array()){
				if(isset($data['id'])){$rest_api[$z]['id'] = $data['id'];}; # id
				if(isset($data['judul'])){$rest_api[$z]['judul'] = $data['judul'];}; # heading-1
				
				$abs_url_images = $config['abs_url_images'].'/';
				$abs_url_videos = $config['abs_url_videos'].'/';
				$abs_url_audios = $config['abs_url_audios'].'/';
				if(!isset($data['gambar'])){$data['gambar']='undefined';}; # images
				if((substr($data['gambar'], 0, 7)=='http://')||(substr($data['gambar'], 0, 8)=='https://')){
					$abs_url_images = $abs_url_videos  = $abs_url_audios = '';
				}
				
				if(substr($data['gambar'], 0, 5)=='data:'){
					$abs_url_images = $abs_url_videos  = $abs_url_audios = '';
				}
				
			    if($data['gambar'] != ''){
					$rest_api[$z]['gambar'] = $abs_url_images . 'sedang_'.$data['gambar']; # images
				}else{
					$rest_api[$z]['gambar'] = $config["default_image"] ; # images
				}
				if(isset($data['isi'])){$rest_api[$z]['isi'] = $data['isi'];}; # to_trusted
				$z++;
			}
			$result->close();
			if(isset($_GET["id"])){
				if(isset($rest_api[0])){
					$rest_api = $rest_api[0];
				}
			}
		}

		break;	

	case "visimisi":
		$rest_api=array();
		$where = $_where = null;
		// TODO: -+----+- statement where
			$_where[] = " id='".$set['halaman_visimisi']."' ";
		$_where[] = " enabled='1' ";
		if(isset($_GET["id"])){
			if($_GET["id"]!="-1"){
				$_where[] = "`id` LIKE '%".$mysql->escape_string($_GET["id"])."%'";
			}
		}
		if(isset($_GET["judul"])){
			if($_GET["judul"]!="-1"){
				$_where[] = "`judul` LIKE '%".$mysql->escape_string($_GET["judul"])."%'";
			}
		}
		if(isset($_GET["gambar"])){
			if($_GET["gambar"]!="-1"){
				$_where[] = "`gambar` LIKE '%".$mysql->escape_string($_GET["gambar"])."%'";
			}
		}
		if(isset($_GET["isi"])){
			if($_GET["isi"]!="-1"){
				$_where[] = "`isi` LIKE '%".$mysql->escape_string($_GET["isi"])."%'";
			}
		}
		if(is_array($_where)){
			$where = " WHERE " . implode(" AND ",$_where);
		}
		// TODO: -+----+- orderby
		$order_by = "`id`";
		$sort_by = "DESC";
		if(!isset($_GET["order"])){
			$_GET["order"] = "`id`";
		}
		// TODO: -+----+- sort asc/desc
		if(!isset($_GET["sort"])){
			$_GET["sort"] = "desc";
		}
		if($_GET["sort"]=="asc"){
			$sort_by = "ASC";
		}else{
			$sort_by = "DESC";
		}
		if($_GET["order"]=="id"){
			$order_by = "`id`";
		}
		if($_GET["order"]=="judul"){
			$order_by = "`judul`";
		}
		if($_GET["order"]=="gambar"){
			$order_by = "`gambar`";
		}
		if($_GET["order"]=="isi"){
			$order_by = "`isi`";
		}
		if($_GET["order"]=="random"){
			$order_by = "RAND()";
		}
		$limit = 100;
		if(isset($_GET["limit"])){
			$limit = (int)$_GET["limit"] ;
		}
		// TODO: -+----+- SQL Query
		$sql = "SELECT * FROM `artikel` ".$where." ORDER BY tgl_upload  desc LIMIT 0, ".$limit." " ;
		if($result = $mysql->query($sql)){
			$z=0;
			while ($data = $result->fetch_array()){
				if(isset($data['id'])){$rest_api[$z]['id'] = $data['id'];}; # id
				if(isset($data['judul'])){$rest_api[$z]['judul'] = $data['judul'];}; # heading-1
				
				$abs_url_images = $config['abs_url_images'].'/';
				$abs_url_videos = $config['abs_url_videos'].'/';
				$abs_url_audios = $config['abs_url_audios'].'/';
				if(!isset($data['gambar'])){$data['gambar']='undefined';}; # images
				if((substr($data['gambar'], 0, 7)=='http://')||(substr($data['gambar'], 0, 8)=='https://')){
					$abs_url_images = $abs_url_videos  = $abs_url_audios = '';
				}
				
				if(substr($data['gambar'], 0, 5)=='data:'){
					$abs_url_images = $abs_url_videos  = $abs_url_audios = '';
				}
				
			    if($data['gambar'] != ''){
					$rest_api[$z]['gambar'] = $abs_url_images . 'sedang_'.$data['gambar']; # images
				}else{
					$rest_api[$z]['gambar'] = $config["default_image"] ; # images
				}
				if(isset($data['isi'])){$rest_api[$z]['isi'] = $data['isi'];}; # to_trusted
				$z++;
			}
			$result->close();
			if(isset($_GET["id"])){
				if(isset($rest_api[0])){
					$rest_api = $rest_api[0];
				}
			}
		}
case "user":
		$rest_api=array();
		$where = $_where = null;
		// TODO: -+----+- statement where
		if(isset($_GET["id"])){
			if($_GET["id"]!="-1"){
				$_where[] = "`id` LIKE '%".$mysql->escape_string($_GET["id"])."%'";
			}
		}
		if(isset($_GET["nik"])){
			if($_GET["nik"]!="-1"){
				$_where[] = "`nik` LIKE '%".$mysql->escape_string($_GET["nik"])."%'";
			}
		}
		if(isset($_GET["pin"])){
			if($_GET["pin"]!="-1"){
				$_where[] = "`pin` LIKE '%".$mysql->escape_string($_GET["pin"])."%'";
			}
		}
		if(is_array($_where)){
			$where = " WHERE " . implode(" AND ",$_where);
		}
		// TODO: -+----+- orderby
		$order_by = "`id`";
		$sort_by = "DESC";
		if(!isset($_GET["order"])){
			$_GET["order"] = "`id`";
		}
		// TODO: -+----+- sort asc/desc
		if(!isset($_GET["sort"])){
			$_GET["sort"] = "desc";
		}
		if($_GET["sort"]=="asc"){
			$sort_by = "ASC";
		}else{
			$sort_by = "DESC";
		}
		if($_GET["order"]=="id"){
			$order_by = "`id`";
		}
		if($_GET["order"]=="nik"){
			$order_by = "`nik`";
		}
		if($_GET["order"]=="pin"){
			$order_by = "`pin`";
		}
		if($_GET["order"]=="random"){
			$order_by = "RAND()";
		}
		// TODO: -+----+- SQL Query
		$sql = "SELECT * FROM `user` ".$where."ORDER BY ".$order_by." ".$sort_by." LIMIT 0, 100" ;
		if($result = $mysql->query($sql)){
			$z=0;
			while ($data = $result->fetch_array()){
				if(isset($data['id'])){$rest_api[$z]['id'] = $data['id'];}; # id
				#
				$abs_url_images = $config['abs_url_images'].'/';
				$abs_url_videos = $config['abs_url_videos'].'/';
				$abs_url_audios = $config['abs_url_audios'].'/';
				if(!isset($data['nik'])){$data['nik']='undefined';}; # as_username
				if((substr($data['nik'], 0, 7)=='http://')||(substr($data['nik'], 0, 8)=='https://')){
					$abs_url_images = $abs_url_videos  = $abs_url_audios = '';
				}
				
				if(substr($data['nik'], 0, 5)=='data:'){
					$abs_url_images = $abs_url_videos  = $abs_url_audios = '';
				}
				
				#
				$abs_url_images = $config['abs_url_images'].'/';
				$abs_url_videos = $config['abs_url_videos'].'/';
				$abs_url_audios = $config['abs_url_audios'].'/';
				if(!isset($data['pin'])){$data['pin']='undefined';}; # as_password
				if((substr($data['pin'], 0, 7)=='http://')||(substr($data['pin'], 0, 8)=='https://')){
					$abs_url_images = $abs_url_videos  = $abs_url_audios = '';
				}
				
				if(substr($data['pin'], 0, 5)=='data:'){
					$abs_url_images = $abs_url_videos  = $abs_url_audios = '';
				}
				
				$z++;
			}
			$result->close();
			if(isset($_GET["id"])){
				if(isset($rest_api[0])){
					$rest_api = $rest_api[0];
				}
			}
		}

		break;
		case "auth":
		// TODO: -+----+- Auth User
		
		$is_user = false;
		if(isset($_SERVER["PHP_AUTH_USER"])){
			$php_auth_user = $mysql->escape_string($_SERVER["PHP_AUTH_USER"]);
			$php_auth_pw = $mysql->escape_string($_SERVER["PHP_AUTH_PW"]);
			$pin = strrev($php_auth_pw);
										$pin = $pin*77;
										$pin .= "!#@$#%";
										$pin = md5($pin); 
			$auth_sql = "SELECT * FROM `tweb_penduduk_mandiri` WHERE `nik` = '$php_auth_user' AND `pin` = '$pin'";
			if($result = $mysql->query($auth_sql)){
				$current_user = $result->fetch_array();
				if(isset($current_user["nik"])){
					$is_user = true;
				}
			}
			if($is_user === true){
				$rest_api=array("data"=>array("status"=>200,"error"=>"Successfully"),"title"=>"Successfully","message"=>"Successfully");
			}else{
				$rest_api=array("data"=>array("status"=>401,"error"=>"Unauthorized"),"title"=>"Failed","message"=>"Nik atau Pin salah silahkan coba lagi.");
			}
		}else{
			$rest_api=array("data"=>array("status"=>401,"error"=>"Unauthorized"),"title"=>"Unauthorized","message"=>"Anda tidak di izinkan melihat halaman ini.");
			break;
		}

		break;
	// TODO: -+- me
	case "me":
		// TODO: -+----+- Auth User
		$is_user = false;
		if(isset($_SERVER["PHP_AUTH_USER"])){
			$php_auth_user = $mysql->escape_string($_SERVER["PHP_AUTH_USER"]);
			$php_auth_pw = $mysql->escape_string($_SERVER["PHP_AUTH_PW"]);
			
			$auth_sql = "SELECT tp.*,tk.no_kk FROM tweb_penduduk tp 
			join tweb_keluarga tk on (tk.id=tp.id_kk)
			WHERE tp.nik = '$php_auth_user' 
			";
			if($result = $mysql->query($auth_sql)){
				$current_user = $result->fetch_array();
				if(isset($current_user["nik"])){
					$is_user = true;
				}
			}
			if($is_user == true){
				$rest_api["data"]["status"]=200;
				$rest_api["me"]["id"]= $current_user["id"];
				$rest_api["me"]["nik"]= $current_user["nik"];
				$rest_api["me"]["nama"]= $current_user["nama"];
				$rest_api["me"]["no_kk"]= $current_user["no_kk"];
				$rest_api["me"]["tanggallahir"]= $current_user["tanggallahir"];
				$rest_api["me"]["alamat"]= $current_user["alamat_sekarang"];
			}else{
				$rest_api=array("data"=>array("status"=>401,"error"=>"Unauthorized"),"title"=>"Failed","message"=>"Silahkan login terlebih dahulu");
			}
		}else{
			$rest_api=array("data"=>array("status"=>401,"error"=>"Unauthorized"),"title"=>"Unauthorized","message"=>"Silahkan login terlebih dahulu");
			break;
		}
		break;		
	// TODO: -+- route
	case "route":		$rest_api=array();
		$rest_api["site"]["name"] = "Open SID" ;
		$rest_api["site"]["description"] = "Aplikasi Android Open SID" ;
		$rest_api["site"]["imabuilder"] = "rev18.04.16" ;

		$rest_api["routes"][0]["namespace"] = "berita";
		$rest_api["routes"][0]["tb_version"] = "Upd.1808150740";
		$rest_api["routes"][0]["methods"][] = "GET";
		$rest_api["routes"][0]["args"]["id"] = array("required"=>"false","description"=>"Selecting `berita` based `id`");
		$rest_api["routes"][0]["args"]["judul"] = array("required"=>"false","description"=>"Selecting `berita` based `judul`");
		$rest_api["routes"][0]["args"]["gambar"] = array("required"=>"false","description"=>"Selecting `berita` based `gambar`");
		$rest_api["routes"][0]["args"]["isi"] = array("required"=>"false","description"=>"Selecting `berita` based `isi`");
		$rest_api["routes"][0]["args"]["order"] = array("required"=>"false","description"=>"order by `random`, `id`, `judul`, `gambar`, `isi`");
		$rest_api["routes"][0]["args"]["sort"] = array("required"=>"false","description"=>"sort by `asc` or `desc`");
		$rest_api["routes"][0]["args"]["limit"] = array("required"=>"false","description"=> "limit the items that appear","type"=>"number");
		$rest_api["routes"][0]["_links"]["self"] = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"]."?json=berita";
		$rest_api["routes"][1]["namespace"] = "agenda";
		$rest_api["routes"][1]["tb_version"] = "Upd.1808150740";
		$rest_api["routes"][1]["methods"][] = "GET";
		$rest_api["routes"][1]["args"]["id"] = array("required"=>"false","description"=>"Selecting `agenda` based `id`");
		$rest_api["routes"][1]["args"]["judul"] = array("required"=>"false","description"=>"Selecting `agenda` based `judul`");
		$rest_api["routes"][1]["args"]["gambar"] = array("required"=>"false","description"=>"Selecting `agenda` based `gambar`");
		$rest_api["routes"][1]["args"]["isi"] = array("required"=>"false","description"=>"Selecting `agenda` based `isi`");
		$rest_api["routes"][1]["args"]["order"] = array("required"=>"false","description"=>"order by `random`, `id`, `judul`, `gambar`, `isi`");
		$rest_api["routes"][1]["args"]["sort"] = array("required"=>"false","description"=>"sort by `asc` or `desc`");
		$rest_api["routes"][1]["args"]["limit"] = array("required"=>"false","description"=> "limit the items that appear","type"=>"number");
		$rest_api["routes"][1]["_links"]["self"] = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"]."?json=agenda";
		$rest_api["routes"][2]["namespace"] = "peraturan";
		$rest_api["routes"][2]["tb_version"] = "Upd.1808150740";
		$rest_api["routes"][2]["methods"][] = "GET";
		$rest_api["routes"][2]["args"]["id"] = array("required"=>"false","description"=>"Selecting `peraturan` based `id`");
		$rest_api["routes"][2]["args"]["judul"] = array("required"=>"false","description"=>"Selecting `peraturan` based `judul`");
		$rest_api["routes"][2]["args"]["gambar"] = array("required"=>"false","description"=>"Selecting `peraturan` based `gambar`");
		$rest_api["routes"][2]["args"]["isi"] = array("required"=>"false","description"=>"Selecting `peraturan` based `isi`");
		$rest_api["routes"][2]["args"]["order"] = array("required"=>"false","description"=>"order by `random`, `id`, `judul`, `gambar`, `isi`");
		$rest_api["routes"][2]["args"]["sort"] = array("required"=>"false","description"=>"sort by `asc` or `desc`");
		$rest_api["routes"][2]["args"]["limit"] = array("required"=>"false","description"=> "limit the items that appear","type"=>"number");
		$rest_api["routes"][2]["_links"]["self"] = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"]."?json=peraturan";
			$rest_api["routes"][3]["namespace"] = "sejarah";
		$rest_api["routes"][3]["tb_version"] = "Upd.1808150927";
		$rest_api["routes"][3]["methods"][] = "GET";
		$rest_api["routes"][3]["args"]["id"] = array("required"=>"false","description"=>"Selecting `sejarah` based `id`");
		$rest_api["routes"][3]["args"]["judul"] = array("required"=>"false","description"=>"Selecting `sejarah` based `judul`");
		$rest_api["routes"][3]["args"]["gambar"] = array("required"=>"false","description"=>"Selecting `sejarah` based `gambar`");
		$rest_api["routes"][3]["args"]["isi"] = array("required"=>"false","description"=>"Selecting `sejarah` based `isi`");
		$rest_api["routes"][3]["args"]["order"] = array("required"=>"false","description"=>"order by `random`, `id`, `judul`, `gambar`, `isi`");
		$rest_api["routes"][3]["args"]["sort"] = array("required"=>"false","description"=>"sort by `asc` or `desc`");
		$rest_api["routes"][3]["args"]["limit"] = array("required"=>"false","description"=> "limit the items that appear","type"=>"number");
		$rest_api["routes"][3]["_links"]["self"] = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"]."?json=sejarah";
		$rest_api["routes"][4]["namespace"] = "pemerintah";
		$rest_api["routes"][4]["tb_version"] = "Upd.1808150946";
		$rest_api["routes"][4]["methods"][] = "GET";
		$rest_api["routes"][4]["args"]["id"] = array("required"=>"false","description"=>"Selecting `pemerintah` based `id`");
		$rest_api["routes"][4]["args"]["judul"] = array("required"=>"false","description"=>"Selecting `pemerintah` based `judul`");
		$rest_api["routes"][4]["args"]["gambar"] = array("required"=>"false","description"=>"Selecting `pemerintah` based `gambar`");
		$rest_api["routes"][4]["args"]["isi"] = array("required"=>"false","description"=>"Selecting `pemerintah` based `isi`");
		$rest_api["routes"][4]["args"]["order"] = array("required"=>"false","description"=>"order by `random`, `id`, `judul`, `gambar`, `isi`");
		$rest_api["routes"][4]["args"]["sort"] = array("required"=>"false","description"=>"sort by `asc` or `desc`");
		$rest_api["routes"][4]["args"]["limit"] = array("required"=>"false","description"=> "limit the items that appear","type"=>"number");
		$rest_api["routes"][4]["_links"]["self"] = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"]."?json=pemerintah";
		$rest_api["routes"][5]["namespace"] = "visimisi";
		$rest_api["routes"][5]["tb_version"] = "Upd.1808150937";
		$rest_api["routes"][5]["methods"][] = "GET";
		$rest_api["routes"][5]["args"]["id"] = array("required"=>"false","description"=>"Selecting `visimisi` based `id`");
		$rest_api["routes"][5]["args"]["judul"] = array("required"=>"false","description"=>"Selecting `visimisi` based `judul`");
		$rest_api["routes"][5]["args"]["gambar"] = array("required"=>"false","description"=>"Selecting `visimisi` based `gambar`");
		$rest_api["routes"][5]["args"]["isi"] = array("required"=>"false","description"=>"Selecting `visimisi` based `isi`");
		$rest_api["routes"][5]["args"]["order"] = array("required"=>"false","description"=>"order by `random`, `id`, `judul`, `gambar`, `isi`");
		$rest_api["routes"][5]["args"]["sort"] = array("required"=>"false","description"=>"sort by `asc` or `desc`");
		$rest_api["routes"][5]["args"]["limit"] = array("required"=>"false","description"=> "limit the items that appear","type"=>"number");
		$rest_api["routes"][5]["_links"]["self"] = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"]."?json=visimisi";
		$rest_api["routes"][6]["namespace"] = "me";
		$rest_api["routes"][6]["methods"][] = "GET";
		$rest_api["routes"][6]["_links"]["self"] = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"]."?json=me";
		$rest_api["routes"][7]["namespace"] = "auth";
		$rest_api["routes"][7]["methods"][] = "GET";
		$rest_api["routes"][7]["_links"]["self"] = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"]."?json=auth";
		$rest_api["routes"][8]["namespace"] = "submit/me";
		$rest_api["routes"][8]["methods"][] = "POST";
		$rest_api["routes"][8]["_links"]["self"] = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"]."?json=submit&form=me";
		$rest_api["routes"][9]["namespace"] = "submit/user";
		$rest_api["routes"][9]["tb_version"] = "";
		$rest_api["routes"][9]["methods"][] = "POST";
		$rest_api["routes"][9]["_links"]["self"] = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"]."?json=submit&form=user";
		$rest_api["routes"][9]["args"]["fullname"] = array("required"=>"true","description"=>"Insert data to field `fullname` in table `user`");
	    $rest_api["routes"][9]["args"]["nik"] = array("required"=>"true","description"=>"Insert data to field `nik` in table `user`");
		$rest_api["routes"][9]["args"]["pin"] = array("required"=>"true","description"=>"Insert data to field `pin` in table `user`");
		$rest_api["routes"][10]["namespace"] = "user";
		$rest_api["routes"][10]["tb_version"] = "Upd.1712040835";
		$rest_api["routes"][10]["methods"][] = "GET";
		$rest_api["routes"][10]["args"]["id"] = array("required"=>"false","description"=>"Selecting `user` based `id`");
		$rest_api["routes"][10]["args"]["order"] = array("required"=>"false","description"=>"order by `random`, `id`");
		$rest_api["routes"][10]["args"]["sort"] = array("required"=>"false","description"=>"sort by `asc` or `desc`");
		$rest_api["routes"][10]["_links"]["self"] = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"]."?json=user";
		$rest_api["routes"][11]["namespace"] = "submit/lapor";
		$rest_api["routes"][11]["tb_version"] = "";
		$rest_api["routes"][11]["methods"][] = "POST";
		$rest_api["routes"][11]["_links"]["self"] = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"]."?json=submit&form=lapor";
		$rest_api["routes"][11]["args"]["nik"] = array("required"=>"true","description"=>"Insert data to field `nik` in table `lapor`");
		$rest_api["routes"][11]["args"]["lapor"] = array("required"=>"true","description"=>"Insert data to field `Lapor` in table `lapor`");
		
		break;
	// TODO: -+- submit

	case "submit":
		$rest_api=array();

		$rest_api["methods"][0] = "POST";
		$rest_api["methods"][1] = "GET";
        switch($_GET["form"]){
			// TODO: -+----+- user
				case "user":


				$rest_api["auth"]["basic"] = false;

				$rest_api["args"]["nik"] = array("required"=>"true","description"=>"Receiving data from the input `nik`");
				$rest_api["args"]["pin"] = array("required"=>"true","description"=>"Receiving data from the input `pin`");
				if(!isset($_POST["nik"])){
					$_POST["nik"]="";
				}
				if(!isset($_POST["pin"])){
					$_POST["pin"]="";
				}
				$rest_api["message"] = "Please! complete the form provided.";
				$rest_api["title"] = "Notice!";
				if(($_POST["nik"] != "") || ($_POST["pin"] != "")){
					// avoid undefined
					$input["nik"] = "";
					$input["pin"] = "";
					// variable post
					if(isset($_POST["nik"])){
						$input["nik"] = $mysql->escape_string($_POST["nik"]);
					}

					if(isset($_POST["pin"])){
						$input["pin"] = $mysql->escape_string($_POST["pin"]);
					}

					$auth_sql = "SELECT * FROM `tweb_penduduk` WHERE `nik` = '".$input["nik"]."'";
					if($result = $mysql->query($auth_sql)){
						
						$current_user = $result->fetch_array();
							if(isset($current_user["nik"])){
								$auth_sql2 = "SELECT * FROM `tweb_penduduk_mandiri` WHERE `nik` = '".$input["nik"]."'";
								if($result2 = $mysql->query($auth_sql2)){
									 $current_user2 = $result2->fetch_array();
									 if(isset($current_user2["nik"])){
										 $rest_api["message"] = "NIK sudah terdaftar sebelumnya,silahkan login.";
								         $rest_api["title"] = "Error!";
									 }
									 else{
										$pin = strrev($input["pin"]);
										$pin = $pin*77;
										$pin .= "!#@$#%";
										$pin = md5($pin); 
										$sql_query = "INSERT INTO `tweb_penduduk_mandiri` (`nik`,`pin`,`id_pend`) VALUES ('".$input["nik"]."','".$pin."','".$current_user["id"]."' )";
										if($query = $mysql->query($sql_query)){
											$rest_api["message"] = "Pendaftaran anda berhasil,silahkan login!";
											$rest_api["title"] = "Berhasil";
										}else{
											$rest_api["message"] = "Form input and SQL Column do not match.";
											$rest_api["title"] = "Fatal Error!";
										} 
									 }
										 
								}	
								else{
									$rest_api["message"] = "Form input and SQL Column do not match.";
									$rest_api["title"] = "Fatal Error!";
								}
								
								
								
							}
                            else{
								$rest_api["message"] = "NIK anda tidak terdaftar.";
								$rest_api["title"] = "Error!";
							}							
								
					}
					else{
									$rest_api["message"] = "Form input and SQL Column do not match.";
									$rest_api["title"] = "Fatal Error!";
								}
					
				}else{
					$rest_api["message"] = "Please! complete the form provided.";
					$rest_api["title"] = "Notice!";
				}

				break;
			case "lapor":


				$rest_api["auth"]["basic"] = false;

				$rest_api["args"]["nik"] = array("required"=>"true","description"=>"Receiving data from the input `nik`");
				$rest_api["args"]["lapor"] = array("required"=>"true","description"=>"Receiving data from the input `Lapor`");
				if(!isset($_POST["nik"])){
					$_POST["nik"]="";
				}
				if(!isset($_POST["lapor"])){
					$_POST["lapor"]="";
				}
				$rest_api["message"] = "Please! complete the form provided.";
				$rest_api["title"] = "Notice!";
				if(($_POST["nik"] != "") || ($_POST["lapor"] != "")){
					// avoid undefined
					$input["nik"] = "";
					$input["lapor"] = "";
					// variable post
					if(isset($_POST["nik"])){
						$input["nik"] = $mysql->escape_string($_POST["nik"]);
					}

					if(isset($_POST["lapor"])){
						$input["lapor"] = $mysql->escape_string($_POST["lapor"]);
					}
                    $auth_sql = "SELECT * FROM `tweb_penduduk` WHERE `nik` = '".$input["nik"]."'";
					if($result = $mysql->query($auth_sql)){
						$current_user = $result->fetch_array();
						if(isset($current_user["nik"])){
					$sql_query = "INSERT INTO `komentar` (`id_artikel`,`owner`,`email`,`komentar`,`tgl_upload`) VALUES 
					('775','".$current_user["nama"]."','".$input["nik"]."','".$input["lapor"]."',NOW() )";
							if($query = $mysql->query($sql_query)){
								$rest_api["message"] = "laporan anda sudah kami terima,silahkan tunggu petugas kami untuk mengecek laporan anda";
								$rest_api["title"] = "Successfully";
							}else{
								$rest_api["message"] = "Form input and SQL Column do not match.";
								$rest_api["title"] = "Fatal Error!";
							}
					
						}
						else{
							$rest_api["message"] = "Silahkan Login Terlebih Dahulu.";
							$rest_api["title"] = "Notice!";
						}
					}	
					
					
				}else{
					$rest_api["message"] = "Please! complete the form provided.";
					$rest_api["title"] = "Notice!";
				}

				break;	
			// TODO: -+- Submit : Me
			case "me":
				// TODO: -+----+- Auth User
				$is_user = false;
				if(isset($_SERVER["PHP_AUTH_USER"])){
					$php_auth_user = $mysql->escape_string($_SERVER["PHP_AUTH_USER"]);
					$php_auth_pw = $mysql->escape_string($_SERVER["PHP_AUTH_PW"]);
					$auth_sql = "SELECT * FROM `user` WHERE `nik` = '$php_auth_user' AND `pin` = '$php_auth_pw'";
					if($result = $mysql->query($auth_sql)){
						$current_user = $result->fetch_array();
						if(isset($current_user["nik"])){
							$is_user = true;
							$update_me_sql = "UPDATE `user` SET  WHERE `nik`='$php_auth_user'";
							if($query = $mysql->query($update_me_sql)){
								$rest_api=array("data"=>array("status"=>200,"title"=>"Successfully"),"title"=>"Successfully","message"=>"You have successfully updated your data.");
							}else{
								$rest_api=array("data"=>array("status"=>200,"title"=>"Error"),"title"=>"Error","message"=>"You have fail updated your data.");
							}
						}
					}
					if($is_user == false){
						$rest_api=array("data"=>array("status"=>401,"title"=>"Unauthorized"),"title"=>"Unauthorized","message"=>"Sorry, you cannot see list resources.");
						break;
					}
				}else{
					$rest_api=array("data"=>array("status"=>401,"title"=>"Unauthorized"),"title"=>"Unauthorized","message"=>"Sorry, you cannot see list resources.");
					break;
				}

				break;
		}
	break;

}


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,PATCH,OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization,X-Authorization');
if (!isset($_GET["callback"])){
	header('Content-type: application/json');
	if(defined("JSON_UNESCAPED_UNICODE")){
		echo json_encode($rest_api,JSON_UNESCAPED_UNICODE);
	}else{
		echo json_encode($rest_api);
	}

}else{
	if(defined("JSON_UNESCAPED_UNICODE")){
		echo strip_tags($_GET["callback"]) ."(". json_encode($rest_api,JSON_UNESCAPED_UNICODE). ");" ;
	}else{
		echo strip_tags($_GET["callback"]) ."(". json_encode($rest_api) . ");" ;
	}

}