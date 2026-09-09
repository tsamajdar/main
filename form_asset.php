<?php

//For more Info: Please visit: http://www.discussdesk.com/bootstrap-datatable-with-add-edit-remove-option-in-php-mysql-ajax.htm

	// VARIABLES
    $aColumns = array('id', 'asset_no', 'asset_desc','category','sub_category','amount','loc','sub_loc','make','model','serial','asset_alias','unit','qty','inv_no','vendor_name','acq_dt','active','desc1','comment');
	$sIndexColumn = "id";

$sTable = "asset_master";
	$gaSql['user'] = "root";
	$gaSql['password'] = "7j5MA2pD3sz2K3aT";
	$gaSql['db'] = "asset";
	$gaSql['server'] = "localhost";
error_reporting(E_ALL ^ E_NOTICE);
error_reporting(E_ERROR | E_PARSE);

session_start();
$user=$_SESSION['user'];
$pass=$_SESSION['pass'];
$dept=$_SESSION['dept'];
//$user=$_GET['u'];

//$dept=$_GET['d'];
   
include("connect.php");   
   
 
  
	    dbinit($gaSql);
	    $check_dept="select name from department WHERE sname='$dept'";   
	    $result=mysqli_query($gaSql['link'], $check_dept);   
	  		while($row = mysqli_fetch_array( $result))
						{	
		
                            $dept_name = $row[0];

							}
   
   
       $_SESSION['user']=$user;//here session is used and value of $user_email store in $_SESSION. 
	  $_SESSION['dept']=$dept;  

	// DATABASE CONNECTION
	function dbinit(&$gaSql) {
		// ERROR HANDLING
		function fatal_error($sErrorMessage = '') {
			header($_SERVER['SERVER_PROTOCOL'] .' 500 Internal Server Error');
			die($sErrorMessage);
		}

		// REUSE EXISTING CONNECTION
		if ( isset($gaSql['link']) && $gaSql['link'] instanceof mysqli ) {
			return;
		}

		// MYSQLI CONNECT
		if ( !$gaSql['link'] = @mysqli_connect($gaSql['server'], $gaSql['user'], $gaSql['password']) ) {
			fatal_error('Could not open connection to server');
		}

		// MYSQLI DATABASE SELECT
		if ( !mysqli_select_db($gaSql['link'], $gaSql['db']) ) {
			fatal_error('Could not select database');
		}
	}

	// AJAX EDIT FROM JQUERY
	if ( isset($_GET['edit']) && 0 < intval($_GET['edit']) ) {
		dbinit($gaSql);

		// SAVE DATA
		if ( isset($_POST) ) {
			$p = $_POST;
			foreach ( $p as &$val ) $val = mysqli_real_escape_string($gaSql['link'], $val);
			if ( !empty($p['asset_no']) && !empty($p['asset_desc']) && !empty($p['category']) )
	@mysqli_query($gaSql['link'], " UPDATE $sTable SET asset_no = '" . $p['asset_no'] . "', asset_desc = '" . $p['asset_desc'] . "',category = '" . $p['category'] .  "',acq_dt = '" . $p['acq_dt'] ."',sub_category = '" . $p['sub_category'] . "',amount = '" . $p['amount'] . "', loc = '" . $p['loc'] . "', sub_loc = '" . $p['sub_loc'] . "',make = '" . $p['make'] . "',unit = '" . $p['unit'] ."',qty = '" . $p['qty'] ."',inv_no = '" . $p['inv_no'] ."',vendor_name = '" . $p['vendor_name'] ."',model = '" . $p['model'] . "',serial = '" . $p['serial'] . "',comment = '" . $p['comment'] . "',active = '" . $p['active'] . "',desc1 = '" . $p['desc1'] . "',asset_alias = '" . $p['asset_alias'] . "' WHERE id = " . intval($_GET['edit']));
		}

		// GET DATA
		$query = mysqli_query($gaSql['link'], " SELECT * FROM $sTable WHERE    $sIndexColumn = " . intval($_GET['edit']));
		die(json_encode(mysqli_fetch_assoc($query)));
	}

	// AJAX ADD FROM JQUERY
	if ( isset($_GET['add']) && isset($_POST) ) {
		dbinit($gaSql);


		$p = $_POST;
		

		foreach ( $p as &$val ) $val = mysqli_real_escape_string($gaSql['link'], $val);
		if ( !empty($p['asset_no']) && !empty($p['asset_desc']) && !empty($p['category'])&& !empty($p['loc']) ) {

		$sql2="select scat,ssubcat from depreciation  where  category='".$p['category']."' and subcategory='".$p['sub_category']."'";
		$result2 = mysqli_query($gaSql['link'], $sql2);

					while($row = mysqli_fetch_array($result2))
						{
						$cat1=$row[0];				   
						$subcat1=$row[1];	
						}
				
				
				
				
$loc=$p['loc'];	
$sql1="select loc_short from location where loc = '$loc'";
	$result = mysqli_query($gaSql['link'], $sql1);
	while($row = mysqli_fetch_array($result))
						{
						$asset_loc=$row[0];					   
	
		}
               
              
                
$sql1="select count(id)+1 from asset_master where loc='$loc'";
	$result = mysqli_query($gaSql['link'], $sql1);
	while($row = mysqli_fetch_array($result))
						{
						$req_no=$row[0];					   
	
						}
function find_asset($asset_desc, $amount,$inv_no,$acq_dt,$vendor_name,$loc,$make,$model,$serial)	
	{
	global $gaSql;
	$sql1="select count(*) from asset_master where loc='$loc' and asset_desc='$asset_desc' and amount=$amount and inv_no='$inv_no' and acq_dt='$acq_dt' and vendor_name='$vendor_name' and make='$make' and model='$model' and serial='$serial'";
	$result = mysqli_query($gaSql['link'], $sql1);
	while($row = mysqli_fetch_array($result))
						{
						$flag=$row[0];					   
	
						}
	return $flag;
	
	}
	
     $count=0;  
	 $flag=find_asset($p['asset_desc'], $p['amount'],$p['inv_no'],$p['acq_dt'],$p['vendor_name'],$p['loc'],$p['make'],$p['model'],$p['serial'])	;
	 if ($flag==0)
	 {
        while ($count<$p['qty'])
        {
            $assetno=$cat1."/".$subcat1."/" .$asset_loc."/".$req_no ;
			@mysqli_query($gaSql['link'], " INSERT INTO $sTable (asset_no,asset_desc,category,sub_category,amount,loc,sub_loc,make,model,serial,asset_alias,unit,qty,inv_no,acq_dt,vendor_name,comment,active,desc1) VALUES ('".$assetno."', '" . $p['asset_desc'] . "', '" . $p['category'] . "', '" .$p['sub_category']. "','" . $p['amount'] . "','" . $p['loc'] . "','" . $p['sub_loc'] . "','" . $p['make'] . "','" . $p['model'] . "','" . $p['serial'] . "','" . $p['asset_alias'] . "','" . $p['unit'] . "','" . 1 . "','" . $p['inv_no'] . "','" . $p['acq_dt'] . "','" . $p['vendor_name'] . "','" . $p['comment'] . "','" . $p['active'] . "','" . $p['desc1'] . "')");
			
                   $count=$count+1; 
                   $req_no=$req_no+1;
                   
                   
        }
}
	else
	{
	echo "<script> alert(' Same record is there please check');</script>";	
		}
        $id = mysqli_insert_id($gaSql['link']);
			$query = mysqli_query($gaSql['link'], " SELECT * FROM $sTable WHERE  $sIndexColumn = " . $id);
			die(json_encode(mysqli_fetch_assoc($query)));
        }
	}

	// AJAX REMOVE FROM JQUERY
	if ( isset($_GET['remove']) && 0 < intval($_GET['remove'])  ) {
		dbinit($gaSql);

		// REMOVE DATA
		@mysqli_query($gaSql['link'], " DELETE FROM $sTable WHERE id = " . intval($_GET['remove']));
                }
 
	


	// AJAX FROM JQUERY
	if ( isset($_GET['ajax']) ) {
		dbinit($gaSql);

		// QUERY LIMIT
		$sLimit = "";
		if ( isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1' ) {
			$sLimit = "LIMIT " . intval($_GET['iDisplayStart']) . ", " . intval($_GET['iDisplayLength']);
		}

		// QUERY ORDER
		$sOrder = "";
		if ( isset($_GET['iSortCol_0']) ) {
			$sOrder = "ORDER BY ";
			for ( $i = 0; $i < intval($_GET['iSortingCols']); $i++ ) {
				if ( $_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true" ) {
					$sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " " . ( $_GET['sSortDir_' . $i] === 'asc' ? 'asc' : 'desc' ) . ", ";
				}
			}
			$sOrder = substr_replace($sOrder, "", -2);
			if ( $sOrder == "ORDER BY" ) $sOrder = "";
		}

		// QUERY SEARCH
                $type=asset_type($user);
		if ($dept<>'SRFTI')
                        $sWhere = "where loc='$dept' and comment='$type'";
                        else 
                             $sWhere = "where comment='$type'";
		if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" ) {
			$sWhere = "WHERE (";
			for ( $i = 0; $i < count($aColumns); $i++ ) {
				if ( isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" ) {
					$sWhere .= $aColumns[$i] . " LIKE '%" . mysqli_real_escape_string($gaSql['link'], $_GET['sSearch']) . "%' OR ";
				}
			}
			$sWhere = substr_replace($sWhere, "", -3);
			$sWhere .= ')';
		}

		// BUILD QUERY
		for ( $i = 0; $i < count($aColumns); $i++ ) {
			if ( isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '' ) {
				if ( $sWhere == "" ) $sWhere = "WHERE ";
				else $sWhere .= " AND ";
				$sWhere .= $aColumns[$i] . " LIKE '%" . mysqli_real_escape_string($gaSql['link'], $_GET['sSearch_' . $i]) . "%' ";
			}
		}

		// FETCH
		$sQuery = " SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . " FROM $sTable $sWhere $sOrder $sLimit ";
		$rResult = mysqli_query($gaSql['link'], $sQuery) or fatal_error('MySQL Error: ' . mysqli_errno($gaSql['link']));
		$sQuery = " SELECT FOUND_ROWS() ";
		$rResultFilterTotal = mysqli_query($gaSql['link'], $sQuery) or fatal_error('MySQL Error: ' . mysqli_errno($gaSql['link']));
		$aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
		$iFilteredTotal = $aResultFilterTotal[0];
		$sQuery = " SELECT COUNT(" . $sIndexColumn . ") FROM $sTable ";
		$rResultTotal = mysqli_query($gaSql['link'], $sQuery) or fatal_error('MySQL Error: ' . mysqli_errno($gaSql['link']));
		$aResultTotal = mysqli_fetch_array($rResultTotal);
		$iTotal = $aResultTotal[0];
		while ( $aRow = mysqli_fetch_array($rResult) ) {
			$row = array();
			for ( $i = 0 ; $i < count($aColumns); $i++ ) {
				if ( $aColumns[$i] == "version" ) $row[] = ( $aRow[$aColumns[$i]] == "0" ) ? '-' : $aRow[$aColumns[$i]];
				else if ( $aColumns[$i] != ' ' ) $row[] = $aRow[$aColumns[$i]];
			}
			
//<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
$output['aaData'][] = array_merge($row, array('<a data-id="row-' . $row[0] . '" href="javascript:editRow(' . $row[0] . ');" class="fa fa-pencil-square-o">edit</a>&nbsp;<a href="" class="fa fa-times" >remove</a>'));
		}

		// RETURN IN JSON
		die(json_encode($output));
	}
