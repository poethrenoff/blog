<?php
include_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/config/config.php';

$work_list = db::select_all( 'select * from work order by work_id' );
foreach($work_list as $index => $work) {
	print $work['work_title'] . PHP_EOL;
	db::delete('work_shingle', array('work_id' => $work['work_id']));
	for ($i = 1; $i <= shingles::SHINGLES_COUNT; $i++) {
		$shingles = array_unique(shingles::get_shingles($work['work_text'], $i));
		foreach ($shingles as $shingle) {
			db::insert('work_shingle', array('work_id' => $work['work_id'], 'shingle_length' => $i,
				'shingle_value' => $shingle, 'shingle_weight' => 1/count($shingles) ));
		}
	}
}
