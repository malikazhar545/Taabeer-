<?php
/** Native Elementor editorial pages. The deployment plugin is not involved in rendering. */
defined('ABSPATH') || exit;

function taabeer_editorial_page_definitions() {
	return array(
		'contemporary-pakistan'=>array('Contemporary Pakistan','Design in the present tense','Contemporary Pakistan is part of a continuing conversation between the familiar and the unexpected. TAABEER explores that conversation through objects, materials and the people giving them a new expression.','collection-expression.webp',array(
			'A different point of view'=>'A piece can carry a reference to the past without repeating it. A quieter colour, a different proportion or a new use for a familiar material can change how we experience an object. Our interest lies in those thoughtful decisions and in the individual point of view they reveal.',
			'Contemporary Pakistan, considered closely'=>'We look across clothing, jewellery, furniture, interiors and art. Rather than treating these as separate worlds, we consider the connections between them: how a surface catches the light, how a silhouette sits in a room, or how a piece becomes part of everyday life. These details guide a selection built around character and lasting relevance.',
			'The work and the voice behind it'=>'Good presentation gives a design enough space to be understood. As our editorial programme develops, we will bring together conversations, studio perspectives and collection notes. Credits and descriptions will distinguish the person who designed a piece from the people involved in making it. Each story begins with the work and the context its creator chooses to share.',
			'Explore the TAABEER perspective'=>'Contemporary Pakistan is an invitation to look again, whether Pakistani design is part of your own heritage or a new discovery. Browse our collection themes for a first introduction, follow the journal as new stories arrive, or contact us to share a creative practice. Our selection will grow through careful observation and conversation, with the emphasis always on individual work.'
		)),
		'pakistan-in-the-making'=>array('Pakistan in the Making','A culture in conversation','Pakistan in the Making explores how ideas, encounters and changing ways of life become part of design. It is a space for looking closely at the relationship between an object and its context.','story-studio.webp',array(
			'Begin with an object'=>'A pattern, a surface or a construction detail can open up a much larger conversation. We begin with what can be seen and understood, then make room for the history and personal experience that a maker brings to the work. This approach keeps the object at the centre of the story.',
			'Pakistan in the Making'=>'Heritage is encountered in different ways by different people. For one designer it may be a material used at home; for another, a visual reference reconsidered through a new medium. We are interested in those specific perspectives. We do not assume that one object or one voice can speak for an entire place or community.',
			'Context matters'=>'Our journal is being developed around clear attribution, thoughtful questions and close attention to the work. Future features will introduce the sources and voices behind each story. Where inspiration, design and production have different origins, those distinctions will remain visible. This helps a reader understand a piece without reducing its story to a decorative label.',
			'Continue exploring'=>'Pakistan in the Making sits alongside our regional notebooks and contemporary design pages. Together they offer different ways into the TAABEER selection. Explore the collection themes to see how objects are considered in relation to the body, the home and everyday use. Designers, researchers and creative collaborators are welcome to start a conversation through our partnerships page.'
		)),
		'new-voices'=>array('New Voices','An individual way of seeing','New Voices is TAABEER’s space for the designers, artists and independent studios developing a distinctive language within Pakistani design. We begin with curiosity about their work and the decisions behind it.','discover-pakistan.webp',array(
			'Make room for a perspective'=>'A creative voice is more than a recognisable style. It can be found in a choice of material, a way of working or a question that returns across a body of work. Our interest is in understanding those choices and presenting them in the creator’s own context.',
			'New Voices, thoughtful introductions'=>'As this series develops, we will introduce individual practices through conversations, images and selected pieces. Profiles will focus on what a person makes, how their work develops and what they want a reader to notice. A clear credit matters as much as a considered photograph. Together they make an introduction more personal and more useful.',
			'Across disciplines'=>'We welcome perspectives from clothing, textiles, jewellery, objects, furniture and art. The connections between these disciplines can be as interesting as their differences. A study of colour may move from one surface to another; a practical detail may become a signature. We look for work with a clear point of view and room for a deeper conversation.',
			'Share your work'=>'New Voices will grow gradually as our editorial programme takes shape. If you would like to introduce your practice, visit the partnerships page and tell us about your work, materials and approach. In the meantime, explore the collection themes or continue through Discover Pakistan. Each offers another starting point for understanding the world TAABEER is building.'
		))
	);
}
function taabeer_region_definitions() {
	return array(
		'punjab'=>array('Punjab','An invitation to look closely','story-object.webp','Punjab is one of the starting points in TAABEER’s exploration of Pakistani design. This notebook makes space for individual objects, creative perspectives and the details that connect a piece to everyday life.'),
		'sindh'=>array('Sindh','Colour, surface and a sense of place','story-studio.webp','Sindh opens another chapter in our exploration of Pakistani design. We approach the region through a close look at materials, visual expression and the stories that people choose to share about their work.'),
		'balochistan'=>array('Balochistan','A considered regional perspective','story-textile.webp','Balochistan is part of the wider cultural landscape TAABEER seeks to explore. This regional notebook begins with respect for individual voices and an interest in the relationship between a piece, its making and its context.'),
		'khyber-pakhtunkhwa'=>array('Khyber Pakhtunkhwa','Objects, people and perspective','collection-leather.webp','Khyber Pakhtunkhwa offers a further starting point for our regional notebooks. TAABEER is interested in the choices behind objects: their materials, their use and the perspectives of the people who bring them into being.'),
		'gilgit-baltistan'=>array('Gilgit-Baltistan','A place within a wider conversation','collection-wear.webp','Gilgit-Baltistan forms part of TAABEER’s exploration of place and design. This notebook creates a space for considered introductions, with attention to the context of individual pieces and the voices behind them.')
	);
}
function taabeer_native_editorial_layout($name,$strap,$intro,$image,$sections,$region=false) {
	$lead=taabeer_visual_container(array(taabeer_visual_heading($region?'Discover Pakistan / Regional notebooks':'Discover Pakistan','p','eyebrow'),taabeer_visual_heading($name,'h1'),taabeer_visual_text('<p>'.esc_html($intro).'</p>','tb-notebook-intro')),'tb-notebook-heading shell');
	$photo=taabeer_visual_container(array(taabeer_visual_image(taabeer_demo_image_url($image)),taabeer_visual_text('<p>Material study from the TAABEER visual collection.</p>','tb-notebook-caption')),'tb-notebook-photo');
	$opening=array(taabeer_visual_heading($strap,'h2'));$rest=array();$i=0;
	foreach($sections as $heading=>$copy){$part=taabeer_visual_container(array(taabeer_visual_heading($heading,'h2'),taabeer_visual_text('<p>'.esc_html($copy).'</p>')),'tb-notebook-chapter');if(0===$i++){$opening[]=$part;}else{$rest[]=$part;}}
	return array($lead,taabeer_visual_container(array($photo,taabeer_visual_container($opening,'tb-notebook-opening')),'tb-notebook-lead shell'),taabeer_visual_container($rest,'tb-notebook-chapters shell'),taabeer_visual_container(array(taabeer_visual_heading('Continue the conversation','h2'),taabeer_visual_text('<p>Explore our collection themes or share a creative practice with TAABEER.</p>'),taabeer_visual_container(array(taabeer_visual_button('Explore collections',home_url('/collections/')),taabeer_visual_button('Discover Pakistan',home_url('/discover-pakistan/'),'button button--outline')),'button-row')),'tb-notebook-next shell'));
}
function taabeer_migrate_editorial_pages_1_1_1() {
	if(get_option('taabeer_editorial_pages_1_1_1') || !did_action('elementor/init')){return;}
	taabeer_visual_design_system();
	$definitions=taabeer_editorial_page_definitions();
	foreach(taabeer_region_definitions() as $slug=>$region){
		$name=$region[0];
		$definitions[$slug]=array($name,$region[1],$region[3],$region[2],array(
			'A closer look at '.$name=>'A regional introduction is a beginning, not a complete picture. We look for the details that make an individual piece worth understanding: the feel of a surface, the balance of a form and the choices involved in making it. Each object offers one perspective within a much wider conversation.',
			'People before labels'=>'Our approach gives the people behind the work room to describe their own practice. Design inspiration, production location and personal identity do not always tell the same story. We will keep those distinctions clear as this notebook grows, with credits and context attached to the individual work rather than broad claims about a region.',
			'Materials in everyday life'=>'A considered object finds its place through use. It may be worn, held, displayed or returned to as part of a daily routine. TAABEER looks at the connection between visual character and practical purpose, bringing the same attention to a textile as to a piece for the home. The collection themes offer another way to follow those connections.',
			'Follow the notebook'=>'This page is an open invitation to explore '.$name.' through future conversations and carefully prepared features. Individual maker profiles and collection stories will be introduced as the editorial programme develops. The imagery here is a material study from our visual collection, rather than a documentary record of the region. Continue through Discover Pakistan, browse the collection themes or contact us to introduce a creative practice.'
		));
	}
	$region_parent=get_page_by_path('discover-pakistan/regions');
	foreach($definitions as $slug=>$definition){
		$is_region=isset(taabeer_region_definitions()[$slug]);
		$path='discover-pakistan/'.($is_region?'regions/':'').$slug;
		$post=get_page_by_path($path);if(!$post){continue;}
		add_post_meta($post->ID,'_taabeer_before_editorial_1_1_1',wp_slash(array('content'=>$post->post_content,'elementor'=>get_post_meta($post->ID,'_elementor_data',true),'status'=>$post->post_status)),true);
		$data=taabeer_native_editorial_layout(...array_merge($definition,array($is_region)));
		taabeer_visual_save($post->ID,$data);
		$copy='<p>'.esc_html($definition[2]).'</p>';foreach($definition[4] as $heading=>$body){$copy.='<h2>'.esc_html($heading).'</h2><p>'.esc_html($body).'</p>';}
		wp_update_post(wp_slash(array('ID'=>$post->ID,'post_status'=>'publish','post_content'=>$copy)));
		delete_post_meta($post->ID,'_taabeer_unapproved');
		update_post_meta($post->ID,'_yoast_wpseo_title',$definition[0].' | Pakistani Design & Stories | TAABEER');
		update_post_meta($post->ID,'_yoast_wpseo_metadesc','Explore '.$definition[0].' with TAABEER: individual creative voices, materials and thoughtful perspectives on Pakistani design and heritage.');
		update_post_meta($post->ID,'_yoast_wpseo_focuskw',$definition[0]);
	}
	// Regional links lead to the original WordPress Pages, which are now published.
	if($region_parent){
		add_post_meta($region_parent->ID,'_taabeer_before_editorial_1_1_1',wp_slash(array('content'=>$region_parent->post_content,'elementor'=>get_post_meta($region_parent->ID,'_elementor_data',true),'status'=>$region_parent->post_status)),true);$cards=array();
		foreach(taabeer_region_definitions() as $slug=>$r){$cards[]=taabeer_visual_container(array(taabeer_visual_image(taabeer_demo_image_url($r[2])),taabeer_visual_heading($r[0],'h2'),taabeer_visual_text('<p>'.esc_html($r[1]).'</p>'),taabeer_visual_button('Explore '.$r[0],home_url('/discover-pakistan/regions/'.$slug.'/'),'text-link')),'tb-region-card');}
		taabeer_visual_save($region_parent->ID,array(taabeer_visual_container(array(taabeer_visual_heading('Discover Pakistan','p','eyebrow'),taabeer_visual_heading('Pakistan by Region','h1'),taabeer_visual_text('<p>Five regional notebooks. Different starting points for exploring the people, objects and ideas behind Pakistani design.</p>')),'tb-notebook-heading shell'),taabeer_visual_container($cards,'tb-region-grid shell')));
		delete_post_meta($region_parent->ID,'_taabeer_unapproved');
	}
	\Elementor\Plugin::instance()->files_manager->clear_cache();
	update_option('taabeer_editorial_pages_1_1_1',1);
}
add_action('admin_init',function(){if(current_user_can('manage_options') && version_compare(TAABEER_VERSION,'1.1.1','>=')){taabeer_migrate_editorial_pages_1_1_1();}},45);