function asset_type($user)
{
      global $gaSql;
      $sql1="select type from user where username = '$user'";
	$result = mysqli_query($gaSql['link'], $sql1);
	while($row = mysqli_fetch_array($result))
						{
						$type=$row[0];					   
	
		}
                return $type;
}
?>
<html>
	<head>
		<title>Asset Master </title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.10/css/jquery.dataTables.css">
 <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/sb-admin.css" rel="stylesheet">
 <link rel="stylesheet" href="bootstrap-datetimepicker-master/css/bootstrap-datetimepicker.min.css">
	 <script src="bootstrap-datetimepicker-master/js/bootstrap-datetimepicker.js"></script>
    <!-- Custom CSS -->
    <!-- Custom Fonts -->
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

<!-- Start: Google analytics code-->

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-38304687-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();


clear_form()
{
document.add-form.reset();
}
</script>

<!-- End: Google analytics code-->
<style>
th{
font-size: 14px;
font-family:"Times New Roman", Times, serif
}
td{
font-size: 12px;
font-family:"Times New Roman", Times, serif
}
.navbar {
 background-color:#6D3F04;
 }
 
 .navbar-brand
{
padding-top:1px;
  }
  
  .page-header{
 
    align: center;
	line-height: 1.4;
	 color: #805006;
	   font-size: 18px;
   }
   #footer {
    bottom: 0;
    width: 100%;
    position: absolute;
    height: $height-footer;
    background-color: #f5f5f5;
    .footer-block {
      margin: 0px 0;
    }
  }
