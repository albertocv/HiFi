<?php
class HeadwayWidgets {
	
	
	public static function init() {
		
		add_filter('get_search_form', array(__CLASS__, 'search_form'));
		
		add_filter('widget_title', array(__CLASS__, 'remove_unnecessary_nbsp_from_titles'));
		
	}
	
	
	public static function search_form() {
		
		$placeholder = apply_filters('headway_search_placeholder', HeadwayOption::get('search-placeholder', 'general', 'Type to search, then press enter'));
		$search_query = get_search_query();
		
		$search_input_attributes = array(
			'type' => 'text',
			'class' => 'field',
			'name' => 's',
			'id' => 's'
		);
						
		/* Handle the placeholder and value */
		if ( !headway_is_ie() ) {
			
			$search_input_attributes['placeholder'] = $placeholder;
			
		} else {
			
			$search_input_attributes['value'] = $search_query ? $search_query : $placeholder;

			$search_input_attributes['onclick'] = 'if(this.value==\'' . $placeholder . '\')this.value=\'\';';
			$search_input_attributes['onblur'] = 'if(this.value==\'\')this.value=\'' . $placeholder . '\';';
			
		}
		
		/* Turn the array into real HTML attributes */
		$search_input_attributes = apply_filters('headway_search_input_attributes', $search_input_attributes);
		$search_input_attributes_string = '';
		
		foreach ( $search_input_attributes as $attribute => $value ) 
			$search_input_attributes_string .= $attribute . '="' . $value . '" ';
		
		return '
			<form method="get" id="searchform" action="' . esc_url(home_url('/')) . '">
				<label for="s" class="assistive-text">' . __('Search', 'headway') . '</label>
				<input ' . trim($search_input_attributes_string) .' />
				<input type="submit" class="submit" name="submit" id="searchsubmit" value="' . esc_attr__('Search', 'headway') . '" />
			</form>
		';
		
	}
	
	
	public static function remove_unnecessary_nbsp_from_titles($title) {
		
		if ( $title == '&nbsp;' )
			return null;
			
		return $title;
		
	}
	
	
}