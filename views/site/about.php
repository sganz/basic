<?php
use yii\helpers\Html;
use yii\web\view;
use yii\helpers\VarDumper;

/* @var $this yii\web\View */
$this->title = 'About';
$this->params['breadcrumbs'][] = $this->title;
?>

 <style> 
	.tree-panel { 
		font-size:14px; 
		border-style: inset;
		padding :10px;
		min-height:500px;
	}
	.edit-panel {
		font-size:14px; 
		border-style: inset;
		padding :10px;
		min-height:500px;
	}
</style>
   

<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        This is the About page. You may modify the following file to customize its content:
    </p>

<div class="row">
	<div class="tree-panel col-sm-8">

	<div id="treeview"></div>	
	

	<?= \yiidreamteam\jstree\JsTree::widget([
		'containerOptions' => [
			'class' => 'tree_demo',	// sets the the div's CLASS
			'id' => 'treeview',		// sets the div's ID
		],
		'jsOptions' => [
			'core' => [
				'check_callback' => true,	// needs to be true for DND
				'multiple' => false,	// allow multiple selections, set to false to allow only single select
				'data' => [
					'url' => \yii\helpers\Url::to(['site/tree', 'recipe_id'=>'1']),
				],

				'themes' => [
					'variant' => 'large',	// makes tree bigger
					 'stripes' => true,		// green bar effect like old computer paper
				],
			],
			'types' =>[
				'default' => ['icon' => 'glyphicon glyphicon-flash'],
				'parent' => ['icon' => 'glyphicon glyphicon-eye-open'],
				'leaf' => ['icon' => 'glyphicon glyphicon-leaf'],
				'root' => ['icon' => 'glyphicon glyphicon-folder-open']
			],
			'plugins' => ['dnd', 'types'],
		]
	])
	?>
		

	<br />
	<div>
		<button id="expand" class="btn btn-primary">Expand</button>
		<button id="contract" class="btn btn-primary">Contract</button>	
		<button id="add" class="btn btn-primary">Add</button>
		<button id="update" class="btn btn-primary">Update</button>
		<button id="remove" class="btn btn-danger">Remove</button> 
		<button id="move" class="btn btn-danger">Move</button> 
	</div>
	</div> <!-- tree-panel-->
	
	<div class="edit-panel col-sm-4">
	<form class="form-horizontal">

	<fieldset>

	<!-- Form Name -->
	<legend>Edit Data Here</legend>

	<div class="control-group">
	  <label class="control-label" for="textinput">Name</label>
	  <div class="controls">
		<input name="name" placeholder="" class="input-xlarge" type="text">
	  </div>
	</div>

	<div class="control-group">
	  <label class="control-label" for="textinput">Weight</label>
	  <div class="controls">
		<input name="weight" placeholder="" class="input-xlarge" type="text">
	  </div>
	</div>

	<div class="control-group">
	  <label class="control-label" for="textinput">Spec Id</label>
	  <div class="controls">
		<input name="spec_id" placeholder="" class="input-xlarge" type="text">
	  </div>
	</div>

	<div class="control-group">
	  <label class="control-label" for="textinput">Order</label>
	  <div class="controls">
		<input name="order" placeholder="" class="input-xlarge" type="text">
	  </div>
	</div>

	<div class="control-group">
	  <label class="control-label" for="textinput">Min</label>
	  <div class="controls">
		<input name="min" placeholder="" class="input-xlarge" type="text">
	  </div>
	</div>
		<div class="control-group">
	  <label class="control-label" for="textinput">Max</label>
	  <div class="controls">
		<input name="max" placeholder="" class="input-xlarge" type="text">
	  </div>
	</div>
	<br />

	<button id="clear" class="btn btn-success">clear</button> 
	
	</fieldset>
	</form>
	<div id="ajax_node_type"></div>
	<div id="ajax_json_type"></div>
	<div id="ajax_node_id"></div>
	<div id="ajax_parent_id"></div>
	<div id="ajax_status_msg"></div>
	</div> <!-- edit-panel-->
</div> <!-- row-->

<?php
// this block will be how you put in JS into YII
// check out <<< and other heredoc in 'string' options for stuffing data into a php var
// NOTE that this should be placed at POS_END as most of the JQuery and
// other javascript by default are loaded at the end in YII. Inline
// <script> tags that reference JQuery may not work since JQuery is loaded
// last.
$script = <<< JS


$(document).ready(function() {
	
	// do some set up to get initial state of buttons, etc
	
	$("#remove").prop("disabled",true);
	$("#update").prop("disabled",true);
	$("#add").prop("disabled",true);
	$("#move").prop("disabled",true);
});

// check for integer only number
function isIntNum(str)
{
	var intregx = /(^([-]?[0-9]+))$/;  
	return intregx.test(str);
}

// see if a valid float
function isFloatNum(str)   
{   
	var decimal = /(^([-]?\.?[0-9]+)|^([-]?[0-9]+\.[0-9]+)|^([-]?[0-9]+))$/;  
	return decimal.test(str);
}  