</style>
	</head>
	<body>
<?php echo 'hghghjgggj'.$type;?>
    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.html"><img src="image/logo.png"></a>
            </div>
            <!-- Top Menu Items -->
		
            <ul class="nav navbar-right top-nav">
					<li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $user;?> <b class="caret"></b></a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-envelope"></i> <b class="caret"></b></a>
                    <ul class="dropdown-menu message-dropdown">
                        <li class="message-preview">
                            <a href="#">
                                <div class="media">
                                    <span class="pull-left">
                                        <img class="media-object" src="http://placehold.it/50x50" alt="">
                                    </span>
                                    <div class="media-body">
                                        <h5 class="media-heading"><strong>	</strong>
                                        </h5>
                                        <p class="small text-muted"><i class="fa fa-clock-o"></i> Yesterday at 4:32 PM</p>
                                        <p>message1...</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="message-preview">
                            <a href="#">
                                <div class="media">
                                    <span class="pull-left">
                                        <img class="media-object" src="http://placehold.it/50x50" alt="">
                                    </span>
                                    <div class="media-body">
                                        <h5 class="media-heading"><strong></strong>
                                        </h5>
                                        <p class="small text-muted"><i class="fa fa-clock-o"></i> Yesterday at 4:32 PM</p>
                                        <p>message2...</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="message-preview">
                            <a href="#">
                                <div class="media">
                                    <span class="pull-left">
                                        <img class="media-object" src="http://placehold.it/50x50" alt="">
                                    </span>
                                    <div class="media-body">
                                        <h5 class="media-heading"><strong></strong>
                                        </h5>
                                        <p class="small text-muted"><i class="fa fa-clock-o"></i> Yesterday at 4:32 PM</p>
                                        <p>message3...</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="message-footer">
                            <a href="#">Read All New Messages</a>
                        </li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell"></i> <b class="caret"></b></a>
                    <ul class="dropdown-menu alert-dropdown">
                        <li>
                            <a href="#">Alert Name <span class="label label-default">Alert Badge</span></a>
                        </li>
                        <li>
                            <a href="#">Alert Name <span class="label label-primary">Alert Badge</span></a>
                        </li>
                        <li>
                            <a href="#">Alert Name <span class="label label-success">Alert Badge</span></a>
                        </li>
                        <li>
                            <a href="#">Alert Name <span class="label label-info">Alert Badge</span></a>
                        </li>
                        <li>
                            <a href="#">Alert Name <span class="label label-warning">Alert Badge</span></a>
                        </li>
                        <li>
                            <a href="#">Alert Name <span class="label label-danger">Alert Badge</span></a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">View All</a>
                        </li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i><?php echo $dept;?> <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="#"><i class="fa fa-fw fa-user"></i> Profile</a>
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-fw fa-envelope"></i> Inbox</a>
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-fw fa-gear"></i> Settings</a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="logout.php"><i class="fa fa-fw fa-power-off"></i> Log Out</a>
                        </li>
                    </ul>
                </li>
            </ul>
            <!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->
                  <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav side-nav">
                    <?php
                  $type=  asset_type($user);
                    
                    if($type=='NAC'){?>
                    <li class="active">
                        <a href="dashboard.php?u=<?php echo $user;?>&d=<?php echo $dept;?>"><i class="fa fa-fw fa-dashboard"></i> Dashboard</a>                    </li>
                </li>			 
                    <?php }
                    elseif ($type=='AC')
                    {?>
                <li class="active">
                        <a href="dashboard_ac.php?u=<?php echo $user;?>&d=<?php echo $dept;?>"><i class="fa fa-fw fa-dashboard"></i> Dashboard</a>                    </li>
                    </li><?php }?>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </nav>
        <div id="page-wrapper">

            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="page-header">
                            Asset Master </h1>
                        <ol class="breadcrumb">
                            <li>
                                <i class="fa fa-dashboard"></i>  <a href="javascript:history.back()">Dashboard</a>
                            </li>
                            <li class="active">
                                <i class="fa fa-edit"></i> Forms
                            </li>
                        </ol>
                    </div>
                </div>

		<div class="container-fluid">
                    <?php if ($dept<>'SRFTI'){?>
		<button type="button" style="padding:10px; margin:0 50px 15px 0;" class="fa fa-plus pull-right" data-toggle="modal" data-target="#add-modal"><b>Add Asset </b></button>
                    <?php }?><div class="row">
