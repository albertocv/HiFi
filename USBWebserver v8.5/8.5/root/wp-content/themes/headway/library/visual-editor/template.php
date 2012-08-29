<!DOCTYPE HTML>
<html lang="en">

<head>

<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta http-equiv="cache-control" content="no-cache" />

<title>Visual Editor: Loading</title>

<?php do_action('headway_visual_editor_head'); ?>

</head><!-- /head -->

<!-- This background color has been inlined to reduce the white flicker during loading. -->
<body class="visual-editor-open visual-editor-mode-<?php echo HeadwayVisualEditor::get_current_mode(); ?>" style="background: #1c1c1c;">
		
<div id="visual-editor-hidden-inputs">
</div>
	
<div id="loading">
	<div class="loading-message">
		<div class="logo"></div>
				
		<div class="loading-bar">
			<div class="loading-bar-inside" style="width: 0%;"></div>
		</div>
		
		<?php do_action('headway_visual_editor_tips'); ?>
	</div>
</div><!-- #loading -->	
	
<div id="menu">
	<a id="logo" href="http://headwaythemes.com/members" title="Headway Members' Dashboard" target="_blank" class="tooltip-top-left">Headway Members' Dashboard</a>
	
	<ul id="modes">
		<?php do_action('headway_visual_editor_modes'); ?>
	</ul>
	
	<div id="menu-right">
		
		<div id="menu-links">
			<ul>
				<?php do_action('headway_visual_editor_menu_links'); ?>
			</ul>
		</div>
		
		<div id="current-layout">			
			<?php do_action('headway_visual_editor_page_switcher'); ?>
		</div>
		
		<div id="save-button-container" class="save-button-container">
			<span id="save-button" class="save-button">Save</span>
			<span id="inactive-save-button" class="save-button tooltip-top-right" title="Nothing to save at this time. What are you doing? Get busy!&emsp;&lt;em&gt;Shortcut: Ctrl + S&lt;/em&gt;">Save</span>
		</div>

		<?php
		if ( HeadwayVisualEditor::is_mode('grid') )
			echo '
			<div id="preview-button-container" class="save-button-container">
				<span class="save-button preview-button tooltip-top-right" id="preview-button" title="Click to hide the grid and show how the website looks before saving.">Preview</span>
				<span class="save-button preview-button" id="inactive-preview-button">Preview</span>
			</div>
			';
		?>
		
	</div><!-- #menu-right -->
</div><!-- #menu -->

<!-- Big Boy iframe -->
<iframe id="content" class="content"  src=""></iframe>
<?php
if ( HeadwayVisualEditor::is_mode('grid') )
	echo '<iframe id="preview" class="content" src="" style="display: none;"></iframe>';
?>
<div id="iframe-overlay"></div>
<div id="iframe-loading-overlay"></div>
<!-- #iframe#content -->

<div id="panel">
				
	<ul id="panel-top">
				
		<?php do_action('headway_visual_editor_panel_top'); ?>
		
	</ul><!-- #ul#panel-top -->
		
	<?php do_action('headway_visual_editor_content'); ?>


</div><!-- div#panel -->

<div id="boxes">
	<?php do_action('headway_visual_editor_boxes'); ?>
</div><!-- div#boxes -->

<?php do_action('headway_visual_editor_footer'); ?>
	
</body>
</html>