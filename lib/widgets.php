<?php

/**
 * A widget to display a user's latest tweets
 */
class RD_Twitter_Widget extends WP_Widget {

	function __construct() {
		/* Widget settings. */
		$widget_ops = array('username' => 'Your username here', 'limit' => 4, 'show_retweets' => true, 'description' => 'A widget to show your latest tweets');

		/* Widget control settings. */
		$control_ops = array('width' => 300, 'height' => 350, 'id_base' => 'twitter-widget');

		/* Create the widget. */
		parent::__construct('twitter-widget', 'RD Twitter Widget', $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		extract($args);
		/* User-selected settings. */
		$title = apply_filters('widget_title', $instance['title']);
		$username = $instance['username'];
		$limit = $instance['limit'];
		$show_retweets = $instance['show_retweets'];

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Title of widget (before and after defined by themes). */
		if ($title) {
			echo $before_title . $title . $after_title;
		}
		?>

		<ol class="tweets">
			<?php
			$doc = new DOMDocument();
			if (($transient = get_transient('RD_Twitter_feed')) === false) {
				$feedURL = 'http://api.twitter.com/1/statuses/user_timeline.rss?screen_name=' . $username;
				$doc->load($feedURL);
				set_transient('RD_Twitter_feed', $doc->saveXML(), 60);
			} else {
				$doc->loadXML($transient);
			}

			$format = get_option('date_format') . ' ' . get_option('time_format');


			$arrFeeds = array();
			foreach ($doc->getElementsByTagName('item') as $node) {
				$itemRSS = array(
						'tweet_title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
						'tweet_date' => date($format, strtotime($node->getElementsByTagName('pubDate')->item(0)->nodeValue)),
						'tweet_link' => $node->getElementsByTagName('link')->item(0)->nodeValue
				);
				if ($show_retweets == true || strrpos($itemRSS['tweet_title'], 'RT ') == false) {
					array_push($arrFeeds, $itemRSS);
				}
			}
			for ($x = 0; $x < $limit; $x++) {
				$tweet_title = str_replace($username . ': ', '', $arrFeeds[$x]['tweet_title']);
				$str = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", "<a href=\"\\0\">\\0</a>", $tweet_title);
				$pattern = '/[#|@][^\s]*/';
				preg_match_all($pattern, $str, $matches);

				foreach ($matches[0] as $keyword) {
					$keyword = str_replace(")", "", $keyword);
					$link = str_replace("#", "#", $keyword);
					$link = str_replace("@", "", $keyword);
					if (strstr($keyword, "@")) {
						$search = "<a href=\"http://twitter.com/$link\">$keyword</a>";
					} else {
						$link = urlencode($link);
						$search = "<a href=\"http://twitter.com/#search?q=$link\" class=\"grey\">$keyword</a>";
					}
					$str = str_replace($keyword, $search, $str);
				}
				?>

				<li>
					<p class="tweet"><?php echo $str ?></p>
					<p class="published">- <a href="<?php echo $arrFeeds[$x]['tweet_link']; ?>"><?php echo $arrFeeds[$x]['tweet_date']; ?></a></p>
				</li>

			<?php
		}
		?>

		</ul>

		<?php echo $after_widget; ?>
		<?php
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['username'] = strip_tags($new_instance['username']);
		$instance['limit'] = $new_instance['limit'];
		$instance['show_retweets'] = $new_instance['show_retweets'];
		return $instance;
	}

	function form($instance) {

		/* Set up some default widget settings. */
		$defaults = array('title' => 'Latest Tweets', 'username' => 'Example', 'limit' => 5, 'show_retweets' => true);
		$instance = wp_parse_args((array) $instance, $defaults);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('username'); ?>">Your Twitter Username:</label>
			<input id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" value="<?php echo $instance['username']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('limit'); ?>">Number of Tweets to show:</label>
			<input id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" value="<?php echo $instance['limit']; ?>" />
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked($instance['show_retweets'], true); ?> id="<?php echo $this->get_field_id('show_retweets'); ?>" name="<?php echo $this->get_field_name('show_retweets'); ?>" />
			<label for="<?php echo $this->get_field_id('show_retweets'); ?>">Include retweets?</label>
		</p>
		<?php
	}

}