<div class="col-md-12 marginT20">

		<div class="table-responsive demo-x content">
		<table id="example" class="display" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>#</th>
					<th>Asset No</th>
					<th>Asset Desc</th>
					<th>Category</th>
					<th>Sub category</th>
					<th>Amount</th>
					<th>Location</th>
					<th>Sub Location</th>
					<th>Make</th>
					<th>Model</th>
					<th>Serial</th>
						<?php if($user=="sound"){
										?>
					<th>Departmental Asset Category</th>
					<?php }else { ?>
						<th>Departmental Asset No</th>
						<?php }?>
                                        <th>Unit</th>
                                        <th>Quantity</th>
                                        <th>Invoice No.</th>
					<th>Vendor Name</th>
                                         <th>Acquisition date.</th>
                                      
                                         <th>Active</th>
                                         <th>Remarks</th>
                                         <th>Ac/Non-AC</th>
                                         
                                        
					<th style="background-image: none">Edit</th>
				</tr>
			</thead>
		</table>

            <!-- Brand and toggle get grouped for better mobile display -->
        

   <p class="footer-block " align="center"> Copyright © 2018 Powered by SRFTI. All rights reserved</p>  


		</div>

		</div>
		
		</div>
	
		</div>
		 
 <div class="modal fade" id="edit-modal" tabindex="-1" role="dialog" aria-labelledby="edit-modal-label">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		    	<form class="form-horizontal" id="edit-form">
			      <div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			        <h4 class="modal-title" id="edit-modal-label">Edit selected row</h4>
			      </div>
			
			      <div class="modal-body">
				  			        	<div class="form-group">
					    	<label for="add-firstname" class="col-sm-2 control-label">Asset No </label>
					    	<div class="col-sm-10">
			      		<input type="hidden" id="edit-id" value="" class="hidden">
			      			        	 	<input type="text" class="form-control" id="asset_no" name="asset_no" placeholder="Asset No" >
					</div>
					</div>
					  	<div class="form-group">
					    	<label for="add-email" class="col-sm-2 control-label">Asset Desc</label>
					    	<div class="col-sm-10">
			<input type="text" class="form-control" id="asset_desc" name="asset_desc" placeholder="Asset Details" required>
					    	</div>
					  	</div>
					  	<div class="form-group">
					    	<label for="add-mobile" class="col-sm-2 control-label">Category</label>
					    	<div class="col-sm-10">
					      		
									<select class="form-control" name="category" id="category">
									<option value='Bulding-Infra'>Bulding-Infra</option>
    								<option value='Electrical Equipments'>Electrical Equipments</option>
	 								<option value='Other Equipments'>Other Equipments</option>
	 								 <option value='Furniture & Fixture'>Furniture & Fixture</option>
	 	  							<option value='Plant & Machinery'>Plant & Machinery</option>
									<option value='Vehicle, Vessels & Aircraft'>Vehicle, Vessels & Aircraft
		</option> 
									
		
                              </select>
					    	</div>
					  	</div>
						<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Sub Category</label>
					    	<div class="col-sm-10">
					      		
								<select class="form-control" name="sub_category" id="sub_category">
								<?php $query = "SELECT distinct subcategory FROM  depreciation order by subcategory";
							$result = mysqli_query($gaSql['link'], $query);

							while($row = mysqli_fetch_array($result))
						{
						$subcategory=$row[0];					   
		echo " <option value='$subcategory'>".$subcategory."</option>";		
						}
							?>		
		
                              </select>
					    	</div>
					  	</div>
												<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Amount</label>
					    	<div class="col-sm-10">
					      		<input type="text" class="form-control" id="amount" name="amount" placeholder="amount" required>
					    	</div>
					  	</div>
                                  	<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Quantity</label>
					    	<div class="col-sm-10">
					      		<input type="text" class="form-control" id="qty" name="qty" placeholder="Quantity" required>
					    	</div>
					  	</div>
                                  	<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Unit</label>
					    	<div class="col-sm-10">
					      		<select class="form-control" name="unit" id="unit">
							
					  <option >Nos</option>
	 					<option >Pcs</option>
	 					
					
							
								</select>
					    	</div>
					  	</div>
                                  	<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Supplier Name</label>
					    	<div class="col-sm-10">
						<input type="text" class="form-control" id="vendor_name" name="vendor_name" placeholder="Supplier Name" >		
					    	</div>
					  	</div>
                                  	<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Invoice No.</label>
					    	<div class="col-sm-10">
						<input type="text" class="form-control" id="inv_no" name="inv_no" placeholder="Invoice No." >		
					    	</div>
					  	</div>
                                  <div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">acquisition Date.</label>
					    	<div class="col-sm-10">
						<input type="text" class="form-control" id="acq_dt" name="acq_dt" placeholder="dd.mm.yyyy" >		
					    	</div>
					  	</div>
						
												<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Location</label>
					    	<div class="col-sm-10">
						<input type="text" class="form-control" id="loc" name="loc" placeholder=" location" required>		
					    	</div>
					  	</div>
                                  <div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Sub Location</label>
					    	<div class="col-sm-10">
						<input type="text" class="form-control" id="sub_loc" name="sub_loc" placeholder="change Sub location" required>		
					    	</div>
					  	</div>
                                            <div class="form-group">
					    	<label for="add-mobile" class="col-sm-2 control-label">Purpose</label>
					    	<div class="col-sm-10">					      		
						<select class="form-control" name="comment" id="comment">
						<option value='NAC' default>Non Academic</option>
                                                <option value='AC'>Academic</option>		
										
                                                <option value='rent'>Resource for Rent</option>				
                                                </select>
					    	</div>
                                                </div>
                                  <div class="form-group">
					    	<?php if($user=="sound"){
										?>
										<label for="mobile" class="col-sm-2 control-label">Departmental Asset Category.</label>
					    	<div class="col-sm-10">
					      	
								<select class="form-control" name="asset_alias" id="asset_alias" >
									
    								<option value=''>Select Category from the List</option>
	 								<option value='Location Recording Equipment'>Location Recording Equipment</option>
	 								 <option value='Studio Based Equipment'>Studio Based Equipment</option>
                                       <option value='Studio'>Studio</option>
									    <option value='Other Equipment'>Other Equipment</option>
	 	  							
									
		
                              </select>	 
					    	</div>
							<?php }else{?>
					    	<label for="mobile" class="col-sm-2 control-label">Departmental Asset No.</label>
					    	<div class="col-sm-10">
					      	
								 	<input type="text" class="form-control" id="asset_alias" name="asset_alias" placeholder="Departmental Asset No" >
								 
					    	</div>
							<?php }?>
					  	</div>
					  <div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Make</label>
					    	<div class="col-sm-10">
					      	
								 	<input type="text" class="form-control" id="make" name="make" placeholder="Make" >
								 
					    	</div>
					  	</div>
						<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Model</label>
					    	<div class="col-sm-10">
					      	
								 	<input type="text" class="form-control" id="model" name="model" placeholder="Model" >
								 
					    	</div>
					  	</div>
						<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Serial</label>
					    	<div class="col-sm-10">
					      	
								 	<input type="text" class="form-control" id="serial" name="serial" placeholder="serial" >
								 
					    	</div>
					  	</div>
						
						<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Asset Status</label>
					    	<div class="col-sm-10">
					      	
						<select class="form-control" name="active" id="comment">
						<option value='yes' default>Active</option>
                                                <option value='no'>Not-in-Use</option>					
                                                </select>
								 
					    	</div>
					  	</div>
                                
                                  
                                                 <div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Remarks</label>
					    	<div class="col-sm-10">
					      	
							<textarea id="desc1" class="form-control" name="desc1" rows="4" cols="50" placeholder="">

