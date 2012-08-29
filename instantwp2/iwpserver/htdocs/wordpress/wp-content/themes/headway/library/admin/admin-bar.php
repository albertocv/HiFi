<?php
class HeadwayAdminBar {
	
	
	public static function init() {
		
		add_action('admin_bar_menu', array(__CLASS__, 'add_admin_bar_nodes'), 75);
		
	}
	
	
	public static function remove_admin_bar() {

		show_admin_bar(false);
		remove_action('wp_head', '_admin_bar_bump_cb');
				
	}
	
	
	public static function add_admin_bar_nodes() {
		
		if ( !HeadwayCapabilities::can_user_visually_edit() )
			return;
		
		global $wp_admin_bar;
			
		$default_visual_editor_mode = current_theme_supports('headway-grid') ? 'grid' : 'manage';
		
		$layout_hash = !is_admin() ? '#layout=' . HeadwayLayout::get_current() : null;
				
		//Headway Root
		$wp_admin_bar->add_menu(array(
			'id' => 'headway', 
			'title' => 'Headway', 
			'href' => add_query_arg(array('visual-editor' => 'true', 'visual-editor-mode' => $default_visual_editor_mode), home_url()) . $layout_hash
		));
		
			//Visual Editor
				$wp_admin_bar->add_menu(array(
					'parent' => 'headway',
					'id' => 'headway-ve', 
					'title' => 'Visual Editor',  
					'href' =>  add_query_arg(array('visual-editor' => 'true', 'visual-editor-mode' => $default_visual_editor_mode), home_url()) . $layout_hash
				));
				
					//Grid
						if ( current_theme_supports('headway-grid') ) {

							$wp_admin_bar->add_menu(array(
								'parent' => 'headway-ve',
								'id' => 'headway-ve-grid', 
								'title' => 'Grid',  
								'href' =>  add_query_arg(array('visual-editor' => 'true', 'visual-editor-mode' => 'grid'), home_url()) . $layout_hash
							));

						}
		
					//Manage
						$wp_admin_bar->add_menu(array(
							'parent' => 'headway-ve',
							'id' => 'headway-ve-manage', 
							'title' => 'Manage',  
							'href' =>  add_query_arg(array('visual-editor' => 'true', 'visual-editor-mode' => 'manage'), home_url()) . $layout_hash
						));
			
					//Design Editor
						if ( current_theme_supports('headway-design-editor') ) {

							$wp_admin_bar->add_menu(array(
								'parent' => 'headway-ve',
								'id' => 'headway-ve-design', 
								'title' => 'Design',  
								'href' => add_query_arg(array('visual-editor' => 'true', 'visual-editor-mode' => 'design'), home_url()) . $layout_hash
							));

						}

			//Extend
				$wp_admin_bar->add_menu(array(
					'parent' => 'headway',
					'id' => 'headway-admin-extend', 
					'title' => 'Extend',  
					'href' => admin_url('admin.php?page=headway-extend')
				));
			
			//Admin Options
				$wp_admin_bar->add_menu(array(
					'parent' => 'headway',
					'id' => 'headway-admin-options', 
					'title' => 'Options',  
					'href' => admin_url('admin.php?page=headway-options')
				));

					$wp_admin_bar->add_menu(array(
						'parent' => 'headway-admin-options',
						'id' => 'headway-admin-options-general', 
						'title' => 'General',  
						'href' => admin_url('admin.php?page=headway-options#tab-general')
					));
					
					$wp_admin_bar->add_menu(array(
						'parent' => 'headway-admin-options',
						'id' => 'headway-admin-options-seo', 
						'title' => 'Search Engine Optimization',  
						'href' => admin_url('admin.php?page=headway-options#tab-seo')
					));
					
					$wp_admin_bar->add_menu(array(
						'parent' => 'headway-admin-options',
						'id' => 'headway-admin-options-scripts',
						'title' => 'Scripts/Analytics',  
						'href' => admin_url('admin.php?page=headway-options#tab-scripts')
					));
					
					$wp_admin_bar->add_menu(array(
						'parent' => 'headway-admin-options',
						'id' => 'headway-admin-options-visual-editor',
						'title' => 'Visual Editor',  
						'href' => admin_url('admin.php?page=headway-options#tab-visual-editor')
					));
					
					$wp_admin_bar->add_menu(array(
						'parent' => 'headway-admin-options',
						'id' => 'headway-admin-options-advanced',
						'title' => 'Advanced',  
						'href' => admin_url('admin.php?page=headway-options#tab-advanced')
					));
					
			//Admin Tools
				$wp_admin_bar->add_menu(array(
					'parent' => 'headway',
					'id' => 'headway-admin-tools', 
					'title' => 'Tools',  
					'href' => admin_url('admin.php?page=headway-tools')
				));

					$wp_admin_bar->add_menu(array(
						'parent' => 'headway-admin-tools',
						'id' => 'headway-admin-tools-system-info', 
						'title' => 'System Info',  
						'href' => admin_url('admin.php?page=headway-tools#tab-system-info')
					));
					
					$wp_admin_bar->add_menu(array(
						'parent' => 'headway-admin-tools',
						'id' => 'headway-admin-tools-maintenance', 
						'title' => 'Maintenance',  
						'href' => admin_url('admin.php?page=headway-tools#tab-maintenance')
					));
					
					$wp_admin_bar->add_menu(array(
						'parent' => 'headway-admin-tools',
						'id' => 'headway-admin-tools-reset', 
						'title' => 'Reset',  
						'href' => admin_url('admin.php?page=headway-tools#tab-reset')
					));
					
	}
	
	
}