function clearEdits()
{
	$("input[name=name]").val('');			
	$("input[name=weight]").val('');			
	$("input[name=spec_id]").val('');			
	$("input[name=order]").val('');			
	$("input[name=min]").val('');			
	$("input[name=max]").val('');			
}

$('#clear').on('click',function(event)
{
	event.preventDefault(); 
	clearEdits();
});

$('#expand').on('click',function(event)
{
	event.preventDefault(); 
	$('#treeview').jstree('open_all');
});

$('#contract').on('click',function(event)
{
	event.preventDefault(); 
	$('#treeview').jstree('close_all');
	$('#treeview').jstree('deselect_all');
	
	$("#remove").prop("disabled",true);
	$("#update").prop("disabled",true);
});

// Add new node to the tree. Calls remote functions, tree refreshed
// after add so no changes made to display unless successful
$('#add').on('click',function(event)
{
	event.preventDefault(); 

	selected = $('#treeview').jstree('get_selected');	// implies single selection mode in tree
	
	if(selected.length == 0)
	{
		alert('Nothing Selected, can not add!');
		return;
	}
	
	alert('Adding Node to Parent ' + selected[0]);

	name = $("input[name=name]").val();
	weight = $("input[name=weight]").val();
	spec_id = $("input[name=spec_id]").val();
	order = $("input[name=order]").val();
	min = $("input[name=min]").val();
	max = $("input[name=max]").val();
	
	name = name.trim(); // if your browser doesn't have this get a new browswer

	if(name.length == 0)
	{
		alert('Error, name Can\'t be empty');
		return;
	}
	
	// validate numbers
	if(!isIntNum(weight))
	{
		alert('Invalid Weight, must be integer');
		return;
	}
	
	if(!isIntNum(spec_id))
	{
		alert('Invalid Spec Id, must be integer');
		return;
	}
	
	if(!isIntNum(order))
	{
		alert('Invalid Order, must be integer');
		return;
	}

	if(!isFloatNum(min))
	{
		alert('Invalid Min, must be numeric');
		return;
	}
	
	if(!isFloatNum(max))
	{
		alert('Invalid Max, must be numeric');
		return;
	}

	if(parseFloat(min) > parseFloat(max))
	{
		alert('Invalid min/max range');
		return;
	}

	// that parent id is enough to get the
	// recipe id, all parms needed
	
	$.ajax({
		url: 'http://localhost/basic/web/index.php?r=site/add-node',	// must match URL format for Yii, will be different if 'friendlyURL' is enabled
		type: 'post',
		data: {
			parent_id : selected[0], 	// this is the parent of the new node. 
			spec_id   : spec_id,
			name      : name,
			weight	  : weight,
			order	  : order,
			min		  : min,
			max		  : max
		},

		success: function (data) {
			node = data.data;

			switch(data.status)
			{
				case 0:  break;	// no error
				case 5:  alert('Application Error ' + data.msg + ', Node Id : ' + node.node_id); return;
				default: alert('Unknown Application Error ' + data.msg); return;
			}
		
			$('#treeview').jstree('refresh');	// once added get the new data
		},
		
		error:	function(data) {
			alert('Http Response : ' + data.responseText + ' Operation Failed');
			// turn tree red, this is where communication failed or invalid
			// data to the ajax call was sent.
		},
		
	});		
});

$('#update').on('click',function(event)
{
	event.preventDefault(); 

	selected = $('#treeview').jstree('get_selected');	// implies single selection
	
	if(selected.length == 0)
	{
		alert('Nothing Selected, can not update!');
		return;
	}
	
	alert('Update Node ' + selected[0]);
	
	// that parent id is enough to get the
	// recipe id, all parms needed

	name = $("input[name=name]").val();
	weight = $("input[name=weight]").val();
	spec_id = $("input[name=spec_id]").val();
	order = $("input[name=order]").val();
	min = $("input[name=min]").val();
	max = $("input[name=max]").val();

	name = name.trim(); // if your browser doesn't have this get a new browswer

	if(name.length == 0)
	{
		alert('Error, name Can\'t be empty');
		return;
	}
	
	// validate numbers
	if(!isIntNum(weight))
	{
		alert('Invalid Weight, must be integer');
		return;
	}
	
	if(!isIntNum(spec_id))
	{
		alert('Invalid Spec Id, must be integer');
		return;
	}
	
	if(!isIntNum(order))
	{
		alert('Invalid Order, must be integer');
		return;
	}

	if(!isFloatNum(min))
	{
		alert('Invalid Min, must be numeric');
		return;
	}
	
	if(!isFloatNum(max))
	{
		alert('Invalid Max, must be numeric');
		return;
	}

	if(parseFloat(min) > parseFloat(max))
	{
		alert('Invalid min/max range');
		return;
	}

	
	$.ajax({
		url: 'http://localhost/basic/web/index.php?r=site/update-node',	// must match URL format for Yii, will be different if 'friendlyURL' is enabled
		type: 'post',
		data: {
			node_id   : selected[0], 	// this is the node we want to update
			spec_id   : spec_id,
			name      : name,
			weight	  : weight,
			order	  : order,
			min		  : min,
			max		  : max
		},
		success: function (data) {
			node = data.data;

			switch(data.status)
			{
				case 0:  break;	// no error
				case 3:
				case 4:  alert('Application Error ' + data.msg + ', Node Id : ' + node.node_id); return;
				default: alert('Unknown Application Error ' + data.msg); return;
			}
		
			$('#treeview').jstree('refresh');
		},
		
		error:	function(data) {
			alert('Http Response : ' + data.responseText + ' Operation Failed');
			// turn tree red, this is where communication failed or invalid
			// data to the ajax call was sent.
		},

	});		
});

