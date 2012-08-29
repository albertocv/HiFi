<?php
function provafield() {
if (is_singular( 'progetto' )){?>
<ul id="infoprogetto">
<li>Difficolt&agrave;:
<?php  
the_field('difficolt');
//$values = get_field('difficolt');
//var_dump(values)?>
</li>
<li>Costo: <?php the_field('costo')?>&euro;</li>
<li>Tempo di realizzazione: <?php the_field('tempo_di_realizzazione')?> minuti</li>
<li>Materiale necessario: <div id="materialslist"><?php the_field('materiale')?></div></li>
</ul>
<div id="preparazione">
<h2>Preparazione</h2>
<?php the_field('preparazione')?>
</div>
<div id="costruzione">
<h2>Costruzione</h2>
<?php the_field('costruzione')?>
</div>
<div id="test">
<h2>Test</h2>
<?php the_field('test')?>
</div>
<div id="conclusioni">
<h2>Conclusioni</h2>
<?php the_field('conclusioni')?>
</div>
<?php
}}
  add_action('headway_after_entry_title', 'provafield');?>
<?php
 //Show projects in home and feed
 //add_filter( 'pre_get_posts', 'my_get_posts' );

function my_get_posts( $query ) {

	if ( (is_home() && $query->is_main_query())  || is_feed())
		$query->set( 'post_type', array( 'post', 'progetto' ) );

	return $query;
}
?>