</textarea>		 
								 
					    	</div>
					  	</div>
						
												
                         
							  			
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			        <button type="submit" class="btn btn-primary">Save changes</button>
			      </div>
		      	</form>
		    </div>
		  </div>
		</div>

		<div class="modal fade" id="add-modal" tabindex="-1" role="dialog" aria-labelledby="add-modal-label">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		    	<form class="form-horizontal" id="add-form" name="add-form" >
			      <div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			        <h4 class="modal-title" id="add-modal-label">Add New Asset</h4>
			      </div>
			      <div class="modal-body">
					  			       <div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Asset No </label>
					    	<div class="col-sm-10">
			      		<input type="hidden" id="edit-id" value="" class="hidden">
                                        <input type="text" class="form-control" id="asset_no" name="asset_no"  value="Auto generated "placeholder="Asset No" readonly >
					</div>
					</div>
					  	<div class="form-group">
					    	<label for="add-email" class="col-sm-2 control-label">Asset Desc *</label>
					    	<div class="col-sm-10">
			<input type="text" class="form-control" id="asset_desc" name="asset_desc" placeholder="Asset Details" required>
					    	</div>
					  	</div>
					  	<div class="form-group">
					    	<label for="add-mobile" class="col-sm-2 control-label">Category *</label>
					    	<div class="col-sm-10">
					      		
									<select class="form-control" name="category" id="category" onclick="processForm()" required>
									
    								<option value=''>Select Category from the List</option>
	 								<option value='Other Equipments'>Other Equipments</option>
	 								 <option value='Furniture & Fixture'>Furniture & Fixture</option>
                                                                         <option value='Electrical Equipments'>Electrical Equipments</option>
	 	  							<option value='Plant & Machinery'>Plant & Machinery</option>
	    							<option value='Vehicle, Vessels & Aircraft'>Vehicle, Vessels & Aircraft
		</option> 
                <option value='Bulding-Infra'>Bulding-Infra</option>
									
		
                              </select>
					    	</div>
					  	</div>
                                  	<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Location *</label>
					    	<div class="col-sm-10">
                                                    <input type="text" class="form-control" id="loc" name="loc" value="<?php echo $dept;?>" required readonly>
								
					    	</div>
					  	</div>
                                   <div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Sub Location</label>
					    	<div class="col-sm-10">
						<input type="text" class="form-control" id="sub_loc" name="sub_loc" value="<?php echo $dept;?>" placeholder="change Sub location" required>		
					    	</div>
					  	</div>
						<div class="form-group">
                                                    
					    	<label for="mobile" class="col-sm-2 control-label">Sub Category *</label>
                                               
					    	<div class="col-sm-10" id="message">
					      		
				
					   		</div>
					  	</div>
						
						
							
												<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Amount *</label>
					    	<div class="col-sm-10">
					      		<input type="text" class="form-control" id="amount" value="0" name="amount" placeholder="amount" required>
					    	</div>
					  	</div>
                                  	<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Quantity </label>
					    	<div class="col-sm-10">
					      		<input type="text" class="form-control" id="qty" name="qty" placeholder="Quantity" value="1" required>
					    	</div>
					  	</div>
                                  	<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Unit *</label>
					    	<div class="col-sm-10">
					      		<select class="form-control" name="unit" id="unit">
							
					  <option >Nos</option>
	 					<option >Pcs</option>
	 					
					
							
								</select>
					    	</div>
					  	</div>
                                  	<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Supplier Name *</label>
					    	<div class="col-sm-10">
						<input type="text" class="form-control" id="vendor_name" name="vendor_name" placeholder="Supplier Name" >		
					    	</div>
					  	</div>
                                  	<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Invoice No. *</label>
					    	<div class="col-sm-10">
						<input type="text" class="form-control" id="inv_no" name="inv_no" placeholder="Invoice No." >		
					    	</div>
					  	</div>
						
										
		 <div class="form-group">
    <label class="control-label col-lg-2" for="acquisition" >Acquisition Date * :</label>
	<div class="col-sm-10"> 

	 	<input type="text" class="form-control datepicker" id="acq_dt" name="acq_dt" pattern="\d{1,2}.\d{1,2}.\d{4}" placeholder="Date must be in dd.mm.yyyy format eg 01.12.2018" >
		</div>
   </div>
                                 <?php  $type=asset_type($user);?>
			     <div class="form-group">
					    	<label for="add-mobile" class="col-sm-2 control-label">Purpose *</label>
					    	<div class="col-sm-10">
                                                    <input type="text" class="form-control" id="comment" name="comment" value="<?php echo $type;?>"  readonly>
								
					    	</div>
                                                </div>	
                                  <div class="form-group">
									<?php if($user=="sound"){
										?>
										<label for="mobile" class="col-sm-2 control-label">Departmental Asset Category.</label>
					    	<div class="col-sm-10">
					      	
								<select class="form-control" name="asset_alias" id="asset_alias" >
									
    								<option value=''>Select Category from the List</option>
	 								<option value='Location Recording Equipment'>Location Recording Equipment</option>
	 								 <option value='Studio Based Equipment'>Studio Based Equipment</option>
									 <option value='Studio'>Studio</option>
                                       <option value='Other Equipment'>Other Equipment</option>
	 	  							
									
		
                              </select>	 
					    	</div>
							<?php }else{?>
					    	<label for="mobile" class="col-sm-2 control-label">Departmental Asset No.</label>
					    	<div class="col-sm-10">
					      	
								 	<input type="text" class="form-control" id="asset_alias" name="asset_alias" placeholder="Departmental Asset No" >
								 
					    	</div>
							<?php }?>
					  	</div>
                     <div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Make</label>
					    	<div class="col-sm-10">
					      	
								 	<input type="text" class="form-control" id="make" name="make" placeholder="Make" >
								 
					    	</div>
					  	</div>
						<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Model</label>
					    	<div class="col-sm-10">
					      	
								 	<input type="text" class="form-control" id="model" name="model" placeholder="Model" >
								 
					    	</div>
					  	</div>
						<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Serial</label>
					    	<div class="col-sm-10">
					      	
								 	<input type="text" class="form-control" id="serial" name="serial" placeholder="serial" >
								 
					    	</div>
					  	</div>
                                  
						<div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Asset Status</label>
					    	<div class="col-sm-10">
					      	
						<select class="form-control" name="active" id="active">
						<option value='yes' default>Active</option>
                                                <option value='no'>Not-in-Use</option>					
                                                </select>
								 
					    	</div>
					  	</div>
                                  <div class="form-group">
					    	<label for="mobile" class="col-sm-2 control-label">Remarks</label>
					    	<div class="col-sm-10">
					      	
						
						<textarea id="desc1" class="form-control" name="desc1" rows="4" cols="50" placeholder="">