$('#remove').on('click',function(event)
{
	event.preventDefault(); 
		
	selected = $('#treeview').jstree('get_selected');	// implies single selection mode
	
	if(selected.length == 0)
	{
		alert('Nothing Selected');
		return;
	}
	
	// alert('Removing Node ID (and children) ' + selected[0]);
	
	$.ajax({
		url: 'http://localhost/basic/web/index.php?r=site/remove-node',	// must match URL format for Yii, will be different if 'friendlyURL' is enabled
		type: 'post',
		data: {
			node_id : selected[0]
		},
		
		success: function (data) {
						
			node = data.data;

			switch(data.status)
			{
				case 0:  break;	// no error
				case 1:
				case 2:  alert('Application Error ' + data.msg + ',  Node Id : ' + node.node_id); return;
				default: alert('Unknown Application Error ' + data.msg); return;
			}
			
			alert('Deleted ' + node.node_cnt + ' Nodes from the tree');
			
			$('#treeview').jstree('refresh');
			clearEdits();
		},
		
		error:	function(data) {
			alert('Http Response : ' + data.responseText + ' Operation Failed');
			// turn tree red, this is where communication failed or invalid
			// data to the ajax call was sent.
		},
		
	});		
});

// capture things in the tree when a click happens
// mainly the single selected (by config options) node

$('#treeview').on('changed.jstree', function (e, data) 	{

	// if we have nothing selected we can't do much, maybe some house keeping
	if(data.selected.length == 0)
	{
		// Nothing selected, clear fields, form display, etc

		$("#remove").prop("disabled",true);	// can remove if something selected
		$("#update").prop("disabled",true);
		$("#add").prop("disabled",true);
		$("#move").prop("disabled",true);

		return;
	}
	
	// console.log(data.node.type); // show intern 'type' of node as set by JSON on load
	
	var json_node_type = data.node.type; // the type coming in from inital load

	// something selected now, enable some buttons
	
	$("#remove").prop("disabled",false);
	$("#update").prop("disabled",false);
	$("#move").prop("disabled",false);
		
	// call the ajax function that gets a node.
	
	$.ajax({
		url:	'http://localhost/basic/web/index.php?r=site/node',	// match URL format for Yii, will be different if 'friendlyURL' is enabled
		type: 	'post',

		data: {		// data sent in post params
					node_id : data.selected[0]	// the node id selected
		},
	   
		error:	function(data) {
			alert('Http Response : ' + data.responseText + ' Operation Failed');
			// turn tree red, this is where communication failed or invalid
			// data to the ajax call was sent.
		},
		   
		success: function (data) {

			// NON ZERO is an error, display and get out
			
			node = data.data;

			if(data.status)
			{
				// specific data for this error, ie, node_id may not exist for other status
				alert('Application Error ' + data.msg + ' Node Id : ' + node.node_id);
				return;
			}
		
			// debug display
			$('#ajax_node_type').html('Node Type   : ' + node.node_type);
			$('#ajax_json_type').html('JSON Type   : ' + json_node_type);
			$('#ajax_node_id').html('Node Id   : ' + node.id);
			$('#ajax_parent_id').html('Parent Id : ' + node.parent_id);
			$('#ajax_status').html('Status : ' + node.status);
			$('#ajax_status_msg').html('Status Message : ' + node.msg);
			  
			// these should both be 0, null is not good for the UI

			if(node.min === null)
				node.min = '0';

			if(node['max'] === null)
				node.max = '0';

			// try stuffing some data to input fields

			$("input[name=name]").val(node.name);			
			$("input[name=weight]").val(node.weight);			
			$("input[name=spec_id]").val(node.spec_id);			
			$("input[name=order]").val(node.order);			
			$("input[name=min]").val(node.min);			
			$("input[name=max]").val(node.max);			

			// mess with some button states based on node type
			// also if the node is 9999 might want to disable 
			// some fields or other UI indicators

			if(node['spec_id'] == 9999)
			{
				$("#add").prop("disabled",false);
			}
			else
			{
				$("#add").prop("disabled",true);
			}
		}
	});		
});

// expand the tree by default on open

$('#treeview').on('loaded.jstree', function (event, data) {
	$(this).jstree("open_all");
	clearEdits();
});	

$('#treeview').on("move_node.jstree", function (e, data) {
   //data.node, data.parent, data.old_parent is what you need

   //console.log(data);
   alert('Moving Node Id : ' + data.node.id + ' To Node Id : ' + data.parent);
   
	$('#treeview').jstree().settings.core.themes.stripes = false;
	$('#treeview').jstree('refresh');	// once added get the new data
  
});

JS;
$this->registerJs($script, view::POS_END);
?>
