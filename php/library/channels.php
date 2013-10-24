<?php

require_once 'db.php';

class Model_channels {
	function __construct() {
		$this->config = &$GLOBALS["config"];
		$this->db = DB::get_instance();
	}

	function select($id) {
		$sql = "SELECT *" .
			" FROM `channels`" .
			" WHERE `id` = ?";
		$statement = $this->db->prepare($sql);
		$statement->execute(array($id));
		return $statement->fetch(PDO::FETCH_ASSOC);
	}
	function select_with_videos() {
		/*
		 * 1. `videos`を`channelId`ごとにgroupし、
		 *    groupごとに`downloadedAt`が最大のものを抽出する（これを`A`とする）
		 * 2. `videos`から`downlaodedAt`が`A`のいずれかの値と一致するものを抽出する（これを`B`とする）
		 * 3. `channels`に`B`をjoinし、
		 *    `channels`.`id`と`videos`.`channelId`が同じものを抽出する
		 */
		$sql = "SELECT `t2`.*, `t1`.*" .
			" FROM `channels` AS `t1` LEFT JOIN (" .
				" SELECT * FROM `videos`" .
				" WHERE `downloadedAt` IN (" .
					" SELECT MAX(`downloadedAt`) FROM `videos`" .
					" GROUP BY `channelId`" .
				" )" .
			" ) AS `t2` ON `t1`.`id` = `t2`.`channelId`" .
			" ORDER BY `t1`.`modifiedAt` DESC";
		$statement = $this->db->prepare($sql);
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}
	function select_by_video_id($video_id) {
		$sql = "SELECT `t2`.*" .
			" FROM `videos` AS `t1`" .
			" RIGHT JOIN `channels` AS `t2` ON `t1`.`channelId` = `t2`.`id`" .
			" WHERE `t1`.`id` = ?";
		$statement = $this->db->prepare($sql);
		$statement->execute(array($video_id));
		return $statement->fetch(PDO::FETCH_ASSOC);
	}
	function select_by_nico_channel_id($channel_id) {
		$sql = "SELECT *" .
			" FROM `channels`" .
			" WHERE `nicoChannelId` = ?";
		$statement = $this->db->prepare($sql);
		$statement->execute(array($channel_id));
		return $statement->fetch(PDO::FETCH_ASSOC);
	}
	function insert_into($channel_id, $title, $description, $keywords) {
		$sql = "INSERT INTO `channels`" .
			" (`nicoChannelId`, `title`, `description`, `keywords`, `createdAt`)" .
			" VALUES (?, ?, ?, ?, ?)";
		$statement = $this->db->prepare($sql);
		return $statement->execute(array(
			$channel_id,
			$title,
			$description,
			$keywords,
			current_date(),
		));
	}
	function count() {
		$sql = "SELECT COUNT(*) AS `count`" .
			" FROM `channels`";
		$statement = $this->db->prepare($sql);
		$statement->execute();
		return $statement->fetchColumn();
	}
	function count_not_crawled() {
		$sql = "SELECT COUNT(*) AS `count`" .
			" FROM `channels`" .
			" WHERE `crawledAt` IS NULL";
		$statement = $this->db->prepare($sql);
		$statement->execute();
		return $statement->fetchColumn();
	}
}