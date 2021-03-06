<?php

require_once 'channels.php';
require_once 'videos.php';
require_once 'controller_anime.php';

class Controller_channel_video extends Controller_anime {
	function get_title($chain=null) {
		return $this->get_title_helper($chain, array(
			$this->chain => $this->video['title'],
		));
	}
	function get_url($chain=null, $params=null) {
		return $this->get_url_helper($chain, $params, array(
			$this->chain => array('id' => $this->video['id']),
			'channel' => array('id' => $this->channel['id']),
		));
	}
	function acd_meta() {
                $json = shell_exec(
                        "ACD_CLI_CACHE_PATH={$this->config["acd_cli_cache_path"]} " .
                        "ACD_CLI_SETTINGS_PATH={$this->config["acd_cli_cache_path"]} " .
                        "/usr/local/bin/acdcli metadata " .
                        "{$this->config["acd_cli_contents_dir"]}/{$this->video["filename"]} 2>&1"
                );
                return json_decode($json, true);
        }
        function acd_sync() {
                shell_exec(
                        "ACD_CLI_CACHE_PATH={$this->config["acd_cli_cache_path"]} " .
                        "ACD_CLI_SETTINGS_PATH={$this->config["acd_cli_cache_path"]} " .
                        "/usr/local/bin/acdcli sync"
                );
        }

	function run() {
		$channels = new Model_channels();
		$videos = new Model_videos();

		if (isset($this->get["id"])) {
			$this->channel = $channels->select_by_video_id($this->get["id"]);
			$this->video = $videos->select($this->get["id"]);
			$this->set("channel", $this->channel);
			$this->set("video", $this->video);
		}

		if (filesize("{$this->config["contents_dir"]}/{$this->video["filename"]}") == 0) {
			$pathinfo = pathinfo($this->video["filename"]);
			$json = $this->acd_meta();
                        if (is_null($json)) {
                                $this->acd_sync();
                        	$json = $this->acd_meta();
                        }
			$this->set("video_url", $json["tempLink"] . "?/v." . $pathinfo["extension"]);
		} else {
			$video_url = "{$this->config["contents_dir_url"]}/{$this->video["filename"]}";
			$this->set("video_url", $video_url);
		}

		$this->render();
	}
}
