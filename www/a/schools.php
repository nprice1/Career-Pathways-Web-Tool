<?php
chdir("..");
include("inc.php");
include("states.inc.php");

ModuleInit('schools');



if( KeyInRequest('id') ) {

	if( PostRequest() ) {

		if( Request('delete') == 'delete' ) {

			$DB->Query("DELETE FROM color_schemes WHERE school_id=".intval($_REQUEST['id']));
                        $DB->Query("UPDATE drawings INNER JOIN drawing_main on drawings.parent_id=drawing_main.id SET deleted = 1 WHERE drawing_main.school_id=".intval($_REQUEST['id']));
			$DB->Query("UPDATE post_drawings INNER JOIN post_drawing_main on post_drawings.parent_id=post_drawing_main.id SET deleted = 1 WHERE post_drawing_main.school_id=".intval($_REQUEST['id']));
			$DB->Query("DELETE FROM schools WHERE id=".intval($_REQUEST['id']));

		} else {

			$content = Array( 'school_name' => $_REQUEST['school_name'],
							  'school_abbr' => $_REQUEST['school_abbr'],
							  'school_website' => $_REQUEST['school_website'],
							  'school_phone' => $_REQUEST['school_phone'],
							  'school_addr' => $_REQUEST['school_addr'],
							  'school_city' => $_REQUEST['school_city'],
							  'school_state' => $_REQUEST['school_state'],
							  'school_zip' => $_REQUEST['school_zip'],
							  'school_county' => $_REQUEST['school_county'],
							  'organization_type' => $_REQUEST['organization_type'],
							);

			$content['school_website'] = str_replace('http://','',$content['school_website']);
			if( substr($content['school_website'],-1) == '/' ) {
				$content['school_website'] = substr($content['school_website'],0,-1);
			}

			if( Request('id') ) {
				$DB->Update('schools',$content,$_REQUEST['id']);
				$school_id = $_REQUEST['id'];
			} else {
				$content['date_created'] = $DB->SQLDate();
				$school_id = $DB->Insert('schools', $content);

				// Insert default HS headers
				foreach( array('English', 'Math', 'Science', 'Social Studies', 'Electives', 'Career and Technical Courses', 'Employment') as $num=>$title )
				{
					$data = array();
					$data['school_id'] = $school_id;
					$data['title'] = $title;
					$data['num'] = $num;
					$DB->Insert('post_default_col', $data);
				}
			}

		}

		header("Location: ".$_SERVER['PHP_SELF']);

	} else {

		PrintHeader();
		ShowSchoolForm($_REQUEST['id']);
		PrintFooter();

	}

} else {

	PrintHeader();

	foreach( array('CC', 'HS', 'Other') as $type )
	{
		switch( $type )
		{
			case 'CC':
				$typetext = 'community college';
				break;
			case 'Other':
				$typetext = 'other organization';
				break;
			case 'HS':
				$typetext = 'high school';
				break;
		}
		echo '<a href="'.$_SERVER['PHP_SELF'].'?id&type=' . $type . '" class="edit"><img src="/common/silk/add.png" width="16" height="16">	add ' . $typetext . '</a>';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	echo '<br /><br />';

	foreach( array('CC', 'HS', 'Other') as $type )
	{
		switch( $type )
		{
			case 'CC':
				$header = 'Community Colleges';
				break;
			case 'Other':
				$header = 'Other Organizations';
				break;
			case 'HS':
				$header = 'High Schools';
				break;
		}

		$schools = $DB->MultiQuery('SELECT * FROM schools WHERE organization_type="' . $type . '" ORDER BY school_name');

		echo '<h3 style="margin-top:0;margin-bottom:0">' . $header . '</h3>';
		echo '<table style="margin-bottom:10px">';

		echo '<tr>';
			echo '<th width="30">&nbsp;</th>';
			echo '<th width="140">Abbr.</th>';
			echo '<th width="290">Organization Name</th>';
			echo '<th width="50">Users</th>';
			echo '<th width="70">Drawings</th>';
			if( $type != 'HS' ) echo '<th>Colors</th>';
		echo '</tr>';

		foreach( $schools as $num=>$s ) {

			echo '<tr class="row'.($num%2).'">';
				echo '<td><a href="'.$_SERVER['PHP_SELF'].'?id='.$s['id'].'" class="edit">edit</a></td>';
				echo '<td>'.$s['school_abbr'].'</td>';
				echo '<td>'.$s['school_name'].'</td>';

				$users = $DB->SingleQuery("SELECT COUNT(*) AS num FROM users WHERE school_id=".$s['id']." AND user_active=1");
				echo '<td>'.($users['num']==0?'&nbsp;':$users['num']).'</td>';

				//JGD: Added POST Drawing count to total Drawing count in Configure Organizations page
				$postDrawings = $DB->SingleQuery("SELECT COUNT(*) AS num FROM post_drawing_main WHERE school_id=".$s['id']."");
				$drawings = $DB->SingleQuery("SELECT COUNT(*) AS num FROM drawing_main WHERE school_id=".$s['id']."");
				$drawingsNum = $drawings['num'] + $postDrawings['num'];
				//JGD: replaced this line with whats shown below --> echo '<td>'.($drawings['num']==0?'&nbsp;':$drawings['num']).'</td>';

				echo '<td>'.($drawingsNum==0?'&nbsp;':$drawingsNum).'</td>';
				echo '<td>';

				if( $type != 'HS' )
				{
					$str = '';
					$colors = $DB->MultiQuery("SELECT * FROM color_schemes WHERE school_id=".$s['id']);
					foreach( $colors as $c ) {
						$str .= '<div title="#'.$c['hex'].'" style="background-color:#'.$c['hex'].'" class="school_color_box_mini"></div>';
					}
					$str .= '<div title="#FFFFFF" style="background-color:#FFFFFF" class="school_color_box_mini"></div>';
					$str .= '<div title="#333333" style="background-color:#333333" class="school_color_box_mini"></div>';
					echo $str;
					echo '</td>';
				}
			echo '</tr>';
		}

		echo '</table>';
	}

	PrintFooter();

}



function ShowSchoolForm($id="") {
global $DB, $STATES;

	$orgtypes['CC'] = 'Community College';
	$orgtypes['Other'] = 'Other Organization';
	$orgtypes['HS'] = 'High School';

	$school = $DB->LoadRecord('schools',$id);

	if( Request('type') ) $school['organization_type'] = Request('type');

?>
<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a><br>
<br>

<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
<table width="600">

	<tr>
		<td colspan="3"><hr></td>
	</tr>
	<tr>
		<td width="120" valign="top">Organization Type:</td>
		<td colspan="2"><?=
			GenerateSelectBox($orgtypes, 'organization_type', $school['organization_type'])
		?></td>
	</tr>
	<tr>
		<td valign="top">Abbreviation:</td>
		<td colspan="2" valign="top"><input type="text" name="school_abbr" value="<?= $school['school_abbr'] ?>" size="10"></td>
	</tr>
	<tr>
		<td>Organization Name:</td>
		<td colspan="2"><input type="text" name="school_name" value="<?= $school['school_name'] ?>" size="50"></td>
	</tr>
	<tr>
		<td>Website:</td>
		<td colspan="2"><input type="text" name="school_website" id="school_website" value="<?= $school['school_website'] ?>" size="50"></td>
	</tr>
	<tr>
		<td>Phone:</td>
		<td colspan="2"><input type="text" name="school_phone" id="school_phone" value="<?= $school['school_phone'] ?>" size="20"></td>
	</tr>
	<tr>
		<td>Address:</td>
		<td colspan="2"><input type="text" name="school_addr" id="school_addr" value="<?= $school['school_addr'] ?>" size="50"></td>
	</tr>
	<tr>
		<td>City:</td>
		<td colspan="2"><input type="text" name="school_city" id="school_city" value="<?= $school['school_city'] ?>" size="20"></td>
	</tr>
	<tr>
		<td>State:</td>
		<td colspan="2"><?php
			echo GenerateSelectBox($STATES,'school_state',l('school state abbr'));
		?></td>
	</tr>
	<tr>
		<td>Zip Code:</td>
		<td colspan="2"><input type="text" name="school_zip" id="school_zip" value="<?= $school['school_zip'] ?>" size="10"></td>
	</tr>
	<tr>
		<td>County:</td>
		<td colspan="2" id="county_container"><!-- This form select is inserted via javascript --></td>
	</tr>

	<tr>
		<td colspan="3"><hr></td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td>
			<input type="submit" value="Submit" class="submit">
			</td>
		<td align="right">
			<?php if( $id != "" ) { ?>
				Delete: <select name="delete"><option value="">-------</option><option value="delete">Delete</option></select>
			<?php } else { ?>
				&nbsp;
			<?php } ?>
		</td>
	</tr>
</table>
<input type="hidden" name="id" value="<?= $id ?>">
</form>

<script type="application/javascript">
	window.jQuery || document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"><\/script>');
</script>
<script>
	// Make saved values available in Javascript.
	var school_state = "<?= isset($school['school_state']) ? $school['school_state'] : '' ?>",
		school_county = "<?= isset($school['school_county']) ? $school['school_county'] : '' ?>";

	if(school_state === '' && school_county === '') {
		// On page load, if this school is NOT being edited,
		// load all counties for this state.
		load_counties();
	} else {
		// On page load, if this school is being edited,
		// load the correct (saved) state and county.
		// First, select the state that was saved.
		$('#school_state').val(school_state);

		// Then load counties for that state, and select the county that was saved.
		load_counties(function(){
			$('[name=school_county]').val(school_county);
		});
	}

	function load_counties(callback) {
		var state = $('#school_state').val();
		$.get('/a/counties.php?state='+state,function(response){
			$('#county_container').html(response);
			if(callback && typeof callback == 'function') {
				callback();
			}
		});
	}

	// ---- event bindings ----
	$('#school_state').change(function(){
		load_counties();
	});
</script>

<?php

}


?>
