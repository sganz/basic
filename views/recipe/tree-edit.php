<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\helpers\Url;
use yii\web\view;
use yii\web\JsExpression;

use yiidreamteam\jstree\JsTree;
use raoul2000\widget\pnotify\PNotifyAsset;
use yii2mod\alert\AlertAsset;
use kartik\select2\Select2;
use kartik\select2\ThemeDefaultAsset;

$this->title = 'Recipe Editor';
$this->params['breadcrumbs'][] = $this->title;

PNotifyAsset::register($this);	// load js for PNotify
AlertAsset::register($this); 	// load for sweetalert
ThemeDefaultAsset::register($this);	// load this so that we can register another CSS for use AFTER this

// Register the tree-edit css, this has to be done after the select2 theme is loaded, and in this case
// we are using the default theme for the component. This all breaks if the theme is changed and
// that asset must be manually loaded for this to keep the tree css file at the end of the list
// need something like an option to say put to last...

$this->registerCssFile('@web/css/tree-edit.css', ['depends'=>['kartik\select2\Select2Asset']]);

//var_dump($this->getAssetManager()->bundles);
//var_dump(\kartik\select2\ThemeDefaultAsset::className());

?>	

<div class="recipe-tree-edit">
	<h1><?= Html::img('@web/images/recipe.png')?>&nbsp<?= Html::encode($this->title) ?></h1>
	<div class="row">
		<div class="recipe_select_panel col-sm-12">
			<?php
				$recipes = $this->context->getRecipes();
				echo Html::dropDownList('recipe_list', '', $recipes, 
								['id'=>'recipe_list',
								'prompt' => '--Select Recipe--',
								]); 
			?>
			<button id="new-recipe" class="btn btn-primary">New</button>
			<button id="edit-recipe" class="btn btn-primary">Edit</button>
			<button id="copy-recipe" class="btn btn-primary">Copy</button>
			<button id="delete-recipe" class="btn btn-danger">Delete</button>
			<div class="version-number pull-right">v1.21</div>
		</div>
	</div>
	
	<div class="row">
		<div id="recipe_add_panel" class="recipe_add_panel col-sm-12">
			<div class="form-inline">
			  <div class="form-group">
				<label for="recipe_name">Name</label>
				<input type="text" class="form-control" id="recipe_name" placeholder="Enter Name">
			  </div>
			  <div class="form-group">
				<label for="recipe_description">Description</label>
				<input type="text" class="form-control" id="recipe_description" placeholder="Enter Description">
			  </div>
			  <div class="form-group">
				<label for="recipe_author">Author</label>
				<input type="text" class="form-control" id="recipe_author" placeholder="Enter Author">
			  </div>
			  <button id="save-recipe" class="btn btn-primary">Save</button>
			  <button id="cancel-recipe" class="btn btn-warning">Cancel</button>
			</div>				
		</div>
	</div>	
	
	<div id="recipe-tree" class="row">
	<div class="tree-panel col-sm-7">

	<?= JsTree::widget([
		'containerOptions' => [
			'class' => 'jstreeview',	// sets the the div's CLASS
			'id' => 'treeview',			// sets the div's ID
		],
		'jsOptions' => [
			'core' => [
			'check_callback' => new JsExpression('function (op, node, parent, position, more)
					{
						if(more && more.dnd)
						{
							// only allow drops on leafs if NOT on the direct leaf ("i")
							if(more.pos == "i" && more.ref.type == "leaf")	// i=in, a=above, b=below
								return false;	// no drop on a node
						}
						return true; // all other cases not specific to dnd
					}'),

				'multiple' => false,		// allow multiple selections, set to false to allow only single select

				'themes' => [
					'variant' => 'large',	// makes tree bigger
					 'stripes' => true,		// green bar effect like old computer paper
				],
			],
			'types' =>[
				'default' =>['icon' => 'glyphicon glyphicon-flash'],		// use yii URL::to() to make safe
				'parent' => ['icon' => URL::to('@web/images/gear.png')], 	// ['icon' => 'glyphicon glyphicon-eye-open'],
				'leaf' => ['icon' => URL::to('@web/images/tools.png')],		// ['icon' => 'glyphicon glyphicon-leaf'],
				'root' => ['icon' => URL::to('@web/images/recipe.png')]		// ['icon' => 'glyphicon glyphicon-folder-open']
			],
			'plugins' => ['dnd', 'types', 'contextmenu'],
			'dnd' => ['check_while_dragging' => true],
			
			// for context menu you need to put into the component non quoted JS, so
			// you must use the JsExpression, it does the rest.
			'contextmenu' => [
				'items' => new JsExpression('function ($node) {
                return {
                    "Expand": {
                        "label": "Expand Tree",
                        "action": function (obj) {
                            this.ExpandTree(obj);
                        }
                    },
                    "Contract": {
                        "label": "Contract Tree",
                        "action": function (obj) {
                            this.ContractTree(obj);
                        }
                    },
                };
            }'),	// JsExpression
				
			],
		]
	])
	?>
	<br />
	<div>
	</div>
	</div> <!-- tree-panel-->
	
	<div class="edit-panel col-sm-5">
		<form class="form-horizontal"> 
		<fieldset>
		<legend id="edit-state">Edit Data Here</legend>

		<?= Html::img('@web/images/recipe.png', ['class' => 'state_icons', 'id' => 'image-state-recipe']) ?>
		<?= Html::img('@web/images/gear.png', ['class' => 'state_icons', 'id' => 'image-state-parent']) ?>
		<?= Html::img('@web/images/tools.png', ['class' => 'state_icons', 'id' => 'image-state-leaf']) ?>
		
		<div class="control-group">
		  <label class="control-label" for="textinput">Name</label>
		  <div class="controls">
			<input name="name" placeholder="" class="input-xlarge" type="text"  size="32">
		  </div>
		</div>

		<div class="control-group">
		  <label class="control-label" for="textinput">Weight</label>
		  <div class="controls">
			  
			<?php
				$weights = $this->context->getWeights();
						?>  
			<?= Html::dropDownList('weight-list', '0', ArrayHelper::map($weights, 'id', 'weight'), 
									['id'=>'weight-list', 
									]); 
			?>
		  </div>
		</div>
		<div class="control-group">
		  <label class="control-label" for="textinput">Specification</label>
		  <div class="controls">
			<?php
				$data = $this->context->getSpecsByCat();
				 echo Select2::widget([
						'name' => 'spec-list',
						'id' => 'spec-list',
						'data' => $data,
						'size' => 'sm',
						'theme' => Select2::THEME_DEFAULT,	// if this is changes manually load the asset bundle for the theme!
						'options' => [
							'placeholder' => 'Type for Search, or Select From List',
						],
						'pluginOptions' => [
								'allowClear' => true,
							],
					]);
			?>
		  </div>
		</div>
		
		<div class="control-group">
		  <label class="control-label" for="textinput">Order</label>
		  <div class="controls">
			<input name="order" placeholder="" class="input-xlarge" type="text" size="8">
		  </div>
		</div>

		<div class="control-group no_disp_parent">
		  <label class="control-label" for="textinput">Min</label>
		  <div class="controls">
			<input name="min" placeholder="" class="input-xlarge" type="text" size="8">
		  </div>
		</div>
		
		<div class="control-group no_disp_parent">
		  <label class="control-label" for="textinput">Max</label>
		  <div class="controls">
			<input name="max" placeholder="" class="input-xlarge" type="text" size="8">
		  </div>
		</div>
		<br />

		<button id="new-leaf" class="btn btn-primary">New Leaf</button>
		<button id="new-parent" class="btn btn-primary">New Parent</button>
		<button id="edit" class="btn btn-primary">Edit</button>
		<button id="save" class="btn btn-success">Save</button> 
		<button id="remove" class="btn btn-danger">Remove</button> 
		<button id="cancel" class="btn btn-warning">Cancel</button> 
		
		</fieldset>
	</form>
	</div> <!-- edit-panel-->
</div> <!-- row-->

<?php
// this block will be how you put in JS into YII
// check out <<< and other heredoc in 'string' options for stuffing data into a php var
// NOTE that this should be placed at POS_END as most of the JQuery and
// other javascript by default are loaded at the end in YII. Inline
// <script> tags that reference JQuery may not work since JQuery is loaded
// last.

// Generated URL's for each action's Ajax Call
$ajax_url['node'] = Url::to(['recipe/get-node']);
$ajax_url['add'] = Url::to(['recipe/add-node']);
$ajax_url['remove'] = Url::to(['recipe/remove-node']);
$ajax_url['update'] = Url::to(['recipe/update-node']);
$ajax_url['move'] = Url::to(['recipe/move-node']);
$ajax_url['add-recipe'] = Url::to(['recipe/add-recipe']);
$ajax_url['get-recipe']= Url::to(['recipe/get-recipe']);
$ajax_url['update-recipe']= Url::to(['recipe/update-recipe']);
$ajax_url['get-recipes']= Url::to(['recipe/get-recipes']);
$ajax_url['delete-recipe']= Url::to(['recipe/delete-recipe']);
$ajax_url['copy-recipe']= Url::to(['recipe/copy-recipe']); 

$ajax_url['tree'] = Url::to(['recipe/tree']); // this is not a post, append the tree ID to the end of this URL '$recipe_id=1234'

$script = <<< JS

var gEditState = 'inactive';	// edit state new, update, browse
var gEditType = 'inactive';		// current edit type, leaf or other 
var gCurrParent = -1; 			// invalid for start
var gCurrNode = -1; 			// invalid for start
var gCurrType = 'invalid';		// invalid for start
var gCurrRecipe = -1;			// invalid for start
var gCurrRecipeState = 'inactive'; // current edit state inactive, new or update
var gAlert = false; 

$(document).ready(function() {
	
	// do some set up to get initial state of buttons, etc

	$("#recipe_list").val(""); // set to the prompt

	clearEdits();
	setEditState('inactive');
	$("#recipe_add_panel").hide();	// hide the recipe panel
	clearRecipeEdits();
	setRecipeState('inactive');
	
	// set up inital state of recipe buttons
	$("#edit-recipe").prop("disabled",true);
	$("#copy-recipe").prop("disabled",true);
	$("#delete-recipe").prop("disabled",true);
	
	consume_alert();
});

function consume_alert() {
    PNotify.prototype.options.delay = 4000;

	// capture window alert and use the pnotify defined below
    if (gAlert) return;
    gAlert = window.alert;	// save it incase we want to restore later
    window.alert = function(message) {
        new PNotify({
            title: 'Alert',
            text: message,
        });
    };
}
    
// expand the tree by default on inital page open

$('#treeview').on('loaded.jstree', function (event, data) {
	$(this).jstree("open_all");
});	

// expand the tree on any refresh due to a reload by url change
$('#treeview').bind('refresh.jstree', function(e, data) {
    // invoked after jstree has loaded
    $(this).jstree("open_all");
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
	$("input[name=order]").val('');			
	$("input[name=min]").val('');			
	$("input[name=max]").val('');		
	$("#spec-list").val('9999').trigger("change");
	$("#weight-list").val('');	
}

function clearRecipeEdits()
{
	$("#recipe_name").val('');	
	$("#recipe_author").val('');
	$("#recipe_description").val('');
}

// show either full edits for leaf or subset for parent/root
function setEditFields(type)
{
	if(type == 'leaf')
		$(".no_disp_parent").show();			
	else
		$(".no_disp_parent").hide();	// root or parent nodes here
	
	gEditType = type;
	setEditIconState(type);
}

function setEditReadOnly(state)
{
	//input fields
	$("input[name=name]").prop('readonly', state);
	$("input[name=order]").prop('readonly', state);
	$("input[name=min]").prop('readonly', state);
	$("input[name=max]").prop('readonly', state);
	
	// drop down lists
	$("#spec-list").attr("disabled", state); 
	$("#weight-list").attr("disabled", state); 
}

function setEditIconState(node_type)
{
	if(node_type == 'leaf')
	{
		$('#image-state-leaf').show();
		$('#image-state-recipe').hide();
		$('#image-state-parent').hide();
	}
	else
		if(node_type == 'parent')
		{
			$('#image-state-parent').show();
			$('#image-state-recipe').hide();
			$('#image-state-leaf').hide();
		}
		else
		{
			$('#image-state-recipe').show();
			$('#image-state-parent').hide();
			$('#image-state-leaf').hide();
		}
}

// set state and controls for recipe related changes
// not specifically node state or changes.
function setRecipeState(new_state)
{
	if(new_state == 'new')
		gCurrRecipeState = 'new';
	else
		if(new_state == 'update')
			gCurrRecipeState = 'update';
		else
			gCurrRecipeState = 'inactive';
			
	setRecipeControlState(gCurrRecipeState);
}

// used to set state of the interface
function setEditState(new_state)
{
	if(new_state == 'new')
		gEditState = 'new';
	else
		if(new_state == 'update')
			gEditState = 'update';
		else
			if(new_state == 'browse')
				gEditState = 'browse';	// all other cases to browse
			else
				gEditState = 'inactive';	// new load or unknown state
		
		setControlState(gEditState);		// update button to new state
}

// sets the recipe buttons abd list state 
function setRecipeControlState(state)
{
	if(state == 'inactive') // inactive means show
	{
		// show list and buttons
		$("#new-recipe").prop("disabled",false);
		$("#edit-recipe").prop("disabled",false);
		$("#copy-recipe").prop("disabled",false);
		$("#delete-recipe").prop("disabled",false);
		$("#recipe_list").prop("disabled",false);
	}
	else
	{
		$("#new-recipe").prop("disabled",true);
		$("#edit-recipe").prop("disabled",true);
		$("#copy-recipe").prop("disabled",true);
		$("#delete-recipe").prop("disabled",true);
		$("#recipe_list").prop("disabled",true);
	}
}

// set all control state based on some globals and current state
function setControlState(state)
{
	if(state == 'new')
	{
		$("#edit-state").html("Add Mode");
		
		setEditReadOnly(false);
		
		$("#new-leaf").prop("disabled", true);
		$("#new-leaf").hide();
		$("#new-parent").prop("disabled", true);
		$("#new-parent").hide();

		setRecipeControlState(state);

		$("#edit").prop("disabled",true);
		$("#edit").hide();
		$("#remove").prop("disabled",true);
		$("#remove").hide();
		$("#save").prop("disabled",false);
		$("#save").show();
		$("#cancel").prop("disabled",false);
		$("#cancel").show();
	}
	else
		if(state == 'update')
		{
			$("#edit-state").html("Edit Mode");
			
			setEditReadOnly(false);

			$("#new-leaf").prop("disabled", true);
			$("#new-leaf").hide();
			$("#new-parent").prop("disabled", true);
			$("#new-parent").hide();

			setRecipeControlState(state);

			$("#edit").prop("disabled",true);
			$("#edit").hide();
			$("#remove").prop("disabled",true);
			$("#remove").hide();
			$("#save").prop("disabled",false);
			$("#save").show();
			$("#cancel").prop("disabled",false);
			$("#cancel").show();
		}
		else 
			if(state == 'browse') // browse mode
			{
				setEditReadOnly(true);
				$("#edit-state").html("Browse Mode");
				$("#remove").show();

				if(gCurrType == 'root')
				{
					$("#remove").prop("disabled",true);
					$("#edit").prop("disabled",true);
				}
				else
				{
					$("#remove").prop("disabled",false);
					$("#edit").prop("disabled",false);
				}
				
				// if node is a leaf, can't add anything to it

				if(gCurrType == 'parent' || gCurrType == 'root')
				{
					$("#new-leaf").prop("disabled", false);
					$("#new-parent").prop("disabled", false);
				}
				else
				{
					$("#new-leaf").prop("disabled", true);
					$("#new-parent").prop("disabled", true);
				}

				$("#new-leaf").show();
				$("#new-parent").show();

				setRecipeControlState('inactive'); 

				$("#edit").show();
				$("#save").prop("disabled",true);
				$("#save").hide();
				$("#cancel").prop("disabled",true);
				$("#cancel").hide();
			}
			else
			{
				setEditReadOnly(true);
				$("#edit-state").html("Please Select Node or Load Recipe");

				$("#remove").hide();
				$("#new-leaf").hide();
				$("#new-parent").hide();

				setRecipeControlState('inactive'); // show buttons

				$("#remove").hide();
				$("#save").hide();
				$("#cancel").hide();
				$("#edit").hide();
			}
}

// set the tree to the new recipe id and refreshes (loads)
function updateTreeURL(id)
{
	var url = '{$ajax_url['tree']}' + '?recipe_id=' + id;
	$('#treeview').jstree(true).settings.core.data = {'url' : url}; 	
	$('#treeview').jstree(true).refresh();
}

function invalidateTree()
{
	$('#treeview').jstree(true).settings.core.data = ''; 	
	$('#treeview').jstree(true).refresh();
	id = -1;
	setRecipeState('inactive');
	setEditState('inactive');
}

$('#recipe_list').on('change', function(event)
{
	var id = $("#recipe_list").val();
	
	if(id == "")
	{
		invalidateTree();

		// reset the state keep new button only active
		$("#new-recipe").prop("disabled",false);
		$("#edit-recipe").prop("disabled",true);
		$("#copy-recipe").prop("disabled",true);
		$("#delete-recipe").prop("disabled",true);

		return;
	}

	gCurrRecipe = id;	// save the current always!
	setRecipeState('inactive');
	updateTreeURL(id);
});

function ExpandTree(obj)
{
	$('#treeview').jstree('open_all');
}

function ContractTree(obj)
{
	$('#treeview').jstree('close_all');
	$('#treeview').jstree('deselect_all');
}

// given a tree id, get the json object for it
function getNodeById(id)
{
	return $('#treeview').jstree(true).get_node(id);
}

function showRecipeEdit(show)
{
	if(show)
		$("#recipe_add_panel").show();
	else
		$("#recipe_add_panel").hide();
}

function showTreeEdit(show)
{
	if(show)
		$("#recipe-tree").show();
	else
		$("#recipe-tree").hide();
}

// event handlers for buttons

$('#new-recipe').on('click',function(event)
{
	setRecipeState('new');
	showRecipeEdit(true);
	showTreeEdit(false);
});

$('#edit-recipe').on('click',function(event)
{
	id = $("#recipe_list").val();
	
	if(id < 1 || id == "")
	{
		alert('Please Select a Recipe to Edit');
		setRecipeState('inactive');
		return;
	}

	// refresh with the new tree if successful
	
	setRecipeState('update');
	showRecipeEdit(true);
	getRecipe(id);
	showTreeEdit(false);
});

$('#copy-recipe').on('click',function(event)
{
	id = $("#recipe_list").val();
	
	if(id < 1 || id == "")
	{
		alert('Please Select a Recipe from the list');
		setRecipeState('inactive');
		return;
	}

	swal({
	  title: "Are You Sure?", 
	  text: "Are you sure you want to create a copying of this Recipe?",
	  type: "warning",
	  showCancelButton: true,
	  confirmButtonText: "OK",
	  cancelButtonText: "Cancel",
	  closeOnConfirm: false,
	  closeOnCancel: false
	},
	function(isConfirm)
	{
		if(isConfirm) 
		{
			// Do all calls on confirm here, ie copyRecipe() refresh to new
			// recipe, etc

			copyRecipe(id);
			getRecipe(id);
			showTreeEdit(true);
			swal("Recipe Copied", "Completed", "success");
		} 
		else 
			swal("Cancelled", "Operation Cancelled", "warning");
	});
});

$('#delete-recipe').on('click',function(event)
{
	id = $("#recipe_list").val();
	
	if(id < 1 || id == "")
	{
		alert('Please Select a Recipe from the list to Delete');
		setRecipeState('inactive');
		return;
	}

	swal({
	  title: "Are You Sure?", 
	  text: "Are you sure you want to DELETE this Recipe?",
	  type: "warning",
	  showCancelButton: true,
	  confirmButtonText: "OK",
	  cancelButtonText: "Cancel",
	  closeOnConfirm: false,
	  closeOnCancel: false
	},
	function(isConfirm)
	{
		if(isConfirm) 
		{
			deleteRecipe(id);
			swal("Recipe Deleted", "Completed", "success");
		} 
		else 
			swal("Cancelled", "Operation Cancelled", "warning");
	});
});

$('#save-recipe').on('click',function(event)
{
	if(gCurrRecipeState == 'new')
		addRecipe();
	else
		if(gCurrRecipeState == 'update')
			updateRecipe();	 // gCurrRecipe has the active ID
		else
			alert('Unknown Recipe state, reload page');
		
	// state of controls set in resulting call to add/updateRecipe() if
	// and ONLY if a successful operation went down. otherwise
	// state will remain in the new/update state which keeps buttons
	// and dropdown disabled. Cancel or successful save resets
});

$('#cancel-recipe').on('click',function(event)
{
	alert('Changes Discarded');
	showRecipeEdit(false);
	clearRecipeEdits();
	showTreeEdit(true);
	setRecipeState('inactive'); // if here reset
});

$('#new-leaf').on('click',function(event)
{
	event.preventDefault(); 

	if(gCurrType == 'leaf')
	{
		alert('Please Select a Parent Node as a Target');
		return;
	}
	
	if(gCurrParent == -1)
	{
		alert('Parent node has not been selected selected, please select a node in the tree')
		return;
	}

	setEditFields('leaf');
	clearEdits();
	setEditState('new');
	
	// set some defaults
	
	$("#weight-list").val('0');
	$("input[name=order]").val('10');
	$("input[name=min]").val('0');
	$("input[name=max]").val('0');
});

$('#new-parent').on('click',function(event)
{
	event.preventDefault(); 

	if(gCurrType == 'leaf')
	{
		alert('Please Select a Parent Node as a target');
		return;
	}

	if(gCurrParent == -1)
	{
		alert('Parent node has not beed selected, please select a node in the tree')
		return;
	}

	setEditFields('parent');

	clearEdits();
	setEditState('new');
	$("#weight-list").val('0');
	$("input[name=order]").val('10');
});

$('#edit').on('click',function(event)
{
	event.preventDefault(); 

	if(gCurrParent == -1)
	{
		alert('Nothing Selected, please select a node in the tree to edit')
		return;
	}

	setEditState('update');
});

$('#save').on('click',function(event)
{
	event.preventDefault(); 
	
	// can be here if add or update save...
	
	if(gEditState == 'new')
		addNode();
	else
		if(gEditState == 'update')
			updateNode();
		else
			alert('ZZZZ Package Memory Dump : Unknown state. Can\'t save or update');

		setEditState('browse');
});

$('#remove').on('click',function(event)
{
	event.preventDefault(); 
	selected = $('#treeview').jstree('get_selected');	// implies single selection mode
	
	if(selected.length == 0)
	{
		alert('Nothing Selected, Select something!');
		return;
	}
	
	if(gCurrType == 'root')
	{
		alert('Sorry, Can\'t Delete Root Node, Delete entire recipe if that\'s your game!');
		return;
	}
	
	swal({
	  title: "Are You Sure?", 
	  text: "Warning - The delete is permanent!",
	  type: "warning",
	  showCancelButton: true,
	  confirmButtonText: "OK",
	  cancelButtonText: "Cancel",
	  closeOnConfirm: false,
	  closeOnCancel: false
	},
	function(isConfirm)
	{
		if(isConfirm) 
		{
			removeNode(selected[0]);		
			swal("Deleted!", "Your Completed", "success");
		} 
		else 
		{
			swal("Cancelled", "Operation Cancelled", "warning");
		}
	});
});

$('#cancel').on('click',function(event)
{
	event.preventDefault(); 
	alert('Changes Discarded');
	setEditState('browse');
	
	getNode(gCurrNode);
});

function getNode(node_id)
{
	if(node_id == -1)
	{
		//alert('getNode() : Invalid node Id');
		return;
	}
	
	// call the ajax function that gets a node.
	
	$.ajax({
		url:	'{$ajax_url['node']}',	// match URL format for Yii, will be different if 'friendlyURL' is enabled
		type: 	'post',

		data: {		// data sent in post params
					node_id : node_id	// the node id of interest
		},
	   
		error:	function(data) {
			alert('Http Response : ' + data.responseText + ' Operation Failed');
			// turn tree red, this is where communication failed or invalid
			// data to the ajax call was sent.
			gCurrNode = -1;
			gCurrType = 'invalid';
		},
		   
		success: function (data) {

			// NON ZERO is an error, display and get out

			var node = data.data;		

			gCurrNode = node.id; 
			
			if(data.status)
			{
				// specific data for this error, ie, node_id may not exist for other status
				alert('Application Error : ' + data.msg + ' Node Id : ' + node.node_id);
				return;
			}
			  
			// these should both be 0, null is not good for the UI

			if(node.min === null)
				node.min = '0';

			if(node['max'] === null)
				node.max = '0';

			if(node['weight'] === null)
				node.weight = '0';

			// try stuffing some data to input fields

			$("input[name=name]").val(node.name);			
			$("#weight-list").val(node.weight);
			
			// set spec list box (select2 required change trigger)
			$("#spec-list").val(node.spec_id).trigger("change");
			
			$("input[name=order]").val(node.order);			
			$("input[name=min]").val(node.min);			
			$("input[name=max]").val(node.max);			

			// mess with some button states based on node type
			// also if the node is 9999 might want to disable 
			// some fields or other UI indicators

			setEditFields(node.node_type); // set field displable or not
		}
	});			
}

$('#treeview').on('changed.jstree', function (e, data) 	{

	// if we have nothing selected we can't do much, maybe some house keeping

	if(data.selected.length == 0)
	{
		gCurrParent = -1;
		gCurrNode = -1;
		gCurrType = 'invalid';
		setEditState('inactive');
		return;
	}
	
	// console.log(data.node.type); // show intern 'type' of node as set by JSON on load
	
	var json_node_type = data.node.type; // the type coming in from inital load
	
	gCurrType = data.node.type;
	
	setEditState('browse');	// set after we get the current type
	
	if(data.node.type == 'leaf')
	{
		node = getNodeById(data.selected[0])
		gCurrParent = node.parent;
	}
	else
		gCurrParent = data.selected[0]; // it a parent or root so can add to it if any child selected

	// call the ajax function that gets a node.
	
	getNode(data.selected[0]);
});


// Add new node to the tree. Calls remote functions, tree refreshed
// after add so no changes made to display unless successful
function addNode()
{
	selected = $('#treeview').jstree('get_selected');	// implies single selection mode in tree
	
	if(selected.length == 0)
	{
		alert('Nothing Selected, can\'t add!');
		return;
	}
	
	// these must be set for all node types
	
	var name = $("input[name=name]").val();
	var weight_id = $("#weight-list").val();
	var order = $("input[name=order]").val();

	// if a leaf then these must also be set from the form
	if(gEditType == 'leaf')
	{
		var spec_id = $("#spec-list").val();
		var min = $("input[name=min]").val();
		var max = $("input[name=max]").val();
	}
	else
	{	// parent/root values
		var spec_id = 9999;	// indicate it's a parent type
		var min = 0;
		var max = 0;
	}
	
	name = name.trim(); // if your browser doesn't have this get a new browswer

	if(name.length == 0)
	{
		alert('Error, name Can\'t be empty');
		return;
	}
	
	// validate numbers
	if(!isIntNum(weight_id))
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

	// that parent id is enough to get the
	// recipe id, all parms needed
		
	$.ajax({
		url: '{$ajax_url['add']}',	// must match URL format for Yii, will be different if 'friendlyURL' is enabled
		type: 'post',
		data: {
			parent_id : selected[0], 	// this is the parent of the new node. 
			spec_id   : spec_id,
			name      : name,
			weight	  : weight_id,
			order	  : order,
			min		  : min,
			max		  : max
		},

		success: function (data) {
			var node = data.data;

			if(data.status != 0)
			{
				alert('Application Error : ' + data.msg + ', Node Id : ' + node.node_id); 
				
				// on error might reset back to edit mode, need to check
				return;
			}
		
			$('#treeview').jstree('refresh');	// once added get the new data
		},
		
		error:	function(data) {
			alert('Http Response : ' + data.responseText + ' Operation Failed');
			// turn tree red, this is where communication failed or invalid
			// data to the ajax call was sent.
		},
	});		
}

function updateNode()
{
	selected = $('#treeview').jstree('get_selected');	// implies single selection
	
	if(selected.length == 0)
	{
		alert('Nothing Selected, can\'t update!');
		return;
	}
	
	// that parent id is enough to get the
	// recipe id, all parms needed

	var name = $("input[name=name]").val();
	var weight_id = $("#weight-list").val();
	var spec_id = $("#spec-list").val();
		
	var order = $("input[name=order]").val();
	var min = $("input[name=min]").val();
	var max = $("input[name=max]").val();

	name = name.trim(); // if your browser doesn't have this get a new browswer

	// validate common

	if(name.length == 0)
	{
		alert('Error, name Can\'t be empty');
		return;
	}

	if(!isIntNum(weight_id))
	{
		alert('Invalid Weight, must be integer');
		return;
	}

	if(!isIntNum(order))
	{
		alert('Invalid Order, must be integer');
		return;
	}

	// validate and set defaults for all values if not leaf
	
	if(gCurrType == 'leaf')
	{
		if(!isIntNum(spec_id))
		{
			alert('Invalid Spec Id, must be integer');
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
	}
	else
	{
		spec_id = 9999; // force this
		min = max = 0; 	// all zeros
	}

	$.ajax({
		url: '{$ajax_url['update']}',	// must match URL format for Yii, will be different if 'friendlyURL' is enabled
		type: 'post',
		data: {
			node_id   : selected[0], 	// this is the node we want to update
			spec_id   : spec_id,
			name      : name,
			weight	  : weight_id,
			order	  : order,
			min		  : min,
			max		  : max
		},
		success: function (data) {
			var node = data.data;

			if(data.status != 0)
			{
				alert('Application Error : ' + data.msg + ', Node Id : ' + node.node_id); 
				return;
			}
		
			$('#treeview').jstree('refresh');
		},
		
		error:	function(data) {
			alert('Http Response : ' + data.responseText + ' Operation Failed');
			// turn tree red, this is where communication failed or invalid
			// data to the ajax call was sent.
		},

	});		
}

$('#treeview').on("move_node.jstree", function (e, data) {

    //alert('Moving Node Id : ' + data.node.id + ' To Node Id : ' + data.parent);

	// this gets the full json for the node
	
	target_node = getNodeById(data.parent);
	source_node = getNodeById(data.node.id);
	
    if(target_node.type === 'leaf')
		target_id = target_node.parent;	// get leafs parent for the drop target
	else
		target_id = data.parent;

	source_id = data.node.id;
	
	// get position needed for order
	
	position = data.position;

	// console.log('Source Id : ' + source_id + ' Target Id : ' + target_id + ' Sending Position : ' + position + ' Old Position :' + data.old_position);

	$.ajax({
		url: '{$ajax_url['move']}',	// must match URL format for Yii, will be different if 'friendlyURL' is enabled
		type: 'post',
		data: {
			source_id : source_id,
			target_id : target_id,
			position  : position,			
		},
		
		success: function (data) {
						
			var node = data.data;

			if(data.status != 0)
			{
				alert('Application Error : ' + data.msg + ',  Source Id : ' + node.source_id + ' Target Id : ' + node.target_id);
				return;
			}

			alert('Move went OK!');
			
			$('#treeview').jstree('refresh');	// once moved refresh get the new data
		},
		
		error:	function(data) {
			alert('Http Response : ' + data.responseText + ' Operation Failed');
		},
		
	});
	
	$('#treeview').jstree('refresh');
	$('#treeview').jstree('open_all');
	clearEdits();
});

function removeNode(id)
{
	
	// alert('Removing Node ID (and children) ' + id);
	
	$.ajax({
		url: '{$ajax_url['remove']}',	// must match URL format for Yii, will be different if 'friendlyURL' is enabled
		type: 'post',
		data: {
			node_id : id
		},
		
		success: function (data) {
						
			var node = data.data;

			if(data.status != 0)
			{
				alert('Application Error : ' + data.msg + ',  Node Id : ' + node.node_id); 
				return;
			}
			
			//alert('Deleted ' + node.node_cnt + ' Nodes from the tree');
			
			$('#treeview').jstree('refresh');
			clearEdits();
		},
		
		error:	function(data) {
			alert('Http Response : ' + data.responseText + ' Operation Failed');
		},
		
	});
}

function getRecipe(recipe_id)
{
	$.ajax({
		url: '{$ajax_url['get-recipe']}',
		type: 'post',
		data: {
			recipe_id        : recipe_id,
		},

		success: function (data) {
			var info = data.data;

			if(data.status != 0)
			{
				alert('Application Error : ' + data.msg + ' Recipe ID : ' + info.recipe_id); 
				gCurrRecipe = -1;
			}
			else
			{
				gCurrRecipe = info.id;		// update global
				
				// stuff fields reading for edit...
				
				$("#recipe_name").val(info.name);	
				$("#recipe_author").val(info.author);
				$("#recipe_description").val(info.description);
			}
		},
		
		error:	function(data) {
			alert('Http Response : ' + data.responseText + ' Operation Failed');
			gCurrRecipe = -1;
		},
	});		
}

function addRecipe()
{
	// these must be set for all node types
	
	var name = $("#recipe_name").val();	
	var author = $("#recipe_author").val();
	var description = $("#recipe_description").val();
	
	name = name.trim(); // if your browser doesn't have this get a new browswer
	author = author.trim();
	
	if(name.length == 0)
	{
		alert('Error, Recipe Name Can\'t be empty');
		return -1;
	}

	if(author.length == 0)
	{
		alert('Error, Author Can\'t be empty');
		return -1;
	}
		
	$.ajax({
		url: '{$ajax_url['add-recipe']}',
		type: 'post',
		data: {
			name        : name,
			description : description,
			author	    : author
		},

		success: function (data) {
			var info = data.data;

			if(data.status != 0)
			{
				alert('Application Error : ' + data.msg); 
				gCurrRecipe = -1;
			}
			else
			{
				var id = info.recipe_id;

				updateTreeURL(id);	// update the tree, refresh, reload etc
				showRecipeEdit(false);
				refreshRecipeList(id);
				showTreeEdit(true);
				clearRecipeEdits();
				gCurrRecipe = id;		// update global
				setRecipeState('inactive'); // if here reset so buttons OK
			}
		},
		
		error:	function(data) {
			alert('Http Response : ' + data.responseText + ' Operation Failed');
			gCurrRecipe = -1;
		},
	});		
}

function deleteRecipe(recipe_id)
{			
	$.ajax({
		url: '{$ajax_url['delete-recipe']}',
		type: 'post',
		data: {
			recipe_id : recipe_id
		},

		success: function (data) {
			var info = data.data;

			if(data.status != 0)
			{
				alert('Application Error : ' + data.msg); 
				gCurrRecipe = -1;
			}
			else
			{
				var id = info.recipe_id;
				invalidateTree();
				gCurrRecipe = -1;		// update global
				setRecipeState('inactive'); // if here reset so buttons OK
				refreshRecipeList(false);
			}
		},
		
		error:	function(data) {
			alert('Http Response : ' + data.responseText + ' Operation Failed');
			gCurrRecipe = -1;
		},
	});		
}

function copyRecipe(recipe_id)
{			
	$.ajax({
		url: '{$ajax_url['copy-recipe']}',
		type: 'post',
		data: {
			recipe_id : recipe_id
		},

		success: function (data) {
			var info = data.data;

			if(data.status != 0)
			{
				alert('Application Error : ' + data.msg); 
				gCurrRecipe = -1;
			}
			else
			{
				var id = info.recipe_id;

				updateTreeURL(id);	// update the tree, refresh, reload etc
				showRecipeEdit(false);
				refreshRecipeList(id);
				showTreeEdit(true);
				clearRecipeEdits();
				gCurrRecipe = id;		// update global
				setRecipeState('inactive'); // if here reset so buttons OK
			}
		},
		
		error:	function(data) {
			alert('Http Response : ' + data.responseText + ' Operation Failed');
			gCurrRecipe = -1;
		},
	});
}

function updateRecipe()
{
	if(gCurrRecipe == -1)
	{
		alert('No active Recipe Id, refresh page and select a recipe');
		return;
	}

	// these must be set for all node types
	
	var name = $("#recipe_name").val();	
	var author = $("#recipe_author").val();
	var description = $("#recipe_description").val();
	
	name = name.trim(); // if your browser doesn't have this get a new browswer
	author = author.trim();
	
	if(name.length == 0)
	{
		alert('Error, Recipe Name Can\'t be empty');
		return -1;
	}

	if(author.length == 0)
	{
		alert('Error, Author Can\'t be empty');
		return -1;
	}
		
	$.ajax({
		url: '{$ajax_url['update-recipe']}',
		type: 'post',
		data: {
			recipe_id	: gCurrRecipe,
			name        : name,
			description : description,
			author	    : author
		},

		success: function (data) {
			var info = data.data;

			if(data.status != 0)
			{
				alert('Application Error : ' + data.msg); 

				// out of recipe edit mode
				gCurrRecipe = -1;
				showRecipeEdit(false);
				showTreeEdit(true);
			}
			else
			{
				// if success go back to tree work, otherwise says on recipe edit until cancel or successful save

				updateTreeURL(id);	// update the tree, refresh, reload etc
				showRecipeEdit(false);
				refreshRecipeList(id);
				showTreeEdit(true);
				clearRecipeEdits();
				gCurrRecipe = id;		// update global
				setRecipeState('inactive'); // if here reset so buttons OK
			}
		},
		
		error:	function(data) {
			alert('Http Response : ' + data.responseText + ' Operation Failed');

			// out of recipe edit mode
			gCurrRecipe = -1;
			showRecipeEdit(false);
			refreshRecipeList();
			showTreeEdit(true);
		},
	});		
}

function refreshRecipeList(selected)
{
	$.ajax({
		url: '{$ajax_url['get-recipes']}',
		type: 'post',
		data: {},

		success: function (html) {
			$("#recipe_list").html(html);
			
			if(selected !== false)
				$("#recipe_list").val(selected);
		},
		
		error:	function(data) {
			alert('Can\'t acquire Recipes List, Communications error');
		},
	});		
}

JS;
$this->registerJs($script, view::POS_END);
?>