</textarea>		 
					    	</div>
					  	</div>
						
			      </div>
			      <div class="modal-footer">
			        <button type="button" name="button-add" id="button-add" class="btn btn-default" data-dismiss="modal">Close</button>
			        <button type="submit" class="btn btn-primary">Save changes</button>
			      </div>
		      	</form>
		    </div>
		  </div>
		  		
		</div>

		<script src="https://code.jquery.com/jquery-2.2.0.min.js" type="text/javascript"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
		<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.10/js/jquery.dataTables.js"></script>
		<script type="text/javascript" language="javascript" class="init">
			$(document).ready(function() {
	
				// ATW
				if ( top.location.href != location.href ) top.location.href = location.href;

				// Initialize datatable
				$('#example').dataTable({
					"aProcessing": true,
					"aServerSide": true,
					"ajax": "form_asset.php?ajax"
				});

				// Save edited row
				$("#edit-form").on("submit", function(event) {
					event.preventDefault();
					$.post("form_asset.php?edit=" + $('#edit-id').val(), $(this).serialize(), function(data) {
						var obj = $.parseJSON(data);
						var tr = $('a[data-id="row-' + $('#edit-id').val() + '"]').parent().parent();
						$('td:eq(1)', tr).html(obj.asset_no);
						$('td:eq(2)', tr).html(obj.asset_desc);
						$('td:eq(3)', tr).html(obj.category);
						$('td:eq(4)', tr).html(obj.sub_category);
						$('td:eq(5)', tr).html(obj.amount);
						$('td:eq(6)', tr).html(obj.loc);
						$('td:eq(7)', tr).html(obj.sub_loc);
						$('td:eq(8)', tr).html(obj.make);
						$('td:eq(9)', tr).html(obj.model);
						$('td:eq(10)', tr).html(obj.serial);
						$('td:eq(11)', tr).html(obj.asset_alias);
                                                $('td:eq(12)', tr).html(obj.unit);
                                                $('td:eq(13)', tr).html(obj.qty);
                                                $('td:eq(14)', tr).html(obj.inv_no);
                                                $('td:eq(15)', tr).html(obj.vendor_name);
						$('td:eq(16)', tr).html(obj.acq_dt);
                                          
                                                $('td:eq(17)', tr).html(obj.active);
                                                $('td:eq(18)', tr).html(obj.desc1);
                                                 $('td:eq(19)', tr).html(obj.comment);
                                                  $('#edit-modal').modal('hide');
						 $('#edit-form').trigger("reset");
					}).fail(function() { alert('Unable to save data, please try again later.'); });
				});
				
				

				// Add new row
				$("#add-form").on("submit", function(event) {
					event.preventDefault();
					$.post("form_asset.php?add", $(this).serialize(), function(data) {
						var obj = $.parseJSON(data);
						$('#example tbody tr:last').after('<tr role="row"><td class="sorting_1">' + obj.id + '</td><td>' + obj.asset_no + '</td><td>' + obj.asset_desc + '</td><td>' + obj.category + '</td><td>' + obj.sub_category + '</td><td>' + obj.amount + '</td><td>' + obj.loc+'</td><td>'+ obj.sub_loc+'</td><td>'+ obj.make + '</td><td>'+ obj.model + '</td><td>' + obj.serial + '</td><td>'+ obj.asset_alias+'</td><td>'+ obj.unit +'</td><td>'+ obj.qty +'</td><td>'+ obj.inv_no +'</td><td>'+ obj.vendor_name +'</td><td>'+ obj.acq_dt +'</td><td>'+ obj.active +'</td><td>'+ obj.desc1  +'</td><td>'+ obj.comment+'</td><td><a data-id="row-' + obj.id + '" href="javascript:editRow(' + obj.id + ');" class=""fa fa-pencil-square-o"">edit</a>&nbsp;<a href="" class="fa fa-times">remove</a></td></tr>');
						$('#add-modal').modal('hide');
						 $('#add-form').trigger("reset");
					}).fail(function() { alert('Unable to save data, please try again later.'); });
				});

			});
			$('#button-add').click(function() {
   $('#add-modal').modal('hide');
});
			// Edit row
			function editRow(id) {
				if ( 'undefined' != typeof id ) {
					$.getJSON('form_asset.php?edit=' + id, function(obj) {
	
						$('#edit-id').val(obj.id);
						$('#asset_no').val(obj.asset_no);
						$('#asset_desc').val(obj.asset_desc);
						$('#category').val(obj.category);
						$('#sub_category').val(obj.sub_category);
						$('#amount').val(obj.amount);
						$('#loc').val(obj.loc);	
                                                $('#sub_loc').val(obj.sub_loc);	
						$('#make').val(obj.make);
						$('#model').val(obj.model);
						$('#serial').val(obj.serial);
						$('#asset_alias').val(obj.asset_alias);
                                                $('#unit').val(obj.unit);
                                                $('#qty').val(obj.qty);
                                                $('#inv_no').val(obj.inv_no);                                                
                                                $('#vendor_name').val(obj.vendor_name);
                                                $('#acq_dt').val(obj.acq_dt);
                                          
                                                $('#active').val(obj.active);
                                                $('#desc1').val(obj.desc1);
                                                  $('#comment').val(obj.comment);
						$('#edit-modal').modal('show')
                                                
					}).fail(function() { alert('Unable to fetch data, please try again later.') });
				} else alert('Unknown row id.');
			}

			// Remove row
			function removeRow(id) {
				if ( 'undefined' != typeof id ) {
					$.get('form_asset.php?remove=' + id, function() {
						$('a[data-id="row-' + id + '"]').parent().parent().remove();
					}).fail(function() { alert('Unable to fetch data, please try again later.') });
				} else alert('Unknown row id.');
			}
                        
/**/
    
function processForm() { 
        $.ajax( {
            type: 'POST',
            url: 'sub_cat.php',
            data: 'category='+ category,

            success: function(data) {
                $('#message').html(data);
            }
        } );
}
 var category=0;

$("#category option").click(function () {
   category = $(this).attr("value");
    console.log(category);
   
});



	$('.form_date').datetimepicker({
        language:  'en',
        weekStart: 1,
        todayBtn:  1,
		autoclose: 1,
		todayHighlight: 1,
		startView: 2,
		minView: 2,
		forceParse: 0
    });
</script>
		</script>
		</div>
		
	</body>
</html>