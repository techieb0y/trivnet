<?php 

$raceid=279;
// require_once("include/head.inc");

$id=0;

if ( isset($_GET["str"]) ) {
	$str = $_GET["str"];
} else {
	$str = "1=&5=&8=&9=&10=&11=jon&12=&type=AND&0=";
} // end if
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>TrivnetDB - Amateur Radio Information Network</title>
	<link rel="stylesheet" href="common/trivnet.css" />
	<script src="js/angular.min.js"></script>
	<!--script src="js/jquery.js"--><!--/script-->
	<!--script src="js/jquery-ui-1.8.24.custom.min.js"--><!--/script-->
	<!-- script src="js/jquery.mobile-1.2.0.min.js"--><!--/script-->

	<script>
		function detailCtrl($scope,$http) {
			$http.get("/agents/search.php?<?php echo $str; ?>").success(function(response) { $scope.names = response;});
		}
	</script>
</head>

<body>

<div ng-app="" ng-controller="detailCtrl">
<table width="100%">

	<tr ng-repeat="x in names">
		<td>{{ x.bibnum }}</td>
		<td>{{ x.race }}</td>
		<td>{{ x.firstname }}</td>
		<td>{{ x.lastname }}</td>
		<td>{{ x.sex }}</td>
	</tr>
</table>
</div>
</body></html>
