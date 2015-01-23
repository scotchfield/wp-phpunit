<?php

/**
 * @group formatting
 */
class Test_Formatting extends WP_UnitTestCase {

	/**
	 * @ticket 11008
	 * @covers ::wpautop
	 */
	function test_wpautop_first_post() {
		$expected = '<p>Welcome to WordPress!  This post contains important information.  After you read it, you can make it private to hide it from visitors but still have the information handy for future reference.</p>
<p>First things first:</p>
<ul>
<li><a href="%1$s" title="Subscribe to the WordPress mailing list for Release Notifications">Subscribe to the WordPress mailing list for release notifications</a></li>
</ul>
<p>As a subscriber, you will receive an email every time an update is available (and only then).  This will make it easier to keep your site up to date, and secure from evildoers.<br />
When a new version is released, <a href="%2$s" title="If you are already logged in, this will take you directly to the Dashboard">log in to the Dashboard</a> and follow the instructions.<br />
Upgrading is a couple of clicks!</p>
<p>Then you can start enjoying the WordPress experience:</p>
<ul>
<li>Edit your personal information at <a href="%3$s" title="Edit settings like your password, your display name and your contact information">Users &#8250; Your Profile</a></li>
<li>Start publishing at <a href="%4$s" title="Create a new post">Posts &#8250; Add New</a> and at <a href="%5$s" title="Create a new page">Pages &#8250; Add New</a></li>
<li>Browse and install plugins at <a href="%6$s" title="Browse and install plugins at the official WordPress repository directly from your Dashboard">Plugins &#8250; Add New</a></li>
<li>Browse and install themes at <a href="%7$s" title="Browse and install themes at the official WordPress repository directly from your Dashboard">Appearance &#8250; Add New Themes</a></li>
<li>Modify and prettify your website&#8217;s links at <a href="%8$s" title="For example, select a link structure like: http://example.com/1999/12/post-name">Settings &#8250; Permalinks</a></li>
<li>Import content from another system or WordPress site at <a href="%9$s" title="WordPress comes with importers for the most common publishing systems">Tools &#8250; Import</a></li>
<li>Find answers to your questions at the <a href="%10$s" title="The official WordPress documentation, maintained by the WordPress community">WordPress Codex</a></li>
</ul>
<p>To keep this post for reference, <a href="%11$s" title="Click to edit the content and settings of this post">click to edit it</a>, go to the Publish box and change its Visibility from Public to Private.</p>
<p>Thank you for selecting WordPress.  We wish you happy publishing!</p>
<p>PS.  Not yet subscribed for update notifications?  <a href="%1$s" title="Subscribe to the WordPress mailing list for Release Notifications">Do it now!</a></p>
';
		$test_data = '
Welcome to WordPress!  This post contains important information.  After you read it, you can make it private to hide it from visitors but still have the information handy for future reference.

First things first:
<ul>
<li><a href="%1$s" title="Subscribe to the WordPress mailing list for Release Notifications">Subscribe to the WordPress mailing list for release notifications</a></li>
</ul>
As a subscriber, you will receive an email every time an update is available (and only then).  This will make it easier to keep your site up to date, and secure from evildoers.
When a new version is released, <a href="%2$s" title="If you are already logged in, this will take you directly to the Dashboard">log in to the Dashboard</a> and follow the instructions.
Upgrading is a couple of clicks!

Then you can start enjoying the WordPress experience:
<ul>
<li>Edit your personal information at <a href="%3$s" title="Edit settings like your password, your display name and your contact information">Users &#8250; Your Profile</a></li>
<li>Start publishing at <a href="%4$s" title="Create a new post">Posts &#8250; Add New</a> and at <a href="%5$s" title="Create a new page">Pages &#8250; Add New</a></li>
<li>Browse and install plugins at <a href="%6$s" title="Browse and install plugins at the official WordPress repository directly from your Dashboard">Plugins &#8250; Add New</a></li>
<li>Browse and install themes at <a href="%7$s" title="Browse and install themes at the official WordPress repository directly from your Dashboard">Appearance &#8250; Add New Themes</a></li>
<li>Modify and prettify your website&#8217;s links at <a href="%8$s" title="For example, select a link structure like: http://example.com/1999/12/post-name">Settings &#8250; Permalinks</a></li>
<li>Import content from another system or WordPress site at <a href="%9$s" title="WordPress comes with importers for the most common publishing systems">Tools &#8250; Import</a></li>
<li>Find answers to your questions at the <a href="%10$s" title="The official WordPress documentation, maintained by the WordPress community">WordPress Codex</a></li>
</ul>
To keep this post for reference, <a href="%11$s" title="Click to edit the content and settings of this post">click to edit it</a>, go to the Publish box and change its Visibility from Public to Private.

Thank you for selecting WordPress.  We wish you happy publishing!

PS.  Not yet subscribed for update notifications?  <a href="%1$s" title="Subscribe to the WordPress mailing list for Release Notifications">Do it now!</a>
';

		// On windows environments, the EOL-style is \r\n
		$expected = str_replace( "\r\n", "\n", $expected );

		$this->assertEquals( $expected, wpautop( $test_data ) );
	}

	/**
	 * wpautop() Should not alter the contents of "<pre>" elements
	 *
	 * @ticket 19855
	 * @covers ::wpautop
	 */
	public function test_wpautop_skip_pre_elements() {
		$code = file_get_contents( DIR_TESTDATA . '/formatting/sizzle.js' );
		$code = str_replace( "\r", '', $code );
		$code = htmlentities( $code );

		// Not wrapped in <p> tags
		$str = "<pre>$code</pre>";
		$this->assertEquals( $str, trim( wpautop( $str ) ) );

		// Text before/after is wrapped in <p> tags
		$str = "Look at this code\n\n<pre>$code</pre>\n\nIsn't that cool?";

		// Expected text after wpautop
		$expected = '<p>Look at this code</p>' . "\n<pre>" . $code . "</pre>\n" . '<p>Isn\'t that cool?</p>';
		$this->assertEquals( $expected, trim( wpautop( $str ) ) );

		// Make sure HTML breaks are maintained if manually inserted
		$str = "Look at this code\n\n<pre>Line1<br />Line2<br>Line3<br/>Line4\nActual Line 2\nActual Line 3</pre>\n\nCool, huh?";
		$expected = "<p>Look at this code</p>\n<pre>Line1<br />Line2<br>Line3<br/>Line4\nActual Line 2\nActual Line 3</pre>\n<p>Cool, huh?</p>";
		$this->assertEquals( $expected, trim( wpautop( $str ) ) );
	}

	/**
	 * wpautop() Should not add <br/> to "<input>" elements
	 *
	 * @ticket 16456
	 * @covers ::wpautop
	 */
	public function test_wpautop_skip_input_elements() {
		$str = 'Username: <input type="text" id="username" name="username" /><br />Password: <input type="password" id="password1" name="password1" />';
		$this->assertEquals( "<p>$str</p>", trim( wpautop( $str ) ) );
	}

	/**
	 * wpautop() Should not add <p> and <br/> around <source> and <track>
	 *
	 * @ticket 26864
	 * @covers ::wpautop
	 */
	public function test_wpautop_source_track_elements() {
		$content = "Paragraph one.\n\n" .
			'<video class="wp-video-shortcode" id="video-0-1" width="640" height="360" preload="metadata" controls="controls">
				<source type="video/mp4" src="http://domain.tld/wp-content/uploads/2013/12/xyz.mp4" />
				<!-- WebM/VP8 for Firefox4, Opera, and Chrome -->
				<source type="video/webm" src="myvideo.webm" />
				<!-- Ogg/Vorbis for older Firefox and Opera versions -->
				<source type="video/ogg" src="myvideo.ogv" />
				<!-- Optional: Add subtitles for each language -->
				<track kind="subtitles" src="subtitles.srt" srclang="en" />
				<!-- Optional: Add chapters -->
				<track kind="chapters" src="chapters.srt" srclang="en" />
				<a href="http://domain.tld/wp-content/uploads/2013/12/xyz.mp4">http://domain.tld/wp-content/uploads/2013/12/xyz.mp4</a>
			</video>' .
			"\n\nParagraph two.";

		$content2 = "Paragraph one.\n\n" .
			'<video class="wp-video-shortcode" id="video-0-1" width="640" height="360" preload="metadata" controls="controls">

			<source type="video/mp4" src="http://domain.tld/wp-content/uploads/2013/12/xyz.mp4" />

			<!-- WebM/VP8 for Firefox4, Opera, and Chrome -->
			<source type="video/webm" src="myvideo.webm" />

			<!-- Ogg/Vorbis for older Firefox and Opera versions -->
			<source type="video/ogg" src="myvideo.ogv" />

			<!-- Optional: Add subtitles for each language -->
			<track kind="subtitles" src="subtitles.srt" srclang="en" />

			<!-- Optional: Add chapters -->
			<track kind="chapters" src="chapters.srt" srclang="en" />

			<a href="http://domain.tld/wp-content/uploads/2013/12/xyz.mp4">http://domain.tld/wp-content/uploads/2013/12/xyz.mp4</a>

			</video>' .
			"\n\nParagraph two.";

		$expected = "<p>Paragraph one.</p>\n" . // line breaks only after <p>
			'<p><video class="wp-video-shortcode" id="video-0-1" width="640" height="360" preload="metadata" controls="controls">' .
			'<source type="video/mp4" src="http://domain.tld/wp-content/uploads/2013/12/xyz.mp4" />' .
			'<!-- WebM/VP8 for Firefox4, Opera, and Chrome -->' .
			'<source type="video/webm" src="myvideo.webm" />' .
			'<!-- Ogg/Vorbis for older Firefox and Opera versions -->' .
			'<source type="video/ogg" src="myvideo.ogv" />' .
			'<!-- Optional: Add subtitles for each language -->' .
			'<track kind="subtitles" src="subtitles.srt" srclang="en" />' .
			'<!-- Optional: Add chapters -->' .
			'<track kind="chapters" src="chapters.srt" srclang="en" />' .
			'<a href="http://domain.tld/wp-content/uploads/2013/12/xyz.mp4">' .
			"http://domain.tld/wp-content/uploads/2013/12/xyz.mp4</a></video></p>\n" .
			'<p>Paragraph two.</p>';

		// When running the content through wpautop() from wp_richedit_pre()
		$shortcode_content = "Paragraph one.\n\n" .
			'[video width="720" height="480" mp4="http://domain.tld/wp-content/uploads/2013/12/xyz.mp4"]
			<!-- WebM/VP8 for Firefox4, Opera, and Chrome -->
			<source type="video/webm" src="myvideo.webm" />
			<!-- Ogg/Vorbis for older Firefox and Opera versions -->
			<source type="video/ogg" src="myvideo.ogv" />
			<!-- Optional: Add subtitles for each language -->
			<track kind="subtitles" src="subtitles.srt" srclang="en" />
			<!-- Optional: Add chapters -->
			<track kind="chapters" src="chapters.srt" srclang="en" />
			[/video]' .
			"\n\nParagraph two.";

		$shortcode_expected = "<p>Paragraph one.</p>\n" . // line breaks only after <p>
			'<p>[video width="720" height="480" mp4="http://domain.tld/wp-content/uploads/2013/12/xyz.mp4"]' .
			'<!-- WebM/VP8 for Firefox4, Opera, and Chrome --><source type="video/webm" src="myvideo.webm" />' .
			'<!-- Ogg/Vorbis for older Firefox and Opera versions --><source type="video/ogg" src="myvideo.ogv" />' .
			'<!-- Optional: Add subtitles for each language --><track kind="subtitles" src="subtitles.srt" srclang="en" />' .
			'<!-- Optional: Add chapters --><track kind="chapters" src="chapters.srt" srclang="en" />' .
			"[/video]</p>\n" .
			'<p>Paragraph two.</p>';

		$this->assertEquals( $expected, trim( wpautop( $content ) ) );
		$this->assertEquals( $expected, trim( wpautop( $content2 ) ) );
		$this->assertEquals( $shortcode_expected, trim( wpautop( $shortcode_content ) ) );
	}

	/**
	 * wpautop() Should not add <p> and <br/> around <param> and <embed>
	 *
	 * @ticket 26864
	 * @covers ::wpautop
	 */
	public function test_wpautop_param_embed_elements() {
		$content1 = '
Paragraph one.

<object width="400" height="224" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0">
	<param name="src" value="http://domain.tld/wp-content/uploads/2013/12/xyz.swf" />
	<param name="allowfullscreen" value="true" />
	<param name="allowscriptaccess" value="always" />
	<param name="overstretch" value="true" />
	<param name="flashvars" value="isDynamicSeeking=true" />

	<embed width="400" height="224" type="application/x-shockwave-flash" src="http://domain.tld/wp-content/uploads/2013/12/xyz.swf" wmode="direct" seamlesstabbing="true" allowfullscreen="true" overstretch="true" flashvars="isDynamicSeeking=true" />
</object>

Paragraph two.';

		$expected1 = "<p>Paragraph one.</p>\n" . // line breaks only after <p>
			'<p><object width="400" height="224" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0">' .
			'<param name="src" value="http://domain.tld/wp-content/uploads/2013/12/xyz.swf" />' .
			'<param name="allowfullscreen" value="true" />' .
			'<param name="allowscriptaccess" value="always" />' .
			'<param name="overstretch" value="true" />' .
			'<param name="flashvars" value="isDynamicSeeking=true" />' .
			'<embed width="400" height="224" type="application/x-shockwave-flash" src="http://domain.tld/wp-content/uploads/2013/12/xyz.swf" wmode="direct" seamlesstabbing="true" allowfullscreen="true" overstretch="true" flashvars="isDynamicSeeking=true" />' .
			"</object></p>\n" .
			'<p>Paragraph two.</p>';

		$content2 = '
Paragraph one.

<div class="video-player" id="x-video-0">
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="640" height="360" id="video-0" standby="Standby text">
  <param name="movie" value="http://domain.tld/wp-content/uploads/2013/12/xyz.swf" />
  <param name="quality" value="best" />

  <param name="seamlesstabbing" value="true" />
  <param name="allowfullscreen" value="true" />
  <param name="allowscriptaccess" value="always" />
  <param name="overstretch" value="true" />

  <!--[if !IE]--><object type="application/x-shockwave-flash" data="http://domain.tld/wp-content/uploads/2013/12/xyz.swf" width="640" height="360" standby="Standby text">
    <param name="quality" value="best" />

    <param name="seamlesstabbing" value="true" />
    <param name="allowfullscreen" value="true" />
    <param name="allowscriptaccess" value="always" />
    <param name="overstretch" value="true" />
  </object><!--<![endif]-->
</object></div>

Paragraph two.';

		$expected2 = "<p>Paragraph one.</p>\n" . // line breaks only after block tags
			'<div class="video-player" id="x-video-0">' . "\n" .
			'<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="640" height="360" id="video-0" standby="Standby text">' .
			'<param name="movie" value="http://domain.tld/wp-content/uploads/2013/12/xyz.swf" />' .
			'<param name="quality" value="best" />' .
			'<param name="seamlesstabbing" value="true" />' .
			'<param name="allowfullscreen" value="true" />' .
			'<param name="allowscriptaccess" value="always" />' .
			'<param name="overstretch" value="true" />' .
			'<!--[if !IE]--><object type="application/x-shockwave-flash" data="http://domain.tld/wp-content/uploads/2013/12/xyz.swf" width="640" height="360" standby="Standby text">' .
			'<param name="quality" value="best" />' .
			'<param name="seamlesstabbing" value="true" />' .
			'<param name="allowfullscreen" value="true" />' .
			'<param name="allowscriptaccess" value="always" />' .
			'<param name="overstretch" value="true" /></object><!--<![endif]-->' .
			"</object></div>\n" .
			'<p>Paragraph two.</p>';

		$this->assertEquals( $expected1, trim( wpautop( $content1 ) ) );
		$this->assertEquals( $expected2, trim( wpautop( $content2 ) ) );
	}

	/**
	 * wpautop() Should not add <br/> to "<select>" or "<option>" elements
	 *
	 * @ticket 22230
	 * @covers ::wpautop
	 */
	public function test_wpautop_skip_select_option_elements() {
		$str = 'Country: <select id="state" name="state"><option value="1">Alabama</option><option value="2">Alaska</option><option value="3">Arizona</option><option value="4">Arkansas</option><option value="5">California</option></select>';
		$this->assertEquals( "<p>$str</p>", trim( wpautop( $str ) ) );
	}

	/**
	 * wpautop() should treat block level HTML elements as blocks.
	 *
	 * @ticket 27268
	 * @covers ::wpautop
	 */
	function test_wpautop_treats_block_level_elements_as_blocks() {
		$blocks = array(
			'table',
			'thead',
			'tfoot',
			'caption',
			'col',
			'colgroup',
			'tbody',
			'tr',
			'td',
			'th',
			'div',
			'dl',
			'dd',
			'dt',
			'ul',
			'ol',
			'li',
			'pre',
			'form',
			'map',
			'area',
			'address',
			'math',
			'style',
			'p',
			'h1',
			'h2',
			'h3',
			'h4',
			'h5',
			'h6',
			'hr',
			'fieldset',
			'legend',
			'section',
			'article',
			'aside',
			'hgroup',
			'header',
			'footer',
			'nav',
			'figure',
			'details',
			'menu',
			'summary',
		);

		$content = array();

		foreach ( $blocks as $block ) {
			$content[] = "<$block>foo</$block>";
		}

		$expected = join( "\n", $content );
		$content = join( "\n\n", $content ); // WS difference

		$this->assertEquals( $expected, trim( wpautop( $content ) ) );
	}

	/**
	 * wpautop() should autop a blockquote's contents but not the blockquote itself
	 *
	 * @ticket 27268
	 * @covers ::wpautop
	 */
	function test_wpautop_does_not_wrap_blockquotes_but_does_autop_their_contents() {
		$content  = "<blockquote>foo</blockquote>";
		$expected = "<blockquote><p>foo</p></blockquote>";

		$this->assertEquals( $expected, trim( wpautop( $content ) ) );
	}

	/**
	 * wpautop() should treat inline HTML elements as inline.
	 *
	 * @ticket 27268
	 * @covers ::wpautop
	 */
	function test_wpautop_treats_inline_elements_as_inline() {
		$inlines = array(
			'a',
			'em',
			'strong',
			'small',
			's',
			'cite',
			'q',
			'dfn',
			'abbr',
			'data',
			'time',
			'code',
			'var',
			'samp',
			'kbd',
			'sub',
			'sup',
			'i',
			'b',
			'u',
			'mark',
			'span',
			'del',
			'ins',
			'noscript',
			'select',
		);

		$content = $expected = array();

		foreach ( $inlines as $inline ) {
			$content[] = "<$inline>foo</$inline>";
			$expected[] = "<p><$inline>foo</$inline></p>";
		}

		$content = join( "\n\n", $content );
		$expected = join( "\n", $expected );

		$this->assertEquals( $expected, trim( wpautop( $content ) ) );
	}

	function balancetags_nestable_tags() {
		return array(
			array( 'blockquote' ), array( 'div' ), array( 'object' ), array( 'q' ), array( 'span' ),
		);
	}

	// This is a complete(?) listing of valid single/self-closing tags.
	function balancetags_single_tags() {
		return array(
			array( 'area' ), array( 'base' ), array( 'basefont' ), array( 'br' ), array( 'col' ), array( 'command' ),
			array( 'embed' ), array( 'frame' ), array( 'hr' ), array( 'img' ), array( 'input' ), array( 'isindex' ),
			array( 'link' ), array( 'meta' ), array( 'param' ), array( 'source' ),
		);
	}

	/**
	 * If a recognized valid single tag appears unclosed, it should get self-closed
	 *
	 * @ticket 1597
	 * @dataProvider balancetags_single_tags
	 * @covers ::balanceTags
	 */
	function test_balancetags_closes_unclosed_known_single_tags( $tag ) {
		$this->assertEquals( "<$tag />", balanceTags( "<$tag>", true ) );
	}

	/**
	 * If a recognized valid single tag is given a closing tag, the closing tag
	 *   should get removed and tag should be self-closed.
	 *
	 * @ticket 1597
	 * @dataProvider balancetags_single_tags
	 * @covers ::balanceTags
	 */
	function test_balancetags_closes_known_single_tags_having_closing_tag( $tag ) {
		$this->assertEquals( "<$tag />", balanceTags( "<$tag></$tag>", true ) );
	}

	/**
	 * @ticket 1597
	 * @covers ::balanceTags
	 */
	function test_balancetags_closes_unknown_single_tags_with_closing_tag() {

		$inputs = array(
			'<strong/>',
			'<em />',
			'<p class="main1"/>',
			'<p class="main2" />',
		);
		$expected = array(
			'<strong></strong>',
			'<em></em>',
			'<p class="main1"></p>',
			'<p class="main2"></p>',
		);

		foreach ( $inputs as $key => $input ) {
			$this->assertEquals( $expected[ $key ], balanceTags( $inputs[ $key ], true ) );
		}
	}

	/**
	 * @covers ::balanceTags
	 */
	function test_balancetags_closes_unclosed_single_tags_having_attributes() {
		$inputs = array(
			'<img src="/images/example.png">',
			'<input type="text" name="example">'
		);
		$expected = array(
			'<img src="/images/example.png"/>',
			'<input type="text" name="example"/>'
		);

		foreach ( $inputs as $key => $input ) {
			$this->assertEquals( $expected[ $key ], balanceTags( $inputs[ $key ], true ) );
		}
	}

	/**
	 * @covers ::balanceTags
	 */
	function test_balancetags_allows_validly_closed_single_tags() {
		$inputs = array(
			'<br />',
			'<hr />',
			'<img src="/images/example.png" />',
			'<input type="text" name="example" />'
		);

		foreach ( $inputs as $key => $input ) {
			$this->assertEquals( $inputs[ $key ], balanceTags( $inputs[ $key ], true ) );
		}
	}

	/**
	 * @dataProvider balancetags_nestable_tags
	 * @covers ::balanceTags
	 */
	function test_balancetags_balances_nestable_tags( $tag ) {
		$inputs = array(
			"<$tag>Test<$tag>Test</$tag>",
			"<$tag><$tag>Test",
			"<$tag>Test</$tag></$tag>",
		);
		$expected = array(
			"<$tag>Test<$tag>Test</$tag></$tag>",
			"<$tag><$tag>Test</$tag></$tag>",
			"<$tag>Test</$tag>",
		);

		foreach ( $inputs as $key => $input ) {
			$this->assertEquals( $expected[ $key ], balanceTags( $inputs[ $key ], true ) );
		}
	}

	/**
	 * @covers ::balanceTags
	 */
	function test_balancetags_allows_adjacent_nestable_tags() {
		$inputs = array(
			'<blockquote><blockquote>Example quote</blockquote></blockquote>',
			'<div class="container"><div>This is allowed></div></div>',
			'<span><span><span>Example in spans</span></span></span>',
			'<blockquote>Main quote<blockquote>Example quote</blockquote> more text</blockquote>',
			'<q><q class="inner-q">Inline quote</q></q>',
		);

		foreach ( $inputs as $key => $input ) {
			$this->assertEquals( $inputs[ $key ], balanceTags( $inputs[ $key ], true ) );
		}
	}

	/**
	 * @ticket 20401
	 * @covers ::balanceTags
	 */
	function test_balancetags_allows_immediately_nested_object_tags() {
		$object = '<object id="obj1"><param name="param1"/><object id="obj2"><param name="param2"/></object></object>';
		$this->assertEquals( $object, balanceTags( $object, true ) );
	}

	/**
	 * @covers ::balanceTags
	 */
	function test_balancetags_balances_nested_non_nestable_tags() {
		$inputs = array(
			'<b><b>This is bold</b></b>',
			'<b>Some text here <b>This is bold</b></b>',
		);
		$expected = array(
			'<b></b><b>This is bold</b>',
			'<b>Some text here </b><b>This is bold</b>',
		);

		foreach ( $inputs as $key => $input ) {
			$this->assertEquals( $expected[ $key ], balanceTags( $inputs[ $key ], true ) );
		}
	}

	/**
	 * @covers ::balanceTags
	 */
	function test_balancetags_fixes_improper_closing_tag_sequence() {
		$inputs = array(
			'<p>Here is a <strong class="part">bold <em>and emphasis</p></em></strong>',
			'<ul><li>Aaa</li><li>Bbb</ul></li>',
		);
		$expected = array(
			'<p>Here is a <strong class="part">bold <em>and emphasis</em></strong></p>',
			'<ul><li>Aaa</li><li>Bbb</li></ul>',
		);

		foreach ($inputs as $key => $input) {
			$this->assertEquals( $expected[ $key ], balanceTags( $inputs[ $key ], true ) );
		}
	}

	/**
	 * @covers ::balanceTags
	 */
	function test_balancetags_adds_missing_closing_tags() {
		$inputs = array(
			'<b><i>Test</b>',
			'<p>Test',
			'<p>Test test</em> test</p>',
			'</p>Test',
			'<p>Here is a <strong class="part">Test</p>',
		);
		$expected = array(
			'<b><i>Test</i></b>',
			'<p>Test</p>',
			'<p>Test test test</p>',
			'Test',
			'<p>Here is a <strong class="part">Test</strong></p>',
		);

		foreach ( $inputs as $key => $input ) {
			$this->assertEquals( $expected[ $key ], balanceTags( $inputs[ $key ], true ) );
		}
	}

	/**
	 * @covers ::balanceTags
	 */
	function test_balancetags_removes_extraneous_closing_tags() {
		$inputs = array(
			'<b>Test</b></b>',
			'<div>Test</div></div><div>Test',
			'<p>Test test</em> test</p>',
			'</p>Test',
		);
		$expected = array(
			'<b>Test</b>',
			'<div>Test</div><div>Test</div>',
			'<p>Test test test</p>',
			'Test',
		);

		foreach ( $inputs as $key => $input ) {
			$this->assertEquals( $expected[ $key ], balanceTags( $inputs[ $key ], true ) );
		}
	}

	/**
	 * @covers ::clean_pre
	 * @expectedDeprecated clean_pre
	 */
	function test_clean_pre_removes_self_closing_br_with_space() {
		$source = 'a b c\n<br />sldfj<br />';
		$res = 'a b c\nsldfj';

		$this->assertEquals( $res, clean_pre( $source ) );
	}

	/**
	 * @covers ::clean_pre
	 * @expectedDeprecated clean_pre
	 */
	function test_clean_pre_removes_self_closing_br_without_space() {
		$source = 'a b c\n<br/>sldfj<br/>';
		$res = 'a b c\nsldfj';
		$this->assertEquals( $res, clean_pre( $source ) );
	}

	/**
	 * I don't think this can ever happen in production;
	 * <br> is changed to <br /> elsewhere. Left in because
	 * that replacement shouldn't happen (what if you want
	 * HTML 4 output?).
	 *
	 * @covers ::clean_pre
	 * @expectedDeprecated clean_pre
	 */
	function test_clean_pre_removes_html_br() {
		$source = 'a b c\n<br>sldfj<br>';
		$res = 'a b c\nsldfj';
		$this->assertEquals( $res, clean_pre( $source ) );
	}

	/**
	 * @covers ::clean_pre
	 * @expectedDeprecated clean_pre
	 */
	function test_clean_pre_removes_p() {
		$source = "<p>isn't this exciting!</p><p>oh indeed!</p>";
		$res = "\nisn't this exciting!\noh indeed!";
		$this->assertEquals( $res, clean_pre( $source ) );
	}

	/**
	 * @covers ::convert_chars
	 */
	function test_convert_chars_replaces_windows1252_entities_with_unicode_ones() {
		$input = "&#130;&#131;&#132;&#133;&#134;&#135;&#136;&#137;&#138;&#139;&#140;&#145;&#146;&#147;&#148;&#149;&#150;&#151;&#152;&#153;&#154;&#155;&#156;&#159;";
		$output = "&#8218;&#402;&#8222;&#8230;&#8224;&#8225;&#710;&#8240;&#352;&#8249;&#338;&#8216;&#8217;&#8220;&#8221;&#8226;&#8211;&#8212;&#732;&#8482;&#353;&#8250;&#339;&#376;";
		$this->assertEquals( $output, convert_chars( $input ) );
	}

	/**
	 * @ticket 20503
	 * @covers ::convert_chars
	 */
	function test_convert_chars_replaces_latin_letter_z_with_caron() {
		$input = "&#142;&#158;";
		$output = "&#381;&#382;";
		$this->assertEquals( $output, convert_chars( $input ) );
	}

	/**
	 * @covers ::convert_chars
	 */
	function test_convert_chars_converts_html_br_and_hr_to_the_xhtml_self_closing_variety() {
		$inputs = array(
			"abc <br> lol <br />" => "abc <br /> lol <br />",
			"<br> ho ho <hr>"     => "<br /> ho ho <hr />",
			"<hr><br>"            => "<hr /><br />"
			);
		foreach ( $inputs as $input => $expected ) {
			$this->assertEquals( $expected, convert_chars( $input ) );
		}
	}

	/**
	 * @covers ::convert_chars
	 */
	function test_convert_chars_escapes_lone_ampersands() {
		$this->assertEquals( "at&#038;t", convert_chars( "at&t" ) );
	}

	/**
	 * @covers ::convert_chars
	 */
	function test_convert_chars_removes_category_and_title_metadata_tags() {
		$this->assertEquals( "", convert_chars( "<title><div class='lol'>abc</div></title><category>a</category>" ) );
	}

	/**
	 * Unpatched, this test passes only when Europe/London is not observing DST.
	 *
	 * @covers ::get_date_from_gmt
	 * @group datetime
	 * @ticket 20328
	 */
	function test_get_date_from_gmt_outside_of_dst() {
		update_option( 'timezone_string', 'Europe/London' );
		$gmt = $local = '2012-01-01 12:34:56';
		$this->assertEquals( $local, get_date_from_gmt( $gmt ) );
	}

	/**
	 * Unpatched, this test passes only when Europe/London is observing DST.
	 *
	 * @covers ::get_date_from_gmt
	 * @group datetime
	 * @ticket 20328
	 */
	function test_get_date_from_gmt_during_dst() {
		update_option( 'timezone_string', 'Europe/London' );
		$gmt   = '2012-06-01 12:34:56';
		$local = '2012-06-01 13:34:56';
		$this->assertEquals( $local, get_date_from_gmt( $gmt ) );
	}

	/**
	 * @covers ::get_date_from_gmt
	 * @group datetime
	 * @ticket 20328
	 */
	function test_get_gmt_from_date_outside_of_dst() {
		update_option( 'timezone_string', 'Europe/London' );
		$local = $gmt = '2012-01-01 12:34:56';
		$this->assertEquals( $gmt, get_gmt_from_date( $local ) );
	}

	/**
	 * @covers ::get_date_from_gmt
	 * @group datetime
	 * @ticket 20328
	 */
	function test_get_gmt_from_date_during_dst() {
		update_option( 'timezone_string', 'Europe/London' );
		$local = '2012-06-01 12:34:56';
		$gmt = '2012-06-01 11:34:56';
		$this->assertEquals( $gmt, get_gmt_from_date( $local ) );
	}

	/*
	 * Get test data from files, one test per line.
	 * Comments start with "###".
	*/
	function ent2ncr_entities() {
		$entities = file( DIR_TESTDATA . '/formatting/entities.txt' );
		$data_provided = array();
		foreach ( $entities as $line ) {
			// comment
			$commentpos = strpos( $line, "###" );
			if ( false !== $commentpos ) {
				$line = trim( substr( $line, 0, $commentpos ) );
				if ( ! $line )
					continue;
			}
			$data_provided[] = array_map( 'trim', explode( '|', $line ) );
		}
		return $data_provided;
	}

	/**
	 * @covers ::ent2ncr
	 * @dataProvider ent2ncr_entities
	 */
	function test_ent2ncr_converts_named_entities_to_numeric_character_references( $entity, $ncr ) {
		$entity = '&' . $entity . ';';
		$ncr = '&#' . $ncr . ';';
		$this->assertEquals( $ncr, ent2ncr( $entity ), $entity );
	}

	/**
	 * @covers ::esc_attr
	 */
	function test_esc_attr_quotes() {
		$attr = '"double quotes"';
		$this->assertEquals( '&quot;double quotes&quot;', esc_attr( $attr ) );

		$attr = "'single quotes'";
		$this->assertEquals( '&#039;single quotes&#039;', esc_attr( $attr ) );

		$attr = "'mixed' " . '"quotes"';
		$this->assertEquals( '&#039;mixed&#039; &quot;quotes&quot;', esc_attr( $attr ) );

		// Handles double encoding?
		$attr = '"double quotes"';
		$this->assertEquals( '&quot;double quotes&quot;', esc_attr( esc_attr( $attr ) ) );

		$attr = "'single quotes'";
		$this->assertEquals( '&#039;single quotes&#039;', esc_attr( esc_attr( $attr ) ) );

		$attr = "'mixed' " . '"quotes"';
		$this->assertEquals( '&#039;mixed&#039; &quot;quotes&quot;', esc_attr( esc_attr( $attr ) ) );
	}

	/**
	 * @covers ::esc_attr
	 */
	function test_esc_attr_amp() {
		$out = esc_attr( 'foo & bar &baz; &apos;' );
		$this->assertEquals( "foo &amp; bar &amp;baz; &apos;", $out );
	}

	/**
	 * @covers ::esc_html
	 */
	function test_esc_html_basics() {
		// Simple string
		$html = "The quick brown fox.";
		$this->assertEquals( $html, esc_html( $html ) );

		// URL with &
		$html = "http://localhost/trunk/wp-login.php?action=logout&_wpnonce=cd57d75985";
		$escaped = "http://localhost/trunk/wp-login.php?action=logout&amp;_wpnonce=cd57d75985";
		$this->assertEquals( $escaped, esc_html( $html ) );

		// SQL query
		$html = "SELECT meta_key, meta_value FROM wp_trunk_sitemeta WHERE meta_key IN ('site_name', 'siteurl', 'active_sitewide_plugins', '_site_transient_timeout_theme_roots', '_site_transient_theme_roots', 'site_admins', 'can_compress_scripts', 'global_terms_enabled') AND site_id = 1";
		$escaped = "SELECT meta_key, meta_value FROM wp_trunk_sitemeta WHERE meta_key IN (&#039;site_name&#039;, &#039;siteurl&#039;, &#039;active_sitewide_plugins&#039;, &#039;_site_transient_timeout_theme_roots&#039;, &#039;_site_transient_theme_roots&#039;, &#039;site_admins&#039;, &#039;can_compress_scripts&#039;, &#039;global_terms_enabled&#039;) AND site_id = 1";
		$this->assertEquals( $escaped, esc_html( $html ) );
	}

	/**
	 * @covers ::esc_html
	 */
	function test_esc_html_escapes_ampersands() {
		$source = "penn & teller & at&t";
		$res = "penn &amp; teller &amp; at&amp;t";
		$this->assertEquals( $res, esc_html($source) );
	}

	/**
	 * @covers ::esc_html
	 */
	function test_esc_html_escapes_greater_and_less_than() {
		$source = "this > that < that <randomhtml />";
		$res = "this &gt; that &lt; that &lt;randomhtml /&gt;";
		$this->assertEquals( $res, esc_html($source) );
	}

	/**
	 * @covers ::esc_html
	 */
	function test_esc_htmlignores_existing_entities() {
		$source = '&#038; &#x00A3; &#x22; &amp;';
		$res = '&amp; &#xA3; &quot; &amp;';
		$this->assertEquals( $res, esc_html($source) );
	}

	function _esc_textarea_charset_iso_8859_1() {
		return 'iso-8859-1';
	}

	/*
	 * Only fails in PHP 5.4 onwards
	 * @covers :;esc_textarea
	 * @ticket 23688
	 */
	function test_esc_textarea_charset_iso_8859_1() {
		add_filter( 'pre_option_blog_charset', array( $this, '_esc_textarea_charset_iso_8859_1' ) );
		$iso8859_1 = 'Fran' .chr(135) .'ais';
		$this->assertEquals( $iso8859_1, esc_textarea( $iso8859_1 ) );
		remove_filter( 'pre_option_blog_charset', array( $this, '_esc_textarea_charset_iso_8859_1' ) );
	}

	function _esc_textarea_charset_utf_8() {
		return 'UTF-8';
	}

	/*
	 * @covers :;esc_textarea
	 * @ticket 23688
	 */
	function test_esc_textarea_charset_utf_8() {
		add_filter( 'pre_option_blog_charset', array( $this, '_esc_textarea_charset_utf_8' ) );
		$utf8 = 'Fran' .chr(195) . chr(167) .'ais';
		$this->assertEquals( $utf8, esc_textarea( $utf8 ) );
		remove_filter( 'pre_option_blog_charset', array( $this, '_esc_textarea_charset_utf_8' ) );
	}

	/*
	 * @covers :;esc_url
	 */
	function test_esc_url_spaces() {
		$this->assertEquals('http://example.com/MrWordPress', esc_url('http://example.com/Mr WordPress'));
		$this->assertEquals('http://example.com/Mr%20WordPress', esc_url('http://example.com/Mr%20WordPress'));
	}

	/*
	 * @covers :;esc_url
	 */
	function test_esc_url_bad_characters() {
		$this->assertEquals('http://example.com/watchthelinefeedgo', esc_url('http://example.com/watchthelinefeed%0Ago'));
		$this->assertEquals('http://example.com/watchthelinefeedgo', esc_url('http://example.com/watchthelinefeed%0ago'));
		$this->assertEquals('http://example.com/watchthecarriagereturngo', esc_url('http://example.com/watchthecarriagereturn%0Dgo'));
		$this->assertEquals('http://example.com/watchthecarriagereturngo', esc_url('http://example.com/watchthecarriagereturn%0dgo'));
		//Nesting Checks
		$this->assertEquals('http://example.com/watchthecarriagereturngo', esc_url('http://example.com/watchthecarriagereturn%0%0ddgo'));
		$this->assertEquals('http://example.com/watchthecarriagereturngo', esc_url('http://example.com/watchthecarriagereturn%0%0DDgo'));
		$this->assertEquals('http://example.com/', esc_url('http://example.com/%0%0%0DAD'));
		$this->assertEquals('http://example.com/', esc_url('http://example.com/%0%0%0ADA'));
		$this->assertEquals('http://example.com/', esc_url('http://example.com/%0%0%0DAd'));
		$this->assertEquals('http://example.com/', esc_url('http://example.com/%0%0%0ADa'));
	}

	/*
	 * @covers :;esc_url
	 */
	function test_esc_url_relative() {
		$this->assertEquals('/example.php', esc_url('/example.php'));
		$this->assertEquals('example.php', esc_url('example.php'));
		$this->assertEquals('#fragment', esc_url('#fragment'));
		$this->assertEquals('?foo=bar', esc_url('?foo=bar'));
	}

	/*
	 * @covers :;esc_url
	 */
	function test_esc_url_protocol() {
		$this->assertEquals('http://example.com', esc_url('http://example.com'));
		$this->assertEquals('', esc_url('nasty://example.com/'));
	}

	/**
	 * @covers ::esc_url
	 * @ticket 23187
	 */
	function test_esc_url_protocol_case() {
		$this->assertEquals('http://example.com', esc_url('HTTP://example.com'));
		$this->assertEquals('http://example.com', esc_url('Http://example.com'));
	}

	/*
	 * @covers :;esc_url
	 */
	function test_esc_url_display_extras() {
		$this->assertEquals('http://example.com/&#039;quoted&#039;', esc_url('http://example.com/\'quoted\''));
		$this->assertEquals('http://example.com/\'quoted\'', esc_url('http://example.com/\'quoted\'',null,'notdisplay'));
	}

	/*
	 * @covers :;esc_url
	 */
	function test_esc_url_non_ascii() {
		$this->assertEquals( 'http://example.org/баба', esc_url( 'http://example.org/баба' ) );
		$this->assertEquals( 'http://баба.org/баба', esc_url( 'http://баба.org/баба' ) );
		$this->assertEquals( 'http://müller.com/', esc_url( 'http://müller.com/' ) );
	}

	/*
	 * @covers :;esc_url
	 */
	function test_esc_url_feed() {
		$this->assertEquals( '', esc_url( 'feed:javascript:alert(1)' ) );
		$this->assertEquals( '', esc_url( 'feed:javascript:feed:alert(1)' ) );
		$this->assertEquals( '', esc_url( 'feed:feed:javascript:alert(1)' ) );
		$this->assertEquals( 'feed:feed:alert(1)', esc_url( 'feed:feed:alert(1)' ) );
		$this->assertEquals( 'feed:http://wordpress.org/feed/', esc_url( 'feed:http://wordpress.org/feed/' ) );
	}

	/**
	 * @covers ::esc_url
	 * @ticket 21974
	 */
	function test_esc_url_protocol_relative_with_colon() {
		$this->assertEquals( '//example.com/foo?foo=abc:def', esc_url( '//example.com/foo?foo=abc:def' ) );
	}

	/**
	 * URL Content Data Provider
	 *
	 * array ( input_txt, converted_output_txt )
	 */
	public function get_url_in_content_get_input_output() {
		return array (
			array (
				"",
				false
			), //empty content
			array (
				"<div>NO URL CONTENT</div>",
				false
			), //no URLs
			array (
				'<div href="/relative.php">NO URL CONTENT</div>',
				false
			), // ignore none link elements
			array (
				'ABC<div><a href="/relative.php">LINK</a> CONTENT</div>',
				"/relative.php"
			), // single link
			array (
				'ABC<div><a href="/relative.php">LINK</a> CONTENT <a href="/suppress.php">LINK</a></div>',
				"/relative.php"
			), // multiple links
			array (
				'ABC<div><a href="http://example.com/Mr%20WordPress 2">LINK</a> CONTENT </div>',
				"http://example.com/Mr%20WordPress2"
			), // escape link
		);
	}

	/**
	 * Validate the get_url_in_content function
	 * @covers ::get_url_in_content
	 * @dataProvider get_url_in_content_get_input_output
	 */
	function test_get_url_in_content( $in_str, $exp_str ) {
		$this->assertEquals($exp_str, get_url_in_content( $in_str ) );
	}

	/**
	 * @covers ::wp_html_excerpt
	 */
	function test_wp_html_excerpt_simple() {
		$this->assertEquals("Baba", wp_html_excerpt("Baba told me not to come", 4));
	}

	/**
	 * @covers ::wp_html_excerpt
	 */
	function test_wp_html_excerpt_html() {
		$this->assertEquals("Baba", wp_html_excerpt("<a href='http://baba.net/'>Baba</a> told me not to come", 4));
	}

	/**
	 * @covers ::wp_html_excerpt
	 */
	function test_wp_html_excerpt_entities() {
		$this->assertEquals("Baba", wp_html_excerpt("Baba &amp; Dyado", 8));
		$this->assertEquals("Baba", wp_html_excerpt("Baba &#038; Dyado", 8));
		$this->assertEquals("Baba &amp; D", wp_html_excerpt("Baba &amp; Dyado", 12));
		$this->assertEquals("Baba &amp; Dyado", wp_html_excerpt("Baba &amp; Dyado", 100));
	}

	/**
	 * @covers ::is_email
	 */
	function test_is_email_returns_the_email_address_if_it_is_valid() {
		$data = array(
			"bob@example.com",
			"phil@example.info",
			"ace@204.32.222.14",
			"kevin@many.subdomains.make.a.happy.man.edu"
			);
		foreach ( $data as $datum ) {
			$this->assertEquals( $datum, is_email( $datum ), $datum );
		}
	}

	/**
	 * @covers ::is_email
	 */
	function test_is_email_returns_false_if_given_an_invalid_email_address() {
		$data = array(
			"khaaaaaaaaaaaaaaan!",
			'http://bob.example.com/',
			"sif i'd give u it, spamer!1",
			"com.exampleNOSPAMbob",
			"bob@your mom"
			);
		foreach ($data as $datum) {
			$this->assertFalse(is_email($datum), $datum);
		}
	}

	/*
	 * Decodes text in RFC2047 "Q"-encoding, e.g.
	 * =?iso-8859-1?q?this=20is=20some=20text?=
	 * @covers ::wp_iso_descrambler
	*/
    function test_wp_iso_descrambler_decodes_iso_8859_1_rfc2047_q_encoding() {
        $this->assertEquals("this is some text", wp_iso_descrambler("=?iso-8859-1?q?this=20is=20some=20text?="));
    }

	/**
	 * @covers ::esc_js
	 */
	function test_esc_js_simple() {
		$out = esc_js('foo bar baz();');
		$this->assertEquals('foo bar baz();', $out);
	}

	/**
	 * @covers ::esc_js
	 */
	function test_esc_js_quotes() {
		$out = esc_js('foo "bar" \'baz\'');
		// does it make any sense to change " into &quot;?  Why not \"?
		$this->assertEquals("foo &quot;bar&quot; \'baz\'", $out);
	}

	/**
	 * @covers ::esc_js
	 */
	function test_esc_js_backslash() {
		$bs = '\\';
		$out = esc_js('foo '.$bs.'t bar '.$bs.$bs.' baz');
		// \t becomes t - bug?
		$this->assertEquals('foo t bar '.$bs.$bs.' baz', $out);
	}

	/**
	 * @covers ::esc_js
	 */
	function test_esc_js_amp() {
		$out = esc_js('foo & bar &baz; &apos;');
		$this->assertEquals("foo &amp; bar &amp;baz; &apos;", $out);
	}

	/**
	 * @covers ::esc_js
	 */
	function test_esc_js_quote_entity() {
		$out = esc_js('foo &#x27; bar &#39; baz &#x26;');
		$this->assertEquals("foo \\' bar \\' baz &amp;", $out);
	}

	/**
	 * @covers ::esc_js
	 */
	function test_esc_js_no_carriage_return() {
		$out = esc_js("foo\rbar\nbaz\r");
		// \r is stripped
		$this->assertequals("foobar\\nbaz", $out);
	}

	/**
	 * @covers ::esc_js
	 */
	function test_esc_js_rn() {
		$out = esc_js("foo\r\nbar\nbaz\r\n");
		// \r is stripped
		$this->assertequals("foo\\nbar\\nbaz\\n", $out);
	}

	/**
	 * @covers ::like_escape
	 * @ticket 10041
	 * @expectedDeprecated like_escape
	 */
	function test_like_escape() {

		$inputs = array(
			'howdy%', //Single Percent
			'howdy_', //Single Underscore
			'howdy\\', //Single slash
			'howdy\\howdy%howdy_', //The works
		);
		$expected = array(
			"howdy\\%",
			'howdy\\_',
			'howdy\\',
			'howdy\\howdy\\%howdy\\_'
		);

		foreach ($inputs as $key => $input) {
			$this->assertEquals($expected[$key], like_escape($input));
		}
	}

	/**
	 * Test Content DataProvider
	 *
	 * array ( input_txt, converted_output_txt)
	 */
	public function links_add_target_get_input_output() {
		return array (
			array (
				'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> END TEXT',
				null,
				null,
				'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC" target="_blank">LINK</a> HERE </div> END TEXT'
			),
			array (
				'MY CONTENT <div> SOME ADDITIONAL TEXT <A href="XYZ" src="ABC">LINK</A> HERE </div> END TEXT',
				null,
				null,
				'MY CONTENT <div> SOME ADDITIONAL TEXT <A href="XYZ" src="ABC" target="_blank">LINK</A> HERE </div> END TEXT'
			),
			array (
				'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <a href="XYZ"  >LINK</a>END TEXT',
				null,
				null,
				'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC" target="_blank">LINK</a> HERE </div> <a href="XYZ"   target="_blank">LINK</a>END TEXT'
			),
			array (
				'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span>END TEXT</span>',
				"_top",
				null,
				'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC" target="_top">LINK</a> HERE </div> <span>END TEXT</span>'
			),
			array (
				'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span>END TEXT</span>',
				"_top",
				array( 'span'),
				'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span target="_top">END TEXT</span>'
			),
			array (
				'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span>END TEXT</span>',
				"_top",
				array( 'SPAN'),
				'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span target="_top">END TEXT</span>'
			),
			array (
				'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span target="_top">END TEXT</span>',
				"_top",
				array( 'span', 'div'),
				'MY CONTENT <div target="_top"> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span target="_top">END TEXT</span>'
			),
			array (
				'MY CONTENT <div target=\'ABC\'> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span target="xyz">END TEXT</span>',
				"_top",
				array( 'span', 'div'),
				'MY CONTENT <div target="_top"> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span target="_top">END TEXT</span>'
			),
			array (
				'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span target="xyz" src="ABC">END TEXT</span>',
				"_top",
				array( 'span'),
				'MY CONTENT <div> SOME ADDITIONAL TEXT <a href="XYZ" src="ABC">LINK</a> HERE </div> <span src="ABC" target="_top">END TEXT</span>'
			),
		);
	}

	/**
	 * Validate the normalize_whitespace function
	 *
	 * @covers ::links_add_target
	 * @dataProvider links_add_target_get_input_output
	 */
	function test_links_add_target_normalize_whitespace( $content, $target, $tags, $exp_str ) {
		if ( true === is_null( $target ) ) {
			$this->assertEquals( $exp_str, links_add_target( $content ) );
		} elseif ( true === is_null( $tags ) ) {
			$this->assertEquals( $exp_str, links_add_target( $content, $target ) );
		} else {
			$this->assertEquals( $exp_str, links_add_target( $content, $target, $tags ) );
		}
	}

	/**
	 * @covers ::make_clickable
	 */
	function test_make_clickable_mailto_xss() {
		$in = 'testzzz@"STYLE="behavior:url(\'#default#time2\')"onBegin="alert(\'refresh-XSS\')"';
		$this->assertEquals($in, make_clickable($in));
	}

	/**
	 * @covers ::make_clickable
	 */
	function test_make_clickable_valid_mailto() {
		$valid_emails = array(
			'foo@example.com',
			'foo.bar@example.com',
			'Foo.Bar@a.b.c.d.example.com',
			'0@example.com',
			'foo@example-example.com',
			);
		foreach ($valid_emails as $email) {
			$this->assertEquals('<a href="mailto:'.$email.'">'.$email.'</a>', make_clickable($email));
		}
	}

	/**
	 * @covers ::make_clickable
	 */
	function test_make_clickable_invalid_mailto() {
		$invalid_emails = array(
			'foo',
			'foo@',
			'foo@@example.com',
			'@example.com',
			'foo @example.com',
			'foo@example',
			);
		foreach ($invalid_emails as $email) {
			$this->assertEquals($email, make_clickable($email));
		}
	}

	/**
	 * tests that make_clickable will not link trailing periods, commas and
	 * (semi-)colons in URLs with protocol (i.e. http://wordpress.org)
	 *
	 * @covers ::make_clickable
	 */
	function test_make_clickable_strip_trailing_with_protocol() {
		$urls_before = array(
			'http://wordpress.org/hello.html',
			'There was a spoon named http://wordpress.org. Alice!',
			'There was a spoon named http://wordpress.org, said Alice.',
			'There was a spoon named http://wordpress.org; said Alice.',
			'There was a spoon named http://wordpress.org: said Alice.',
			'There was a spoon named (http://wordpress.org) said Alice.'
			);
		$urls_expected = array(
			'<a href="http://wordpress.org/hello.html" rel="nofollow">http://wordpress.org/hello.html</a>',
			'There was a spoon named <a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a>. Alice!',
			'There was a spoon named <a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a>, said Alice.',
			'There was a spoon named <a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a>; said Alice.',
			'There was a spoon named <a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a>: said Alice.',
			'There was a spoon named (<a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a>) said Alice.'
			);

		foreach ($urls_before as $key => $url) {
			$this->assertEquals($urls_expected[$key], make_clickable($url));
		}
	}

	/**
	 * tests that make_clickable will not link trailing periods, commas and
	 * (semi-)colons in URLs with protocol (i.e. http://wordpress.org)
	 *
	 * @covers ::make_clickable
	 */
	function test_make_clickable_strip_trailing_with_protocol_nothing_afterwards() {
		$urls_before = array(
			'http://wordpress.org/hello.html',
			'There was a spoon named http://wordpress.org.',
			'There was a spoon named http://wordpress.org,',
			'There was a spoon named http://wordpress.org;',
			'There was a spoon named http://wordpress.org:',
			'There was a spoon named (http://wordpress.org)',
			'There was a spoon named (http://wordpress.org)x',
			);
		$urls_expected = array(
			'<a href="http://wordpress.org/hello.html" rel="nofollow">http://wordpress.org/hello.html</a>',
			'There was a spoon named <a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a>.',
			'There was a spoon named <a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a>,',
			'There was a spoon named <a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a>;',
			'There was a spoon named <a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a>:',
			'There was a spoon named (<a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a>)',
			'There was a spoon named (<a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a>)x',
			);

		foreach ($urls_before as $key => $url) {
			$this->assertEquals($urls_expected[$key], make_clickable($url));
		}
	}

	/**
	 * tests that make_clickable will not link trailing periods, commas and
	 * (semi-)colons in URLs without protocol (i.e. www.wordpress.org)
	 *
	 * @covers ::make_clickable
	 */
	function test_make_clickable_strip_trailing_without_protocol() {
		$urls_before = array(
			'www.wordpress.org',
			'There was a spoon named www.wordpress.org. Alice!',
			'There was a spoon named www.wordpress.org, said Alice.',
			'There was a spoon named www.wordpress.org; said Alice.',
			'There was a spoon named www.wordpress.org: said Alice.',
			'There was a spoon named www.wordpress.org) said Alice.'
			);
		$urls_expected = array(
			'<a href="http://www.wordpress.org" rel="nofollow">http://www.wordpress.org</a>',
			'There was a spoon named <a href="http://www.wordpress.org" rel="nofollow">http://www.wordpress.org</a>. Alice!',
			'There was a spoon named <a href="http://www.wordpress.org" rel="nofollow">http://www.wordpress.org</a>, said Alice.',
			'There was a spoon named <a href="http://www.wordpress.org" rel="nofollow">http://www.wordpress.org</a>; said Alice.',
			'There was a spoon named <a href="http://www.wordpress.org" rel="nofollow">http://www.wordpress.org</a>: said Alice.',
			'There was a spoon named <a href="http://www.wordpress.org" rel="nofollow">http://www.wordpress.org</a>) said Alice.'
			);

		foreach ($urls_before as $key => $url) {
			$this->assertEquals($urls_expected[$key], make_clickable($url));
		}
	}

	/**
	 * tests that make_clickable will not link trailing periods, commas and
	 * (semi-)colons in URLs without protocol (i.e. www.wordpress.org)
	 *
	 * @covers ::make_clickable
	 */
	function test_make_clickable_strip_trailing_without_protocol_nothing_afterwards() {
		$urls_before = array(
			'www.wordpress.org',
			'There was a spoon named www.wordpress.org.',
			'There was a spoon named www.wordpress.org,',
			'There was a spoon named www.wordpress.org;',
			'There was a spoon named www.wordpress.org:',
			'There was a spoon named www.wordpress.org)'
			);
		$urls_expected = array(
			'<a href="http://www.wordpress.org" rel="nofollow">http://www.wordpress.org</a>',
			'There was a spoon named <a href="http://www.wordpress.org" rel="nofollow">http://www.wordpress.org</a>.',
			'There was a spoon named <a href="http://www.wordpress.org" rel="nofollow">http://www.wordpress.org</a>,',
			'There was a spoon named <a href="http://www.wordpress.org" rel="nofollow">http://www.wordpress.org</a>;',
			'There was a spoon named <a href="http://www.wordpress.org" rel="nofollow">http://www.wordpress.org</a>:',
			'There was a spoon named <a href="http://www.wordpress.org" rel="nofollow">http://www.wordpress.org</a>)'
			);

		foreach ($urls_before as $key => $url) {
			$this->assertEquals($urls_expected[$key], make_clickable($url));
		}
	}

	/**
	 * @covers ::make_clickable
	 * @ticket 4570
	 */
	function test_make_clickable_iri() {
		$urls_before = array(
			'http://www.詹姆斯.com/',
			'http://bg.wikipedia.org/Баба',
			'http://example.com/?a=баба&b=дядо',
		);
		$urls_expected = array(
			'<a href="http://www.詹姆斯.com/" rel="nofollow">http://www.詹姆斯.com/</a>',
			'<a href="http://bg.wikipedia.org/Баба" rel="nofollow">http://bg.wikipedia.org/Баба</a>',
			'<a href="http://example.com/?a=баба&#038;b=дядо" rel="nofollow">http://example.com/?a=баба&#038;b=дядо</a>',
		);
		foreach ($urls_before as $key => $url) {
			$this->assertEquals($urls_expected[$key], make_clickable($url));
		}
	}

	/**
	 * @covers ::make_clickable
	 * @ticket 10990
	 */
	function test_make_clickable_brackets_in_urls() {
		$urls_before = array(
			'http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)',
			'(http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software))',
			'blah http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software) blah',
			'blah (http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)) blah',
			'blah blah blah http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software) blah blah',
			'blah blah blah http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)) blah blah',
			'blah blah (http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)) blah blah',
			'blah blah http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software).) blah blah',
			'blah blah http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software).)moreurl blah blah',
			'In his famous speech “You and Your research” (here:
			http://www.cs.virginia.edu/~robins/YouAndYourResearch.html)
			Richard Hamming wrote about people getting more done with their doors closed, but',
		);
		$urls_expected = array(
			'<a href="http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)" rel="nofollow">http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)</a>',
			'(<a href="http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)" rel="nofollow">http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)</a>)',
			'blah <a href="http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)" rel="nofollow">http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)</a> blah',
			'blah (<a href="http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)" rel="nofollow">http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)</a>) blah',
			'blah blah blah <a href="http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)" rel="nofollow">http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)</a> blah blah',
			'blah blah blah <a href="http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)" rel="nofollow">http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)</a>) blah blah',
			'blah blah (<a href="http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)" rel="nofollow">http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)</a>) blah blah',
			'blah blah <a href="http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)" rel="nofollow">http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)</a>.) blah blah',
			'blah blah <a href="http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)" rel="nofollow">http://en.wikipedia.org/wiki/PC_Tools_(Central_Point_Software)</a>.)moreurl blah blah',
			'In his famous speech “You and Your research” (here:
			<a href="http://www.cs.virginia.edu/~robins/YouAndYourResearch.html" rel="nofollow">http://www.cs.virginia.edu/~robins/YouAndYourResearch.html</a>)
			Richard Hamming wrote about people getting more done with their doors closed, but',
		);
		foreach ($urls_before as $key => $url) {
			$this->assertEquals($urls_expected[$key], make_clickable($url));
		}
	}

	/**
	 * Based on a real comments which were incorrectly linked. #11211
	 *
	 * @covers ::make_clickable
	 * @ticket 11211
	 */
	function test_make_clickable_real_world_examples() {
		$urls_before = array(
			'Example: WordPress, test (some text), I love example.com (http://example.org), it is brilliant',
			'Example: WordPress, test (some text), I love example.com (http://example.com), it is brilliant',
			'Some text followed by a bracketed link with a trailing elipsis (http://example.com)...',
			'In his famous speech “You and Your research” (here: http://www.cs.virginia.edu/~robins/YouAndYourResearch.html) Richard Hamming wrote about people getting more done with their doors closed...',
		);
		$urls_expected = array(
			'Example: WordPress, test (some text), I love example.com (<a href="http://example.org" rel="nofollow">http://example.org</a>), it is brilliant',
			'Example: WordPress, test (some text), I love example.com (<a href="http://example.com" rel="nofollow">http://example.com</a>), it is brilliant',
			'Some text followed by a bracketed link with a trailing elipsis (<a href="http://example.com" rel="nofollow">http://example.com</a>)...',
			'In his famous speech “You and Your research” (here: <a href="http://www.cs.virginia.edu/~robins/YouAndYourResearch.html" rel="nofollow">http://www.cs.virginia.edu/~robins/YouAndYourResearch.html</a>) Richard Hamming wrote about people getting more done with their doors closed...',
		);
		foreach ($urls_before as $key => $url) {
			$this->assertEquals($urls_expected[$key], make_clickable($url));
		}
	}

	/**
	 * @covers ::make_clickable
	 * @ticket 14993
	 */
	function test_make_clickable_twitter_hash_bang() {
		$urls_before = array(
			'http://twitter.com/#!/wordpress/status/25907440233',
			'This is a really good tweet http://twitter.com/#!/wordpress/status/25907440233 !',
			'This is a really good tweet http://twitter.com/#!/wordpress/status/25907440233!',
		);
		$urls_expected = array(
			'<a href="http://twitter.com/#!/wordpress/status/25907440233" rel="nofollow">http://twitter.com/#!/wordpress/status/25907440233</a>',
			'This is a really good tweet <a href="http://twitter.com/#!/wordpress/status/25907440233" rel="nofollow">http://twitter.com/#!/wordpress/status/25907440233</a> !',
			'This is a really good tweet <a href="http://twitter.com/#!/wordpress/status/25907440233" rel="nofollow">http://twitter.com/#!/wordpress/status/25907440233</a>!',
		);
		foreach ($urls_before as $key => $url) {
			$this->assertEquals($urls_expected[$key], make_clickable($url));
		}
	}

	/**
	 * @covers ::make_clickable
	 */
	function test_make_clickable_wrapped_in_angles() {
		$before = array(
			'URL wrapped in angle brackets <http://example.com/>',
			'URL wrapped in angle brackets with padding < http://example.com/ >',
			'mailto wrapped in angle brackets <foo@example.com>',
		);
		$expected = array(
			'URL wrapped in angle brackets <<a href="http://example.com/" rel="nofollow">http://example.com/</a>>',
			'URL wrapped in angle brackets with padding < <a href="http://example.com/" rel="nofollow">http://example.com/</a> >',
			'mailto wrapped in angle brackets <foo@example.com>',
		);
		foreach ($before as $key => $url) {
			$this->assertEquals($expected[$key], make_clickable($url));
		}
	}

	/**
	 * @covers ::make_clickable
	 */
	function test_make_clickable_preceded_by_punctuation() {
		$before = array(
			'Comma then URL,http://example.com/',
			'Period then URL.http://example.com/',
			'Semi-colon then URL;http://example.com/',
			'Colon then URL:http://example.com/',
			'Exclamation mark then URL!http://example.com/',
			'Question mark then URL?http://example.com/',
		);
		$expected = array(
			'Comma then URL,<a href="http://example.com/" rel="nofollow">http://example.com/</a>',
			'Period then URL.<a href="http://example.com/" rel="nofollow">http://example.com/</a>',
			'Semi-colon then URL;<a href="http://example.com/" rel="nofollow">http://example.com/</a>',
			'Colon then URL:<a href="http://example.com/" rel="nofollow">http://example.com/</a>',
			'Exclamation mark then URL!<a href="http://example.com/" rel="nofollow">http://example.com/</a>',
			'Question mark then URL?<a href="http://example.com/" rel="nofollow">http://example.com/</a>',
		);
		foreach ($before as $key => $url) {
			$this->assertEquals($expected[$key], make_clickable($url));
		}
	}

	/**
	 * @covers ::make_clickable
	 */
	function test_make_clickable_dont_break_attributes() {
		$urls_before = array(
			"<img src='http://trunk.domain/wp-includes/images/smilies/icon_smile.gif' alt=':)' class='wp-smiley'>",
			"(<img src='http://trunk.domain/wp-includes/images/smilies/icon_smile.gif' alt=':)' class='wp-smiley'>)",
			"http://trunk.domain/testing#something (<img src='http://trunk.domain/wp-includes/images/smilies/icon_smile.gif' alt=':)' class='wp-smiley'>)",
			"http://trunk.domain/testing#something
			(<img src='http://trunk.domain/wp-includes/images/smilies/icon_smile.gif' alt=':)' class='wp-smiley'>)",
			"<span style='text-align:center; display: block;'><object width='425' height='350'><param name='movie' value='http://www.youtube.com/v/nd_BdvG43rE&rel=1&fs=1&showsearch=0&showinfo=1&iv_load_policy=1' /> <param name='allowfullscreen' value='true' /> <param name='wmode' value='opaque' /> <embed src='http://www.youtube.com/v/nd_BdvG43rE&rel=1&fs=1&showsearch=0&showinfo=1&iv_load_policy=1' type='application/x-shockwave-flash' allowfullscreen='true' width='425' height='350' wmode='opaque'></embed> </object></span>",
			'<a href="http://example.com/example.gif" title="Image from http://example.com">Look at this image!</a>',
		);
		$urls_expected = array(
			"<img src='http://trunk.domain/wp-includes/images/smilies/icon_smile.gif' alt=':)' class='wp-smiley'>",
			"(<img src='http://trunk.domain/wp-includes/images/smilies/icon_smile.gif' alt=':)' class='wp-smiley'>)",
			"<a href=\"http://trunk.domain/testing#something\" rel=\"nofollow\">http://trunk.domain/testing#something</a> (<img src='http://trunk.domain/wp-includes/images/smilies/icon_smile.gif' alt=':)' class='wp-smiley'>)",
			"<a href=\"http://trunk.domain/testing#something\" rel=\"nofollow\">http://trunk.domain/testing#something</a>
			(<img src='http://trunk.domain/wp-includes/images/smilies/icon_smile.gif' alt=':)' class='wp-smiley'>)",
			"<span style='text-align:center; display: block;'><object width='425' height='350'><param name='movie' value='http://www.youtube.com/v/nd_BdvG43rE&rel=1&fs=1&showsearch=0&showinfo=1&iv_load_policy=1' /> <param name='allowfullscreen' value='true' /> <param name='wmode' value='opaque' /> <embed src='http://www.youtube.com/v/nd_BdvG43rE&rel=1&fs=1&showsearch=0&showinfo=1&iv_load_policy=1' type='application/x-shockwave-flash' allowfullscreen='true' width='425' height='350' wmode='opaque'></embed> </object></span>",
			'<a href="http://example.com/example.gif" title="Image from http://example.com">Look at this image!</a>',
		);
		foreach ($urls_before as $key => $url) {
			$this->assertEquals($urls_expected[$key], make_clickable($url));
		}
	}

	/**
	 * @covers ::make_clickable
	 * @ticket 23756
	 */
	function test_make_clickable_no_links_inside_pre_or_code() {
		$before = array(
			'<pre>http://wordpress.org</pre>',
			'<code>http://wordpress.org</code>',
			'<pre class="foobar" id="foo">http://wordpress.org</pre>',
			'<code class="foobar" id="foo">http://wordpress.org</code>',
			'<precustomtag>http://wordpress.org</precustomtag>',
			'<codecustomtag>http://wordpress.org</codecustomtag>',
			'URL before pre http://wordpress.org<pre>http://wordpress.org</pre>',
			'URL before code http://wordpress.org<code>http://wordpress.org</code>',
			'URL after pre <PRE>http://wordpress.org</PRE>http://wordpress.org',
			'URL after code <code>http://wordpress.org</code>http://wordpress.org',
			'URL before and after pre http://wordpress.org<pre>http://wordpress.org</pre>http://wordpress.org',
			'URL before and after code http://wordpress.org<code>http://wordpress.org</code>http://wordpress.org',
			'code inside pre <pre>http://wordpress.org <code>http://wordpress.org</code> http://wordpress.org</pre>',
		);

		$expected = array(
			'<pre>http://wordpress.org</pre>',
			'<code>http://wordpress.org</code>',
			'<pre class="foobar" id="foo">http://wordpress.org</pre>',
			'<code class="foobar" id="foo">http://wordpress.org</code>',
			'<precustomtag><a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a></precustomtag>',
			'<codecustomtag><a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a></codecustomtag>',
			'URL before pre <a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a><pre>http://wordpress.org</pre>',
			'URL before code <a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a><code>http://wordpress.org</code>',
			'URL after pre <PRE>http://wordpress.org</PRE><a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a>',
			'URL after code <code>http://wordpress.org</code><a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a>',
			'URL before and after pre <a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a><pre>http://wordpress.org</pre><a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a>',
			'URL before and after code <a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a><code>http://wordpress.org</code><a href="http://wordpress.org" rel="nofollow">http://wordpress.org</a>',
			'code inside pre <pre>http://wordpress.org <code>http://wordpress.org</code> http://wordpress.org</pre>',
		);

		foreach ( $before as $key => $url )
			$this->assertEquals( $expected[ $key ], make_clickable( $url ) );
	}

	/**
	 * @covers ::make_clickable
	 * @ticket 16892
	 */
	function test_make_clickable_click_inside_html() {
		$urls_before = array(
			'<span>http://example.com</span>',
			'<p>http://example.com/</p>',
		);
		$urls_expected = array(
			'<span><a href="http://example.com" rel="nofollow">http://example.com</a></span>',
			'<p><a href="http://example.com/" rel="nofollow">http://example.com/</a></p>',
		);
		foreach ($urls_before as $key => $url) {
			$this->assertEquals( $urls_expected[$key], make_clickable( $url ) );
		}
	}

	/**
	 * @covers ::make_clickable
	 */
	function test_make_clickable_no_links_within_links() {
		$in = array(
			'Some text with a link <a href="http://example.com">http://example.com</a>',
			//'<a href="http://wordpress.org">This is already a link www.wordpress.org</a>', // fails in 3.3.1 too
		);
		foreach ( $in as $text ) {
			$this->assertEquals( $text, make_clickable( $text ) );
		}
	}

	/**
	 * @covers ::make_clickable
	 * @ticket 16892
	 */
	function test_make_clickable_no_segfault() {
		$in = str_repeat( 'http://example.com/2011/03/18/post-title/', 256 );
		$out = make_clickable( $in );
		$this->assertEquals( $in, $out );
	}

	/**
	 * @covers ::make_clickable
	 * @ticket 19028
	 */
	function test_make_clickable_line_break_in_existing_clickable_link() {
		$html = "<a
				  href='mailto:someone@example.com'>someone@example.com</a>";
		$this->assertEquals( $html, make_clickable( $html ) );
	}

	/**
	 * WhitespaceTest Content DataProvider
	 *
	 * array( input_txt, converted_output_txt)
	 */
	public function normalize_whitespace_get_input_output() {
		return array (
			array (
				"		",
				""
			),
			array (
				"\rTEST\r",
				"TEST"
			),
			array (
				"\r\nMY TEST CONTENT\r\n",
				"MY TEST CONTENT"
			),
			array (
				"MY\r\nTEST\r\nCONTENT ",
				"MY\nTEST\nCONTENT"
			),
			array (
				"\tMY\rTEST\rCONTENT ",
				"MY\nTEST\nCONTENT"
			),
			array (
				"\tMY\t\t\tTEST\r\t\t\rCONTENT ",
				"MY TEST\n \nCONTENT"
			),
			array (
				"\tMY TEST \t\t\t CONTENT ",
				"MY TEST CONTENT"
			),
		);
	}

	/**
	 * Validate the normalize_whitespace function
	 *
	 * @covers ::normalize_whitespace
	 * @dataProvider normalize_whitespace_get_input_output
	 */
	function test_normalize_whitespace( $in_str, $exp_str ) {
		$this->assertEquals($exp_str, normalize_whitespace( $in_str ) );
	}

	/**
	 * @covers ::wp_sanitize_redirect
	 * @group pluggable
	 */
	function test_wp_sanitize_redirect() {
		$this->assertEquals('http://example.com/watchthelinefeedgo', wp_sanitize_redirect('http://example.com/watchthelinefeed%0Ago'));
		$this->assertEquals('http://example.com/watchthelinefeedgo', wp_sanitize_redirect('http://example.com/watchthelinefeed%0ago'));
		$this->assertEquals('http://example.com/watchthecarriagereturngo', wp_sanitize_redirect('http://example.com/watchthecarriagereturn%0Dgo'));
		$this->assertEquals('http://example.com/watchthecarriagereturngo', wp_sanitize_redirect('http://example.com/watchthecarriagereturn%0dgo'));
		$this->assertEquals('http://example.com/watchtheallowedcharacters-~+_.?#=&;,/:%!*stay', wp_sanitize_redirect('http://example.com/watchtheallowedcharacters-~+_.?#=&;,/:%!*stay'));
		//Nesting checks
		$this->assertEquals('http://example.com/watchthecarriagereturngo', wp_sanitize_redirect('http://example.com/watchthecarriagereturn%0%0ddgo'));
		$this->assertEquals('http://example.com/watchthecarriagereturngo', wp_sanitize_redirect('http://example.com/watchthecarriagereturn%0%0DDgo'));
		$this->assertEquals('http://example.com/whyisthisintheurl/?param[1]=foo', wp_sanitize_redirect('http://example.com/whyisthisintheurl/?param[1]=foo'));
		$this->assertEquals('http://[2606:2800:220:6d:26bf:1447:aa7]/', wp_sanitize_redirect('http://[2606:2800:220:6d:26bf:1447:aa7]/'));
		$this->assertEquals('http://example.com/search.php?search=(amistillhere)', wp_sanitize_redirect('http://example.com/search.php?search=(amistillhere)'));
	}

	/**
	 * @covers ::remove_accents
	 */
	public function test_remove_accents_simple() {
		$this->assertEquals( 'abcdefghijkl', remove_accents( 'abcdefghijkl' ) );
	}

	/**
	 * @covers ::remove_accents
	 * @ticket 9591
	 */
	public function test_remove_accents_latin1_supplement() {
		$input = 'ªºÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ';
		$output = 'aoAAAAAAAECEEEEIIIIDNOOOOOOUUUUYTHsaaaaaaaeceeeeiiiidnoooooouuuuythy';

		$this->assertEquals( $output, remove_accents( $input ), 'remove_accents replaces Latin-1 Supplement' );
	}

	/**
	 * @covers ::remove_accents
	 */
	public function test_remove_accents_latin_extended_a() {
		$input = 'ĀāĂăĄąĆćĈĉĊċČčĎďĐđĒēĔĕĖėĘęĚěĜĝĞğĠġĢģĤĥĦħĨĩĪīĬĭĮįİıĲĳĴĵĶķĸĹĺĻļĽľĿŀŁłŃńŅņŇňŉŊŋŌōŎŏŐőŒœŔŕŖŗŘřŚśŜŝŞşŠšŢţŤťŦŧŨũŪūŬŭŮůŰűŲųŴŵŶŷŸŹźŻżŽžſ';
		$output = 'AaAaAaCcCcCcCcDdDdEeEeEeEeEeGgGgGgGgHhHhIiIiIiIiIiIJijJjKkkLlLlLlLlLlNnNnNnNnNOoOoOoOEoeRrRrRrSsSsSsSsTtTtTtUuUuUuUuUuUuWwYyYZzZzZzs';

		$this->assertEquals( $output, remove_accents( $input ), 'remove_accents replaces Latin Extended A' );
	}

	/**
	 * @covers ::remove_accents
	 */
	public function test_remove_accents_latin_extended_b() {
		$this->assertEquals( 'SsTt', remove_accents( 'ȘșȚț' ), 'remove_accents replaces Latin Extended B' );
	}

	/**
	 * @covers ::remove_accents
	 */
	public function test_remove_accents_euro_pound_signs() {
		$this->assertEquals( 'E', remove_accents( '€' ), 'remove_accents replaces euro sign' );
		$this->assertEquals( '', remove_accents( '£' ), 'remove_accents replaces pound sign' );
	}

	/**
	 * @covers ::remove_accents
	 */
	public function test_remove_accents_iso8859() {
		// File is Latin1 encoded
		$file = DIR_TESTDATA . '/formatting/remove_accents.01.input.txt';
		$input = file_get_contents( $file );
		$input = trim( $input );
		$output = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyyOEoeAEDHTHssaedhth";

		$this->assertEquals( $output, remove_accents( $input ), 'remove_accents from ISO-8859-1 text' );
	}

	/**
	 * @covers ::remove_accents
	 * @ticket 17738
	 */
	public function test_remove_accents_vowels_diacritic() {
		// Vowels with diacritic
		// unmarked
		$this->assertEquals( 'OoUu', remove_accents( 'ƠơƯư' ) );
		// grave accent
		$this->assertEquals( 'AaAaEeOoOoUuYy', remove_accents( 'ẦầẰằỀềỒồỜờỪừỲỳ' ) );
		// hook
		$this->assertEquals( 'AaAaAaEeEeIiOoOoOoUuUuYy', remove_accents( 'ẢảẨẩẲẳẺẻỂểỈỉỎỏỔổỞởỦủỬửỶỷ' ) );
		// tilde
		$this->assertEquals( 'AaAaEeEeOoOoUuYy', remove_accents( 'ẪẫẴẵẼẽỄễỖỗỠỡỮữỸỹ' ) );
		// acute accent
		$this->assertEquals( 'AaAaEeOoOoUu', remove_accents( 'ẤấẮắẾếỐốỚớỨứ' ) );
		// dot below
		$this->assertEquals( 'AaAaAaEeEeIiOoOoOoUuUuYy', remove_accents( 'ẠạẬậẶặẸẹỆệỊịỌọỘộỢợỤụỰựỴỵ' ) );
	}

	/**
	 * @covers ::remove_accents
	 * @ticket 20772
	 */
	public function test_remove_accents_hanyu_pinyin() {
		// Vowels with diacritic (Chinese, Hanyu Pinyin)
		// macron
		$this->assertEquals( 'aeiouuAEIOUU', remove_accents( 'āēīōūǖĀĒĪŌŪǕ' ) );
		// acute accent
		$this->assertEquals( 'aeiouuAEIOUU', remove_accents( 'áéíóúǘÁÉÍÓÚǗ' ) );
		// caron
		$this->assertEquals( 'aeiouuAEIOUU', remove_accents( 'ǎěǐǒǔǚǍĚǏǑǓǙ' ) );
		// grave accent
		$this->assertEquals( 'aeiouuAEIOUU', remove_accents( 'àèìòùǜÀÈÌÒÙǛ' ) );
		// unmarked
		$this->assertEquals( 'aaeiouuAEIOUU', remove_accents( 'aɑeiouüAEIOUÜ' ) );
	}

	function _remove_accents_germanic_umlauts_cb() {
		return 'de_DE';
	}

	/**
	 * @covers ::remove_accents
	 * @ticket 3782
	 */
	public function test_remove_accents_germanic_umlauts() {
		add_filter( 'locale', array( $this, '_remove_accents_germanic_umlauts_cb' ) );

		$this->assertEquals( 'AeOeUeaeoeuess', remove_accents( 'ÄÖÜäöüß' ) );

		remove_filter( 'locale', array( $this, '_remove_accents_germanic_umlauts_cb' ) );
	}

	public function _remove_accents_set_locale_to_danish() {
		return 'da_DK';
	}

	/**
	 * @covers ::remove_accents
	 * @ticket 23907
	 */
	public function test_remove_danish_accents() {
		add_filter( 'locale', array( $this, '_remove_accents_set_locale_to_danish' ) );
		
		$this->assertEquals( 'AeOeAaaeoeaa', remove_accents( 'ÆØÅæøå' ) );
		
		remove_filter( 'locale', array( $this, '_set_locale_to_danish' ) );
	}

	/**
	 * @covers ::sanitize_file_name
	 */
	function test_sanitize_file_name_munges_extensions() {
		# r17990
		$file_name = sanitize_file_name( 'test.phtml.txt' );
		$this->assertEquals( 'test.phtml_.txt', $file_name );
	}

	/**
	 * @covers ::sanitize_file_name
	 */
	function test_sanitize_file_name_removes_special_chars() {
		$special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", chr(0));
		$string = 'test';
		foreach ( $special_chars as $char )
			$string .= $char;
		$string .= 'test';
		$this->assertEquals( 'testtest', sanitize_file_name( $string ) );
	}

	/**
	 * Test that spaces are correctly replaced with dashes.
	 *
	 * @covers ::sanitize_file_name
	 * @ticket 16330
	 */
	function test_sanitize_file_name_replace_spaces() {
		$urls = array(
			'unencoded space.png'   => 'unencoded-space.png',
			'encoded%20space.jpg'   => 'encoded-space.jpg',
			'plus+space.jpg'        => 'plus-space.jpg',
			'multi %20 +space.png'   => 'multi-space.png',
		);

		foreach( $urls as $test => $expected ) {
			$this->assertEquals( $expected, sanitize_file_name( $test ) );
		}
	}

	/**
	 * @covers ::sanitize_file_name
	 */
	function test_sanitize_file_name_replaces_any_number_of_hyphens_with_one_hyphen() {
		$this->assertEquals("a-t-t", sanitize_file_name("a----t----t"));
	}

	/**
	 * @covers ::sanitize_file_name
	 */
	function test_sanitize_file_name_trims_trailing_hyphens() {
		$this->assertEquals("a-t-t", sanitize_file_name("a----t----t----"));
	}

	/**
	 * @covers ::sanitize_file_name
	 */
	function test_sanitize_file_name_replaces_any_amount_of_whitespace_with_one_hyphen() {
		$this->assertEquals("a-t", sanitize_file_name("a          t"));
		$this->assertEquals("a-t", sanitize_file_name("a    \n\n\nt"));
	}

/* // @todo These tests need to be rewritten for sanitize_sql_orderby
class Tests_Formatting_SanitizeOrderby extends WP_UnitTestCase {
	function test_empty() {
		$cols = array('a' => 'a');
		$this->assertEquals( '', sanitize_sql_orderby('', $cols) );
		$this->assertEquals( '', sanitize_sql_orderby('  ', $cols) );
		$this->assertEquals( '', sanitize_sql_orderby("\t", $cols) );
		$this->assertEquals( '', sanitize_sql_orderby(null, $cols) );
		$this->assertEquals( '', sanitize_sql_orderby(0, $cols) );
		$this->assertEquals( '', sanitize_sql_orderby('+', $cols) );
		$this->assertEquals( '', sanitize_sql_orderby('-', $cols) );
	}

	function test_unknown_column() {
		$cols = array('name' => 'post_name', 'date' => 'post_date');
		$this->assertEquals( '', sanitize_sql_orderby('unknown_column', $cols) );
		$this->assertEquals( '', sanitize_sql_orderby('+unknown_column', $cols) );
		$this->assertEquals( '', sanitize_sql_orderby('-unknown_column', $cols) );
		$this->assertEquals( '', sanitize_sql_orderby('-unknown1,+unknown2,unknown3', $cols) );
		$this->assertEquals( 'post_name ASC', sanitize_sql_orderby('name,unknown_column', $cols) );
		$this->assertEquals( '', sanitize_sql_orderby('!@#$%^&*()_=~`\'",./', $cols) );
	}

	function test_valid() {
		$cols = array('name' => 'post_name', 'date' => 'post_date', 'random' => 'rand()');
		$this->assertEquals( 'post_name ASC', sanitize_sql_orderby('name', $cols) );
		$this->assertEquals( 'post_name ASC', sanitize_sql_orderby('+name', $cols) );
		$this->assertEquals( 'post_name DESC', sanitize_sql_orderby('-name', $cols) );
		$this->assertEquals( 'post_date ASC, post_name ASC', sanitize_sql_orderby('date,name', $cols) );
		$this->assertEquals( 'post_date ASC, post_name ASC', sanitize_sql_orderby(' date , name ', $cols) );
		$this->assertEquals( 'post_name DESC, post_date ASC', sanitize_sql_orderby('-name,date', $cols) );
		$this->assertEquals( 'post_name ASC, post_date ASC', sanitize_sql_orderby('name ,+ date', $cols) );
		$this->assertEquals( 'rand() ASC', sanitize_sql_orderby('random', $cols) );
	}
}
*/

	/**
	 * @covers ::sanitize_mime_type
	 * @ticket 17855
	 */
	function test_sanitize_mime_type_valid() {
		$inputs = array(
			'application/atom+xml',
			'application/EDI-X12',
			'application/EDIFACT',
			'application/json',
			'application/javascript',
			'application/octet-stream',
			'application/ogg',
			'application/pdf',
			'application/postscript',
			'application/soap+xml',
			'application/x-woff',
			'application/xhtml+xml',
			'application/xml-dtd',
			'application/xop+xml',
			'application/zip',
			'application/x-gzip',
			'audio/basic',
			'image/jpeg',
			'text/css',
			'text/html',
			'text/plain',
			'video/mpeg',
		);

		foreach ( $inputs as $input ) {
			$this->assertEquals($input, sanitize_mime_type($input));
		}
	}

	/**
	 * TODO add covers notation
	 * @group post 
	 * @ticket 22324
	 */
	function test_int_fields() {
		$post = $this->factory->post->create_and_get();
		$int_fields = array(
			'ID'            => 'integer',
			'post_parent'   => 'integer',
			'menu_order'    => 'integer',
			'post_author'   => 'string',
			'comment_count' => 'string',
		);

		foreach ( $int_fields as $field => $type ) {
			$this->assertInternalType( $type, $post->$field, "field $field" );
		}
	}

	/**
	 * @covers ::sanitize_text_field
	 * @ticket 11528
	 */
	function test_sanitize_text_field() {
		$inputs = array(
			'оРангутанг', //Ensure UTF8 text is safe the Р is D0 A0 and A0 is the non-breaking space.
			'САПР', //Ensure UTF8 text is safe the Р is D0 A0 and A0 is the non-breaking space.
			'one is < two',
			'tags <span>are</span> <em>not allowed</em> here',
			' we should trim leading and trailing whitespace ',
			'we  also  trim  extra  internal  whitespace',
			'tabs 	get removed too',
			'newlines are not welcome
			here',
			'We also %AB remove %ab octets',
			'We don\'t need to wory about %A
			B removing %a
			b octets even when %a	B they are obscured by whitespace',
			'%AB%BC%DE', //Just octets
			'Invalid octects remain %II',
			'Nested octects %%%ABABAB %A%A%ABBB',
		);
		$expected = array(
			'оРангутанг',
			'САПР',
			'one is &lt; two',
			'tags are not allowed here',
			'we should trim leading and trailing whitespace',
			'we also trim extra internal whitespace',
			'tabs get removed too',
			'newlines are not welcome here',
			'We also remove octets',
			'We don\'t need to wory about %A B removing %a b octets even when %a B they are obscured by whitespace',
			'', //Emtpy as we strip all the octets out
			'Invalid octects remain %II',
			'Nested octects',
		);

		foreach ($inputs as $key => $input) {
			$this->assertEquals($expected[$key], sanitize_text_field($input));
		}
	}

	/**
	 * @covers ::sanitize_title
	 */
	function test_sanitize_title_strips_html() {
		$input = "Captain <strong>Awesome</strong>";
		$expected = "captain-awesome";
		$this->assertEquals($expected, sanitize_title($input));
	}

	/**
	 * @covers ::sanitize_title
	 */
	function test_sanitize_title_titles_sanitized_to_nothing_are_replaced_with_optional_fallback() {
		$input = "<strong></strong>";
		$fallback = "Captain Awesome";
		$this->assertEquals($fallback, sanitize_title($input, $fallback));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	function test_sanitize_title_with_dashes_strips_unencoded_percent_signs() {
		$this->assertEquals("fran%c3%a7ois", sanitize_title_with_dashes("fran%c3%a7%ois"));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	function test_sanitize_title_with_dashes_makes_title_lowercase() {
		$this->assertEquals("abc", sanitize_title_with_dashes("ABC"));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	function test_sanitize_title_with_dashes_replaces_any_amount_of_whitespace_with_one_hyphen() {
		$this->assertEquals("a-t", sanitize_title_with_dashes("a          t"));
		$this->assertEquals("a-t", sanitize_title_with_dashes("a    \n\n\nt"));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	function test_sanitize_title_with_dashes_replaces_any_number_of_hyphens_with_one_hyphen() {
		$this->assertEquals("a-t-t", sanitize_title_with_dashes("a----t----t"));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	function test_sanitize_title_with_dashes_trims_trailing_hyphens() {
		$this->assertEquals("a-t-t", sanitize_title_with_dashes("a----t----t----"));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	function test_sanitize_title_with_dashes_handles_non_entity_ampersands() {
		$this->assertEquals("penn-teller-bull", sanitize_title_with_dashes("penn & teller bull"));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	public function test_sanitize_title_with_dashes_strips_nbsp_ndash_and_amp() {
		$this->assertEquals("no-entities-here", sanitize_title_with_dashes("No &nbsp; Entities &ndash; Here &amp;"));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	public function test_sanitize_title_with_dashes_strips_encoded_ampersand() {
		$this->assertEquals("one-two", sanitize_title_with_dashes("One &amp; Two", '', 'save'));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	public function test_sanitize_title_with_dashes_strips_url_encoded_ampersand() {
		$this->assertEquals("one-two", sanitize_title_with_dashes("One &#123; Two;", '', 'save'));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	public function test_sanitize_title_with_dashes_strips_trademark_symbol() {
		$this->assertEquals("one-two", sanitize_title_with_dashes("One Two™;", '', 'save'));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	public function test_sanitize_title_with_dashes_strips_unencoded_ampersand_followed_by_encoded_ampersand() {
		$this->assertEquals("one-two", sanitize_title_with_dashes("One &&amp; Two;", '', 'save'));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	public function test_sanitize_title_with_dashes_strips_unencoded_ampersand_when_not_surrounded_by_spaces() {
		$this->assertEquals("onetwo", sanitize_title_with_dashes("One&Two", '', 'save'));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	function test_sanitize_title_with_dashes_replaces_nbsp() {
		$this->assertEquals("dont-break-the-space", sanitize_title_with_dashes("don't break the space", '', 'save'));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	function test_sanitize_title_with_dashes_replaces_ndash_mdash() {
		$this->assertEquals("do-the-dash", sanitize_title_with_dashes("Do – the Dash", '', 'save'));
		$this->assertEquals("do-the-dash", sanitize_title_with_dashes("Do the — Dash", '', 'save'));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	function test_sanitize_title_with_dashes_replaces_iexcel_iquest() {
		$this->assertEquals("just-a-slug", sanitize_title_with_dashes("Just ¡a Slug", '', 'save'));
		$this->assertEquals("just-a-slug", sanitize_title_with_dashes("Just a Slug¿", '', 'save'));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	function test_sanitize_title_with_dashes_replaces_angle_quotes() {
		$this->assertEquals("just-a-slug", sanitize_title_with_dashes("‹Just a Slug›", '', 'save'));
		$this->assertEquals("just-a-slug", sanitize_title_with_dashes("«Just a Slug»", '', 'save'));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	function test_sanitize_title_with_dashes_replaces_curly_quotes() {
		$this->assertEquals("hey-its-curly-joe", sanitize_title_with_dashes("Hey its “Curly Joe”", '', 'save'));
		$this->assertEquals("hey-its-curly-joe", sanitize_title_with_dashes("Hey its ‘Curly Joe’", '', 'save'));
		$this->assertEquals("hey-its-curly-joe", sanitize_title_with_dashes("Hey its „Curly Joe“", '', 'save'));
		$this->assertEquals("hey-its-curly-joe", sanitize_title_with_dashes("Hey its ‚Curly Joe‛", '', 'save'));
		$this->assertEquals("hey-its-curly-joe", sanitize_title_with_dashes("Hey its „Curly Joe‟", '', 'save'));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 */
	function test_sanitize_title_with_dashes_replaces_copy_reg_deg_trade() {
		$this->assertEquals("just-a-slug", sanitize_title_with_dashes("Just © a Slug", '', 'save'));
		$this->assertEquals("just-a-slug", sanitize_title_with_dashes("® Just a Slug", '', 'save'));
		$this->assertEquals("just-a-slug", sanitize_title_with_dashes("Just a ° Slug", '', 'save'));
		$this->assertEquals("just-a-slug", sanitize_title_with_dashes("Just ™ a Slug", '', 'save'));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 * @ticket 19820
	 */
	function test_sanitize_title_with_dashes_replaces_multiply_sign() {
		$this->assertEquals("6x7-is-42", sanitize_title_with_dashes("6×7 is 42", '', 'save'));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 * @ticket 20772
	 */
	function test_sanitize_title_with_dashes_replaces_standalone_diacritic() {
		$this->assertEquals("aaaa", sanitize_title_with_dashes("āáǎà", '', 'save'));
	}

	/**
	 * @covers ::sanitize_title_with_dashes
	 * @ticket 22395
	 */
	function test_sanitize_title_with_dashes_replaces_acute_accents() {
		$this->assertEquals("aaaa", sanitize_title_with_dashes("ááa´aˊ", '', 'save'));
	}

	/**
	 * @covers ::sanitize_trackback_urls
	 * @ticket 21624
	 * @dataProvider sanitize_trackback_urls_breaks
	 */
	function test_sanitize_trackback_urls_with_multiple_urls( $break ) {
		$this->assertEquals( "http://example.com\nhttp://example.org", sanitize_trackback_urls( "http://example.com{$break}http://example.org" ) );
	}

	function sanitize_trackback_urls_breaks() {
		return array(
			array( "\r\n\t " ),
			array( "\r" ),
			array( "\n" ),
			array( "\t" ),
			array( ' ' ),
			array( '  ' ),
			array( "\n  " ),
			array( "\r\n" ),
		);
	}

	/**
	 * @covers ::sanitize_user
	 */
	function test_sanitize_user_strips_html() {
		$input = "Captain <strong>Awesome</strong>";
		$expected = is_multisite() ? 'captain awesome' : 'Captain Awesome';
		$this->assertEquals($expected, sanitize_user($input));
	}

	/**
	 * @covers ::sanitize_user
	 */
	public function test_sanitize_user_strips_encoded_ampersand() {
		$expected = 'ATT';

		// Multisite forces user logins to lowercase.
		if ( is_multisite() ) {
			$expected = strtolower( $expected );
		}

		$this->assertEquals( $expected, sanitize_user( "AT&amp;T" ) );
	}

	/**
	 * @covers ::sanitize_user
	 */
	public function test_sanitize_user_strips_encoded_ampersand_when_followed_by_semicolon() {
		$expected = 'ATT Test;';

		// Multisite forces user logins to lowercase.
		if ( is_multisite() ) {
			$expected = strtolower( $expected );
		}

		$this->assertEquals( $expected, sanitize_user( "AT&amp;T Test;" ) );
	}

	/**
	 * @covers ::sanitize_user
	 */
	function test_sanitize_user_strips_percent_encoded_octets() {
		$expected = is_multisite() ? 'franois' : 'Franois';
		$this->assertEquals( $expected, sanitize_user( "Fran%c3%a7ois" ) );
	}

	/**
	 * @covers ::sanitize_user
	 */
	function test_sanitize_user_optional_strict_mode_reduces_to_safe_ascii_subset() {
		$this->assertEquals("abc", sanitize_user("()~ab~ˆcˆ!", true));
	}


	/**
	 * `seems_utf8` returns true for utf-8 strings, false otherwise.
	 *
	 * @covers ::seems_utf8
	 * @dataProvider seems_utf8_strings
	 */
	function test_seems_utf8_returns_true_for_utf8_strings( $utf8_string ) {
		// from http://www.i18nguy.com/unicode-example.html
		$this->assertTrue( seems_utf8( $utf8_string ) );
	}

	function seems_utf8_strings() {
		$utf8_strings = file( DIR_TESTDATA . '/formatting/utf-8/utf-8.txt' );
		foreach ( $utf8_strings as &$string ) {
			$string = (array) trim( $string );
		}
		unset( $string );
		return $utf8_strings;
	}

	/**
	 * @covers ::seems_utf8
	 * @dataProvider seems_utf8_big5_strings
	 */
	function test_seems_utf8_returns_false_for_non_utf8_strings( $big5_string ) {
		$this->assertFalse( seems_utf8( $big5_string ) );
	}

	function seems_utf8_big5_strings() {
		// Get data from formatting/big5.txt
		$big5_strings = file( DIR_TESTDATA . '/formatting/big5.txt' );
		foreach ( $big5_strings as &$string ) {
			$string = (array) trim( $string );
		}
		unset( $string );
		return $big5_strings;
	}

	/**
	 * @covers ::backslashit
	 */
	function test_backslashit_middle_numbers() {
		$this->assertEquals("\\a-!9\\a943\\b\\c", backslashit("a-!9a943bc"));
	}

	/**
	 * @covers ::backslashit
	 */
	function test_backslashit_alphas() {
		$this->assertEquals("\\a943\\b\\c", backslashit("a943bc"));
	}

	/**
	 * @covers ::backslashit
	 */
	function test_backslashit_double_backslashes_leading_numbers() {
		$this->assertEquals("\\\\95", backslashit("95"));
	}

	/**
	 * @covers ::untrailingslashit
	 */
	function test_untrailingslashit_removes_trailing_slashes() {
		$this->assertEquals("a", untrailingslashit("a/"));
		$this->assertEquals("a", untrailingslashit("a////"));
	}

	/**
	 * @covers ::untrailingslashit
	 * @ticket 22267
	 */
	function test_untrailingslashit_removes_trailing_backslashes() {
		$this->assertEquals("a", untrailingslashit("a\\"));
		$this->assertEquals("a", untrailingslashit("a\\\\\\\\"));
	}

	/**
	 * @covers ::untrailingslashit
	 * @ticket 22267
	 */
	function test_untrailingslashit_removes_trailing_mixed_slashes() {
		$this->assertEquals("a", untrailingslashit("a/\\"));
		$this->assertEquals("a", untrailingslashit("a\\/\\///\\\\//"));
	}

	/**
	 * @covers ::trailingslashit
	 */
	function test_trailingslashit_adds_trailing_slash() {
		$this->assertEquals("a/", trailingslashit("a"));
	}

	/**
	 * @covers ::trailingslashit
	 */
	function test_trailingslashit_does_not_add_trailing_slash_if_one_exists() {
		$this->assertEquals("a/", trailingslashit("a/"));
	}

	/**
	 * @covers ::trailingslashit
	 * @ticket 22267
	 */
	function test_trailingslashit_converts_trailing_backslash_to_slash_if_one_exists() {
		$this->assertEquals("a/", trailingslashit("a\\"));
	}

	/**
	 * Basic Test Content DataProvider
	 *
	 * array ( input_txt, converted_output_txt)
	 */
	public function get_smilies_input_output() {
		$includes_path = includes_url("images/smilies/");

		return array (
			array (
				'Lorem ipsum dolor sit amet mauris ;-) Praesent gravida sodales. :lol: Vivamus nec diam in faucibus eu, bibendum varius nec, imperdiet purus est, at augue at lacus malesuada elit dapibus a, :eek: mauris. Cras mauris viverra elit. Nam laoreet viverra. Pellentesque tortor. Nam libero ante, porta urna ut turpis. Nullam wisi magna, :mrgreen: tincidunt nec, sagittis non, fringilla enim. Nam consectetuer nec, ullamcorper pede eu dui odio consequat vel, vehicula tortor quis pede turpis cursus quis, egestas ipsum ultricies ut, eleifend velit. Mauris vestibulum iaculis. Sed in nunc. Vivamus elit porttitor egestas. Mauris purus :?:',
				'Lorem ipsum dolor sit amet mauris <img src="' . $includes_path . 'icon_wink.gif" alt=";-)" class="wp-smiley" /> Praesent gravida sodales. <img src="' . $includes_path . 'icon_lol.gif" alt=":lol:" class="wp-smiley" /> Vivamus nec diam in faucibus eu, bibendum varius nec, imperdiet purus est, at augue at lacus malesuada elit dapibus a, <img src="' . $includes_path . 'icon_surprised.gif" alt=":eek:" class="wp-smiley" /> mauris. Cras mauris viverra elit. Nam laoreet viverra. Pellentesque tortor. Nam libero ante, porta urna ut turpis. Nullam wisi magna, <img src="' . $includes_path . 'icon_mrgreen.gif" alt=":mrgreen:" class="wp-smiley" /> tincidunt nec, sagittis non, fringilla enim. Nam consectetuer nec, ullamcorper pede eu dui odio consequat vel, vehicula tortor quis pede turpis cursus quis, egestas ipsum ultricies ut, eleifend velit. Mauris vestibulum iaculis. Sed in nunc. Vivamus elit porttitor egestas. Mauris purus <img src="' . $includes_path . 'icon_question.gif" alt=":?:" class="wp-smiley" />'
			),
			array (
				'<strong>Welcome to the jungle!</strong> We got fun n games! :) We got everything you want 8-) <em>Honey we know the names :)</em>',
				'<strong>Welcome to the jungle!</strong> We got fun n games! <img src="' . $includes_path . 'icon_smile.gif" alt=":)" class="wp-smiley" /> We got everything you want <img src="' . $includes_path . 'icon_cool.gif" alt="8-)" class="wp-smiley" /> <em>Honey we know the names <img src="' . $includes_path . 'icon_smile.gif" alt=":)" class="wp-smiley" /></em>'
			),
			array (
				"<strong;)>a little bit of this\na little bit:other: of that :D\n:D a little bit of good\nyeah with a little bit of bad8O",
				"<strong;)>a little bit of this\na little bit:other: of that <img src=\"{$includes_path}icon_biggrin.gif\" alt=\":D\" class=\"wp-smiley\" />\n<img src=\"{$includes_path}icon_biggrin.gif\" alt=\":D\" class=\"wp-smiley\" /> a little bit of good\nyeah with a little bit of bad8O"
			),
			array (
				'<strong style="here comes the sun :-D">and I say it\'s allright:D:D',
				'<strong style="here comes the sun :-D">and I say it\'s allright:D:D'
			),
			array (
				'<!-- Woo-hoo, I\'m a comment, baby! :x > -->',
				'<!-- Woo-hoo, I\'m a comment, baby! :x > -->'
			),
			array (
				':?:P:?::-x:mrgreen:::',
				':?:P:?::-x:mrgreen:::'
			),
		);
	}

	/**
	 * @covers ::smilies_init
	 * @covers ::convert_smilies
	 * @dataProvider get_smilies_input_output
	 *
	 * Basic Validation Test to confirm that smilies are converted to image
	 * when use_smilies = 1 and not when use_smilies = 0
	 */
	function test_convert_standard_smilies( $in_txt, $converted_txt ) {
		// standard smilies, use_smilies: ON
		update_option( 'use_smilies', 1 );

		smilies_init();

		$this->assertEquals( $converted_txt, convert_smilies($in_txt) );

		// standard smilies, use_smilies: OFF
		update_option( 'use_smilies', 0 );

		$this->assertEquals( $in_txt, convert_smilies($in_txt) );
	}

	/**
	 * Custom Smilies Test Content DataProvider
	 *
	 * array ( input_txt, converted_output_txt)
	 */
	public function get_custom_smilies_input_output() {
		$includes_path = includes_url("images/smilies/");

		return array (
			array (
				'Peter Brian Gabriel (born 13 February 1950) is a British singer, musician, and songwriter who rose to fame as the lead vocalist and flautist of the progressive rock group Genesis. :monkey:',
				'Peter Brian Gabriel (born 13 February 1950) is a British singer, musician, and songwriter who rose to fame as the lead vocalist and flautist of the progressive rock group Genesis. <img src="' . $includes_path . 'icon_shock_the_monkey.gif" alt=":monkey:" class="wp-smiley" />'
			),
			array (
				'Star Wars Jedi Knight :arrow: Jedi Academy is a first and third-person shooter action game set in the Star Wars universe. It was developed by Raven Software and published, distributed and marketed by LucasArts in North America and by Activision in the rest of the world. :nervou:',
				'Star Wars Jedi Knight <img src="' . $includes_path . 'icon_arrow.gif" alt=":arrow:" class="wp-smiley" /> Jedi Academy is a first and third-person shooter action game set in the Star Wars universe. It was developed by Raven Software and published, distributed and marketed by LucasArts in North America and by Activision in the rest of the world. <img src="' . $includes_path . 'icon_nervou.gif" alt=":nervou:" class="wp-smiley" />'
			),
			array (
				':arrow: monkey: Lorem ipsum dolor sit amet enim. Etiam ullam :PP <br />corper. Suspendisse a pellentesque dui, non felis.<a> :arrow: :arrow</a>',
				'<img src="' . $includes_path . 'icon_arrow.gif" alt=":arrow:" class="wp-smiley" /> monkey: Lorem ipsum dolor sit amet enim. Etiam ullam <img src="' . $includes_path . 'icon_tongue.gif" alt=":PP" class="wp-smiley" /> <br />corper. Suspendisse a pellentesque dui, non felis.<a> <img src="' . $includes_path . 'icon_arrow.gif" alt=":arrow:" class="wp-smiley" /> :arrow</a>'
			),
		);
	}

	/**
	 * @covers ::smilies_init
	 * @covers ::convert_smilies
	 * @dataProvider get_custom_smilies_input_output
	 *
	 * Validate Custom Smilies are converted to images when use_smilies = 1
	 */
	function test_convert_custom_smilies ( $in_txt, $converted_txt ) {
		global $wpsmiliestrans;

		// custom smilies, use_smilies: ON
		update_option( 'use_smilies', 1 );

		if ( !isset( $wpsmiliestrans ) ) {
			smilies_init();
		}

		$trans_orig = $wpsmiliestrans; // save original translations array

		$wpsmiliestrans = array(
		  ':PP' => 'icon_tongue.gif',
		  ':arrow:' => 'icon_arrow.gif',
		  ':monkey:' => 'icon_shock_the_monkey.gif',
		  ':nervou:' => 'icon_nervou.gif'
		);

		smilies_init();

		$this->assertEquals( $converted_txt, convert_smilies($in_txt) );

		// standard smilies, use_smilies: OFF
		update_option( 'use_smilies', 0 );

		$this->assertEquals( $in_txt, convert_smilies($in_txt) );

		$wpsmiliestrans = $trans_orig; // reset original translations array
	}


	/**
	 * DataProvider of HTML elements/tags that smilie matches should be ignored in
	 *
	 */
	public function get_smilies_ignore_tags() {
		return array (
			array( 'pre' ),
			array( 'code' ),
			array( 'script' ),
			array( 'style' ),
			array( 'textarea'),
		);
	}

	/**
	 * Validate Conversion of Smilies is ignored in pre-determined tags
	 * pre, code, script, style
	 *
	 * @covers ::smilies_init
	 * @covers ::convert_smilies
	 * @ticket 16448
	 * @dataProvider get_smilies_ignore_tags
	 */
	public function test_ignore_smilies_in_tags( $element ) {
		$includes_path = includes_url("images/smilies/");

		$in_str = 'Do we ingore smilies ;-) in ' . $element . ' tags <' . $element . '>My Content Here :?: </' . $element . '>';
		$exp_str = 'Do we ingore smilies <img src="' . $includes_path . 'icon_wink.gif" alt=";-)" class="wp-smiley" /> in ' . $element . ' tags <' . $element . '>My Content Here :?: </' . $element . '>';

		// standard smilies, use_smilies: ON
		update_option( 'use_smilies', 1 );
		smilies_init();

		$this->assertEquals( $exp_str, convert_smilies($in_str) );

		// standard smilies, use_smilies: OFF
		update_option( 'use_smilies', 0 );
	}

	/**
	 * DataProvider of Smilie Combinations
	 *
	 */
	public function get_smilies_combinations() {
		$includes_path = includes_url("images/smilies/");

		return array (
			array (
				'8-O :-(',
				'<img src="' . $includes_path . 'icon_eek.gif" alt="8-O" class="wp-smiley" /> <img src="' . $includes_path . 'icon_sad.gif" alt=":-(" class="wp-smiley" />'
			),
			array (
				'8-) 8-O',
				'<img src="' . $includes_path . 'icon_cool.gif" alt="8-)" class="wp-smiley" /> <img src="' . $includes_path . 'icon_eek.gif" alt="8-O" class="wp-smiley" />'
			),
			array (
				'8-) 8O',
				'<img src="' . $includes_path . 'icon_cool.gif" alt="8-)" class="wp-smiley" /> <img src="' . $includes_path . 'icon_eek.gif" alt="8O" class="wp-smiley" />'
			),
			array (
				'8-) :-(',
				'<img src="' . $includes_path . 'icon_cool.gif" alt="8-)" class="wp-smiley" /> <img src="' . $includes_path . 'icon_sad.gif" alt=":-(" class="wp-smiley" />'
			),
			array (
				'8-) :twisted:',
				'<img src="' . $includes_path . 'icon_cool.gif" alt="8-)" class="wp-smiley" /> <img src="' . $includes_path . 'icon_twisted.gif" alt=":twisted:" class="wp-smiley" />'
			),
			array (
				'8O :twisted: :( :? :(',
				'<img src="' . $includes_path . 'icon_eek.gif" alt="8O" class="wp-smiley" /> <img src="' . $includes_path . 'icon_twisted.gif" alt=":twisted:" class="wp-smiley" /> <img src="' . $includes_path . 'icon_sad.gif" alt=":(" class="wp-smiley" /> <img src="' . $includes_path . 'icon_confused.gif" alt=":?" class="wp-smiley" /> <img src="' . $includes_path . 'icon_sad.gif" alt=":(" class="wp-smiley" />'
			),
		);
	}

	/**
	 * Validate Combinations of Smilies separated by single space
	 * are converted correctly
	 *
	 * @covers ::smilies_init
	 * @covers ::convert_smilies
	 * @ticket 20124
	 * @dataProvider get_smilies_combinations
	 */
	public function test_smilies_combinations( $in_txt, $converted_txt ) {
		// custom smilies, use_smilies: ON
		update_option( 'use_smilies', 1 );
		smilies_init();

		$this->assertEquals( $converted_txt, convert_smilies($in_txt) );

		// custom smilies, use_smilies: OFF
		update_option( 'use_smilies', 0 );

		$this->assertEquals( $in_txt, convert_smilies($in_txt) );
	}

	/**
	 * DataProvider of Single Smilies input and converted output
	 *
	 */
	public function get_single_smilies_input_output() {
		$includes_path = includes_url("images/smilies/");

		return array (
			array (
				'8-O :-(',
				'8-O :-('
			),
			array (
				'8O :) additional text here :)',
				'8O <img src="' . $includes_path . 'icon_smile.gif" alt=":)" class="wp-smiley" /> additional text here <img src="' . $includes_path . 'icon_smile.gif" alt=":)" class="wp-smiley" />'
			),
			array (
				':) :) :) :)',
				'<img src="' . $includes_path . 'icon_smile.gif" alt=":)" class="wp-smiley" /> <img src="' . $includes_path . 'icon_smile.gif" alt=":)" class="wp-smiley" /> <img src="' . $includes_path . 'icon_smile.gif" alt=":)" class="wp-smiley" /> <img src="' . $includes_path . 'icon_smile.gif" alt=":)" class="wp-smiley" />'
			),
		);
	}

	/**
	 * Validate Smilies are converted for single smilie in
	 * the $wpsmiliestrans global array
	 *
	 * @covers ::smilies_init
	 * @covers ::convert_smilies
	 * @ticket 25303
	 * @dataProvider get_single_smilies_input_output
	 */
	public function test_single_smilies_in_wpsmiliestrans( $in_txt, $converted_txt ) {
		global $wpsmiliestrans;

		// standard smilies, use_smilies: ON
		update_option( 'use_smilies', 1 );

		if ( !isset( $wpsmiliestrans ) ) {
			smilies_init();
		}

		$orig_trans = $wpsmiliestrans; // save original tranlations array

		$wpsmiliestrans = array (
		  ':)' => 'icon_smile.gif'
		);

		smilies_init();

		$this->assertEquals( $converted_txt, convert_smilies($in_txt) );

		// standard smilies, use_smilies: OFF
		update_option( 'use_smilies', 0 );

		$this->assertEquals( $in_txt, convert_smilies($in_txt) );

		$wpsmiliestrans = $orig_trans; // reset original translations array
	}

	/**
	 * Check that $wp_smiliessearch pattern will match smilies
	 * between spaces, but never capture those spaces.
	 *
	 * Further check that spaces aren't randomly deleted
	 * or added when replacing the text with an image.
	 *
	 * @covers ::smilies_init
	 * @covers ::convert_smilies
	 * @ticket 22692
	 */
	function test_spaces_around_smilies() {
		$nbsp = "\xC2\xA0";

		// standard smilies, use_smilies: ON
		update_option( 'use_smilies', 1 );
		smilies_init();

		$input  = array();
		$output = array();

		$input[]  = 'My test :) smile';
		$output[] = array('test <img ', 'alt=":)"', ' /> smile');

		$input[]  = 'My test ;) smile';
		$output[] = array('test <img ', 'alt=";)"', ' /> smile');

		$input[]  = 'My test &nbsp;:)&nbsp;smile';
		$output[] = array('test &nbsp;<img ', 'alt=":)"', ' />&nbsp;smile');

		$input[]  = 'My test &nbsp;;)&nbsp;smile';
		$output[] = array('test &nbsp;<img ', 'alt=";)"', ' />&nbsp;smile');

		$input[]  = "My test {$nbsp}:){$nbsp}smile";
		$output[] = array("test {$nbsp}<img ", 'alt=":)"', " />{$nbsp}smile");

		$input[]  = "My test {$nbsp};){$nbsp}smile";
		$output[] = array("test {$nbsp}<img ", 'alt=";)"', " />{$nbsp}smile");

		foreach($input as $key => $in) {
			$result = convert_smilies( $in );
			foreach($output[$key] as $out) {

				// Each output element must appear in the results.
				$this->assertContains( $out, $result );

			}
		}

		// standard smilies, use_smilies: OFF
		update_option( 'use_smilies', 0 );
	}

	/**
	 * @covers ::stripslashes_deep
	 * @ticket 18026
	 */
	function test_stripslashes_deep_preserves_original_datatype() {

		$this->assertEquals( true, stripslashes_deep( true ) );
		$this->assertEquals( false, stripslashes_deep( false ) );
		$this->assertEquals( 4, stripslashes_deep( 4 ) );
		$this->assertEquals( 'foo', stripslashes_deep( 'foo' ) );
		$arr = array( 'a' => true, 'b' => false, 'c' => 4, 'd' => 'foo' );
		$arr['e'] = $arr; // Add a sub-array
		$this->assertEquals( $arr, stripslashes_deep( $arr ) ); // Keyed array
		$this->assertEquals( array_values( $arr ), stripslashes_deep( array_values( $arr ) ) ); // Non-keyed

		$obj = new stdClass;
		foreach ( $arr as $k => $v )
			$obj->$k = $v;
		$this->assertEquals( $obj, stripslashes_deep( $obj ) );
	}

	/**
	 * @covers ::stripslashes_deep
	 */
	function test_stripslashes_deep_strips_slashes() {
		$old = "I can\'t see, isn\'t that it?";
		$new = "I can't see, isn't that it?";
		$this->assertEquals( $new, stripslashes_deep( $old ) );
		$this->assertEquals( $new, stripslashes_deep( "I can\\'t see, isn\\'t that it?" ) );
		$this->assertEquals( array( 'a' => $new ), stripslashes_deep( array( 'a' => $old ) ) ); // Keyed array
		$this->assertEquals( array( $new ), stripslashes_deep( array( $old ) ) ); // Non-keyed

		$obj_old = new stdClass;
		$obj_old->a = $old;
		$obj_new = new stdClass;
		$obj_new->a = $new;
		$this->assertEquals( $obj_new, stripslashes_deep( $obj_old ) );
	}

	/**
	 * @covers ::stripslashes_deep
	 */
	function test_stripslashes_deep_permits_escaped_slash() {
		$txt = "I can't see, isn\'t that it?";
		$this->assertEquals( $txt, stripslashes_deep( "I can\'t see, isn\\\'t that it?" ) );
		$this->assertEquals( $txt, stripslashes_deep( "I can\'t see, isn\\\\\'t that it?" ) );
	}

	/**
	 * @covers ::preg_replace_callback
	 * @dataProvider preg_replace_callback_data
	 */
	function test_preg_replace_callback_convert_urlencoded_to_entities( $u_urlencoded, $entity ) {
		$this->assertEquals( $entity, preg_replace_callback('/\%u([0-9A-F]{4})/', '_convert_urlencoded_to_entities', $u_urlencoded ), $entity );
	}

	function preg_replace_callback_data() {
		$input  = file( DIR_TESTDATA . '/formatting/utf-8/u-urlencoded.txt' );
		$output = file( DIR_TESTDATA . '/formatting/utf-8/entitized.txt' );
		$data_provided = array();
		foreach ( $input as $key => $value ) {
			$data_provided[] = array( trim( $value ), trim( $output[ $key ] ) );
		}
		return $data_provided;
	}

	/**
	 * Non-ASCII UTF-8 characters should be percent encoded. Spaces etc.
	 * are dealt with elsewhere.
	 *
	 * @covers ::utf8_uri_encode
	 * @dataProvider utf8_uri_encode_data
	 */
	function test_utf8_uri_encode_percent_encodes_non_reserved_characters( $utf8, $urlencoded ) {
		$this->assertEquals($urlencoded, utf8_uri_encode( $utf8 ) );
	}

	/**
	 * @covers ::utf8_uri_encode
	 * @dataProvider utf8_uri_encode_data
	 */
	function test_utf8_uri_encode_output_is_not_longer_than_optional_length_argument( $utf8, $unused_for_this_test ) {
		$max_length = 30;
		$this->assertTrue( strlen( utf8_uri_encode( $utf8, $max_length ) ) <= $max_length );
	}

	function utf8_uri_encode_data() {
		$utf8_urls  = file( DIR_TESTDATA . '/formatting/utf-8/utf-8.txt' );
		$urlencoded = file( DIR_TESTDATA . '/formatting/utf-8/urlencoded.txt' );
        $data_provided = array();
		foreach ( $utf8_urls as $key => $value ) {
			$data_provided[] = array( trim( $value ), trim( $urlencoded[ $key ] ) );
		}
		return $data_provided;
	}

	/**
	 * @covers ::wp_basename
	 */
	function test_wp_basename_unix() {
		$this->assertEquals('file',
			wp_basename('/home/test/file'));
	}

	/**
	 * @covers ::wp_basename
	 */
	function test_wp_basename_unix_utf8_support() {
		$this->assertEquals('žluťoučký kůň.txt',
			wp_basename('/test/žluťoučký kůň.txt'));
	}

	/**
	 * @covers ::wp_basename
	 * @ticket 22138
	 */
	function test_wp_basename_windows() {
		$this->assertEquals('file.txt',
			wp_basename('C:\Documents and Settings\User\file.txt'));
	}

	/**
	 * @covers ::wp_basename
	 * @ticket 22138
	 */
	function test_wp_basename_windows_utf8_support() {
		$this->assertEquals('щипцы.txt',
			wp_basename('C:\test\щипцы.txt'));
	}

	function _wp_htmledit_pre_charset_iso_8859_1() {
		return 'iso-8859-1';
	}

	/**
	 * Only fails in PHP 5.4 onwards
	 * @covers ::wp_htmledit_pre
	 * @ticket 23688
	 */
	function test_wp_htmledit_pre_charset_iso_8859_1() {
		add_filter( 'pre_option_blog_charset', array( $this, '_wp_htmledit_pre_charset_iso_8859_1' ) );
		$iso8859_1 = 'Fran' .chr(135) .'ais';
		$this->assertEquals( $iso8859_1, wp_htmledit_pre( $iso8859_1 ) );
		remove_filter( 'pre_option_blog_charset', array( $this, '_wp_htmledit_pre_charset_iso_8859_1' ) );
	}

	function _wp_htmledit_pre_charset_utf_8() {
		return 'UTF-8';
	}

	/**
	 * @covers ::wp_htmledit_pre
	 * @ticket 23688
	 */
	function test_wp_htmledit_pre_charset_utf_8() {
		add_filter( 'pre_option_blog_charset', array( $this, '_wp_htmledit_pre_charset_utf_8' ) );
		$utf8 = 'Fran' .chr(195) . chr(167) .'ais';
		$this->assertEquals( $utf8, wp_htmledit_pre( $utf8 ) );
		remove_filter( 'pre_option_blog_charset', array( $this, '_wp_htmledit_pre_charset_utf_8' ) );
	}

	function _wp_richedit_pre_charset_iso_8859_1() {
		return 'iso-8859-1';
	}

	/**
	 * Only fails in PHP 5.4 onwards
	 * @covers ::wp_richedit_pre
	 * @ticket 23688
	 */
	function test_wp_richedit_pre_charset_iso_8859_1() {
		add_filter( 'pre_option_blog_charset', array( $this, '_wp_richedit_pre_charset_iso_8859_1' ) );
		$iso8859_1 = 'Fran' .chr(135) .'ais';
		$this->assertEquals( '&lt;p&gt;' . $iso8859_1 . "&lt;/p&gt;\n", wp_richedit_pre( $iso8859_1 ) );
		remove_filter( 'pre_option_blog_charset', array( $this, '_wp_richedit_pre_charset_iso_8859_1' ) );
	}

	function _wp_richedit_pre_charset_utf_8() {
		return 'UTF-8';
	}

	/**
	 * @covers ::wp_richedit_pre
	 * @ticket 23688
	 */
	function test_wp_richedit_pre_charset_utf_8() {
		add_filter( 'pre_option_blog_charset', array( $this, '_wp_richedit_pre_charset_utf_8' ) );
		$utf8 = 'Fran' .chr(195) . chr(167) .'ais';
		$this->assertEquals( '&lt;p&gt;' . $utf8 . "&lt;/p&gt;\n", wp_richedit_pre( $utf8 ) );
		remove_filter( 'pre_option_blog_charset', array( $this, '_wp_richedit_pre_charset_utf_8' ) );
	}

	/**
	 * @covers ::_wp_specialchars
	 */
	function test_wp_specialchars_basics() {
		$html =  "&amp;&lt;hello world&gt;";
		$this->assertEquals( $html, _wp_specialchars( $html ) );

		$double = "&amp;amp;&amp;lt;hello world&amp;gt;";
		$this->assertEquals( $double, _wp_specialchars( $html, ENT_NOQUOTES, false, true ) );
	}

	/**
	 * @covers ::_wp_specialchars
	 */
	function test_wp_specialchars_allowed_entity_names() {
		global $allowedentitynames;

		// Allowed entities should be unchanged
		foreach ( $allowedentitynames as $ent ) {
			$ent = '&' . $ent . ';';
			$this->assertEquals( $ent, _wp_specialchars( $ent ) );
		}
	}

	/**
	 * @covers ::_wp_specialchars
	 */
	function test_wp_specialchars_not_allowed_entity_names() {
		$ents = array( 'iacut', 'aposs', 'pos', 'apo', 'apo?', 'apo.*', '.*apo.*', 'apos ', ' apos', ' apos ' );

		foreach ( $ents as $ent ) {
			$escaped = '&amp;' . $ent . ';';
			$ent = '&' . $ent . ';';
			$this->assertEquals( $escaped, _wp_specialchars( $ent ) );
		}
	}

	/**
	 * @covers ::_wp_specialchars
	 */
	function test_wp_specialchars_optionally_escapes_quotes() {
		$source = "\"'hello!'\"";
		$this->assertEquals( '"&#039;hello!&#039;"', _wp_specialchars($source, 'single') );
		$this->assertEquals( "&quot;'hello!'&quot;", _wp_specialchars($source, 'double') );
		$this->assertEquals( '&quot;&#039;hello!&#039;&quot;', _wp_specialchars($source, true) );
		$this->assertEquals( $source, _wp_specialchars($source) );
	}

	/**
	 * @covers ::wp_strip_all_tags
	 */
	function test_wp_strip_all_tags() {
		$text = 'lorem<br />ipsum';
		$this->assertEquals( 'loremipsum', wp_strip_all_tags( $text ) );

		$text = "lorem<br />\nipsum";
		$this->assertEquals( "lorem\nipsum", wp_strip_all_tags( $text ) );

		// test removing breaks is working
		$text = "lorem<br />ipsum";
		$this->assertEquals( "loremipsum", wp_strip_all_tags( $text, true ) );

		// test script / style tag's contents is removed
		$text = "lorem<script>alert(document.cookie)</script>ipsum";
		$this->assertEquals( "loremipsum", wp_strip_all_tags( $text ) );

		$text = "lorem<style>* { display: 'none' }</style>ipsum";
		$this->assertEquals( "loremipsum", wp_strip_all_tags( $text ) );

		// test "marlformed" markup of contents
		$text = "lorem<style>* { display: 'none' }<script>alert( document.cookie )</script></style>ipsum";
		$this->assertEquals( "loremipsum", wp_strip_all_tags( $text ) );
	}

	/**
	 * @covers ::wptexturize
	 */
	function test_wptexturize_dashes() {
		$this->assertEquals('Hey &#8212; boo?', wptexturize('Hey -- boo?'));
		$this->assertEquals('<a href="http://xx--xx">Hey &#8212; boo?</a>', wptexturize('<a href="http://xx--xx">Hey -- boo?</a>'));
	}

	/**
	 * @covers ::wptexturize
	 */
	function test_wptexturize_disable() {
		$this->assertEquals('<pre>---</pre>', wptexturize('<pre>---</pre>'));
		$this->assertEquals('<pre><code></code>--</pre>', wptexturize('<pre><code></code>--</pre>'));

		$this->assertEquals( '<code>---</code>',     wptexturize( '<code>---</code>'     ) );
		$this->assertEquals( '<kbd>---</kbd>',       wptexturize( '<kbd>---</kbd>'       ) );
		$this->assertEquals( '<style>---</style>',   wptexturize( '<style>---</style>'   ) );
		$this->assertEquals( '<script>---</script>', wptexturize( '<script>---</script>' ) );
		$this->assertEquals( '<tt>---</tt>',         wptexturize( '<tt>---</tt>'         ) );

		$this->assertEquals('<code>href="baba"</code> &#8220;baba&#8221;', wptexturize('<code>href="baba"</code> "baba"'));

		$enabled_tags_inside_code = '<code>curl -s <a href="http://x/">baba</a> | grep sfive | cut -d "\"" -f 10 &gt; topmp3.txt</code>';
		$this->assertEquals($enabled_tags_inside_code, wptexturize($enabled_tags_inside_code));

		$double_nest = '<pre>"baba"<code>"baba"<pre></pre></code>"baba"</pre>';
		$this->assertEquals($double_nest, wptexturize($double_nest));

		$invalid_nest = '<pre></code>"baba"</pre>';
		$this->assertEquals($invalid_nest, wptexturize($invalid_nest));

	}

	/**
	 * @covers ::wptexturize
	 * @ticket 1418
	 */
	function test_wptexturize_bracketed_quotes_1418() {
		$this->assertEquals('(&#8220;test&#8221;)', wptexturize('("test")'));
		$this->assertEquals('(&#8216;test&#8217;)', wptexturize("('test')"));
		$this->assertEquals('(&#8217;twas)', wptexturize("('twas)"));
	}

	/**
	 * @covers ::wptexturize
	 * @ticket 3810
	 */
	function test_wptexturize_bracketed_quotes_3810() {
		$this->assertEquals('A dog (&#8220;Hubertus&#8221;) was sent out.', wptexturize('A dog ("Hubertus") was sent out.'));
	}

	/**
	 * @covers ::wptexturize
	 * @ticket 4539
	 */
	function test_wptexturize_basic_quotes() {
		$this->assertEquals('test&#8217;s', wptexturize('test\'s'));

		$this->assertEquals('&#8216;quoted&#8217;', wptexturize('\'quoted\''));
		$this->assertEquals('&#8220;quoted&#8221;', wptexturize('"quoted"'));

		$this->assertEquals('space before &#8216;quoted&#8217; space after', wptexturize('space before \'quoted\' space after'));
		$this->assertEquals('space before &#8220;quoted&#8221; space after', wptexturize('space before "quoted" space after'));

		$this->assertEquals('(&#8216;quoted&#8217;)', wptexturize('(\'quoted\')'));
		$this->assertEquals('{&#8220;quoted&#8221;}', wptexturize('{"quoted"}'));

		$this->assertEquals('&#8216;qu(ot)ed&#8217;', wptexturize('\'qu(ot)ed\''));
		$this->assertEquals('&#8220;qu{ot}ed&#8221;', wptexturize('"qu{ot}ed"'));

		$this->assertEquals(' &#8216;test&#8217;s quoted&#8217; ', wptexturize(' \'test\'s quoted\' '));
		$this->assertEquals(' &#8220;test&#8217;s quoted&#8221; ', wptexturize(' "test\'s quoted" '));
	}

	/**
	 * @covers ::wptexturize
	 * @ticket 4539
	 * @ticket 15241
	 */
	function test_wptexturize_full_sentences_with_unmatched_single_quotes() {
		$this->assertEquals(
			'That means every moment you&#8217;re working on something without it being in the public it&#8217;s actually dying.',
			wptexturize("That means every moment you're working on something without it being in the public it's actually dying.")
		);
	}

	/**
	 * @covers ::wptexturize
	 * @ticket 4539
	 */
	function test_wptexturize_quotes() {
		$this->assertEquals('&#8220;Quoted String&#8221;', wptexturize('"Quoted String"'));
		//$this->assertEquals('Here is &#8220;<a href="http://example.com">a test with a link</a>&#8221;', wptexturize('Here is "<a href="http://example.com">a test with a link</a>"'));
		//$this->assertEquals('Here is &#8220;<a href="http://example.com">a test with a link and a period</a>&#8221;.', wptexturize('Here is "<a href="http://example.com">a test with a link and a period</a>".'));
		$this->assertEquals('Here is &#8220;<a href="http://example.com">a test with a link</a>&#8221; and a space.', wptexturize('Here is "<a href="http://example.com">a test with a link</a>" and a space.'));
		$this->assertEquals('Here is &#8220;<a href="http://example.com">a test with a link</a> and some text quoted&#8221;', wptexturize('Here is "<a href="http://example.com">a test with a link</a> and some text quoted"'));
		//$this->assertEquals('Here is &#8220;<a href="http://example.com">a test with a link</a>&#8221;, and a comma.', wptexturize('Here is "<a href="http://example.com">a test with a link</a>", and a comma.'));
		//$this->assertEquals('Here is &#8220;<a href="http://example.com">a test with a link</a>&#8221;; and a semi-colon.', wptexturize('Here is "<a href="http://example.com">a test with a link</a>"; and a semi-colon.'));
		//$this->assertEquals('Here is &#8220;<a href="http://example.com">a test with a link</a>&#8221;- and a dash.', wptexturize('Here is "<a href="http://example.com">a test with a link</a>"- and a dash.'));
		//$this->assertEquals('Here is &#8220;<a href="http://example.com">a test with a link</a>&#8221;&#8230; and ellipses.', wptexturize('Here is "<a href="http://example.com">a test with a link</a>"... and ellipses.'));
		//$this->assertEquals('Here is &#8220;a test <a href="http://example.com">with a link</a>&#8221;.', wptexturize('Here is "a test <a href="http://example.com">with a link</a>".'));
		//$this->assertEquals('Here is &#8220;<a href="http://example.com">a test with a link</a>&#8221;and a work stuck to the end.', wptexturize('Here is "<a href="http://example.com">a test with a link</a>"and a work stuck to the end.'));
		//$this->assertEquals('A test with a finishing number, &#8220;like 23&#8221;.', wptexturize('A test with a finishing number, "like 23".'));
		//$this->assertEquals('A test with a number, &#8220;like 62&#8221;, is nice to have.', wptexturize('A test with a number, "like 62", is nice to have.'));
	}

	/**
	 * @covers ::wptexturize
	 * @ticket 4539
	 */
	function test_wptexturize_quotes_before_s() {
		$this->assertEquals('test&#8217;s', wptexturize("test's"));
		$this->assertEquals('&#8216;test&#8217;s', wptexturize("'test's"));
		$this->assertEquals('&#8216;test&#8217;s&#8217;', wptexturize("'test's'"));
		$this->assertEquals('&#8216;string&#8217;', wptexturize("'string'"));
		$this->assertEquals('&#8216;string&#8217;s&#8217;', wptexturize("'string's'"));
	}

	/**
	 * @covers ::wptexturize
	 * @ticket 4539
	 */
	function test_wptexturize_quotes_before_numbers() {
		$this->assertEquals('Class of &#8217;99', wptexturize("Class of '99"));
		$this->assertEquals('Class of &#8217;99&#8217;s', wptexturize("Class of '99's"));
		$this->assertEquals('&#8216;Class of &#8217;99&#8217;', wptexturize("'Class of '99'"));
		$this->assertEquals('&#8216;Class of &#8217;99&#8217;s&#8217;', wptexturize("'Class of '99's'"));
		$this->assertEquals('&#8216;Class of &#8217;99&#8217;s&#8217;', wptexturize("'Class of '99&#8217;s'"));
		//$this->assertEquals('&#8220;Class of 99&#8221;', wptexturize("\"Class of 99\""));
		$this->assertEquals('&#8220;Class of &#8217;99&#8221;', wptexturize("\"Class of '99\""));
		$this->assertEquals('{&#8220;Class of &#8217;99&#8221;}', wptexturize("{\"Class of '99\"}"));
		$this->assertEquals(' &#8220;Class of &#8217;99&#8221; ', wptexturize(" \"Class of '99\" "));
		$this->assertEquals('}&#8221;Class of &#8217;99&#8243;{', wptexturize("}\"Class of '99\"{")); // Not a quotation, may be between two other quotations.
	}

	/**
	 * @covers ::wptexturize
	 */
	function test_wptexturize_quotes_after_numbers() {
		$this->assertEquals('Class of &#8217;99', wptexturize("Class of '99"));
	}

	/**
	 * @covers ::wptexturize
	 * @ticket 4539
	 * @ticket 15241
	 */
	function test_wptexturize_other_html() {
		$this->assertEquals('&#8216;<strong>', wptexturize("'<strong>"));
		//$this->assertEquals('&#8216;<strong>Quoted Text</strong>&#8217;,', wptexturize("'<strong>Quoted Text</strong>',"));
		//$this->assertEquals('&#8220;<strong>Quoted Text</strong>&#8221;,', wptexturize('"<strong>Quoted Text</strong>",'));
	}

	/**
	 * @covers ::wptexturize
	 */
	function test_wptexturize_x() {
		$this->assertEquals('14&#215;24', wptexturize("14x24"));
	}

	/**
	 * @covers ::wptexturize
	 */
	function test_wptexturize_minutes_seconds() {
		$this->assertEquals('9&#8242;', wptexturize('9\''));
		$this->assertEquals('9&#8243;', wptexturize("9\""));

		$this->assertEquals('a 9&#8242; b', wptexturize('a 9\' b'));
		$this->assertEquals('a 9&#8243; b', wptexturize("a 9\" b"));

		$this->assertEquals('&#8220;a 9&#8242; b&#8221;', wptexturize('"a 9\' b"'));
		$this->assertEquals('&#8216;a 9&#8243; b&#8217;', wptexturize("'a 9\" b'"));
	}

	/**
	 * @covers ::wptexturize
	 * @ticket 8775
	 */
	function test_wptexturize_quotes_around_numbers() {
		$this->assertEquals('&#8220;12345&#8221;', wptexturize('"12345"'));
		$this->assertEquals('&#8216;12345&#8217;', wptexturize('\'12345\''));
		$this->assertEquals('&#8220;a 9&#8242; plus a &#8216;9&#8217;, maybe a 9&#8242; &#8216;9&#8217;&#8221;', wptexturize('"a 9\' plus a \'9\', maybe a 9\' \'9\'"'));
		$this->assertEquals('<p>&#8217;99<br />&#8216;123&#8217;<br />&#8217;tis<br />&#8216;s&#8217;</p>', wptexturize('<p>\'99<br />\'123\'<br />\'tis<br />\'s\'</p>'));
	}

	/**
	 * @covers ::wptexturize
	 * @ticket 8912
	 */
	function test_wptexturize_html_comments() {
		$this->assertEquals('<!--[if !IE]>--><!--<![endif]-->', wptexturize('<!--[if !IE]>--><!--<![endif]-->'));
		$this->assertEquals('<!--[if !IE]>"a 9\' plus a \'9\', maybe a 9\' \'9\' "<![endif]-->', wptexturize('<!--[if !IE]>"a 9\' plus a \'9\', maybe a 9\' \'9\' "<![endif]-->'));
		$this->assertEquals('<ul><li>Hello.</li><!--<li>Goodbye.</li>--></ul>', wptexturize('<ul><li>Hello.</li><!--<li>Goodbye.</li>--></ul>'));
	}

	/**
	 * @covers ::wptexturize
	 * @ticket 4539
	 * @ticket 15241
	 */
	function test_wptexturize_entity_quote_cuddling() {
		$this->assertEquals('&nbsp;&#8220;Testing&#8221;', wptexturize('&nbsp;"Testing"'));
		//$this->assertEquals('&#38;&#8220;Testing&#8221;', wptexturize('&#38;"Testing"'));
	}

	/**
	 * @covers ::wptexturize
	 * @ticket 22823
	 */
	function test_wptexturize_apostrophes_before_primes() {
		$this->assertEquals( 'WordPress 3.5&#8217;s release date', wptexturize( "WordPress 3.5's release date" ) );
	}

	/**
	 * @covers ::wptexturize
	 * @ticket 23185
	 */
	function test_wptexturize_spaces_around_hyphens() {
		$nbsp = "\xC2\xA0";

		$this->assertEquals( ' &#8211; ', wptexturize( ' - ' ) );
		$this->assertEquals( '&nbsp;&#8211;&nbsp;', wptexturize( '&nbsp;-&nbsp;' ) );
		$this->assertEquals( ' &#8211;&nbsp;', wptexturize( ' -&nbsp;' ) );
		$this->assertEquals( '&nbsp;&#8211; ', wptexturize( '&nbsp;- ') );
		$this->assertEquals( "$nbsp&#8211;$nbsp", wptexturize( "$nbsp-$nbsp" ) );
		$this->assertEquals( " &#8211;$nbsp", wptexturize( " -$nbsp" ) );
		$this->assertEquals( "$nbsp&#8211; ", wptexturize( "$nbsp- ") );

		$this->assertEquals( ' &#8212; ', wptexturize( ' -- ' ) );
		$this->assertEquals( '&nbsp;&#8212;&nbsp;', wptexturize( '&nbsp;--&nbsp;' ) );
		$this->assertEquals( ' &#8212;&nbsp;', wptexturize( ' --&nbsp;' ) );
		$this->assertEquals( '&nbsp;&#8212; ', wptexturize( '&nbsp;-- ') );
		$this->assertEquals( "$nbsp&#8212;$nbsp", wptexturize( "$nbsp--$nbsp" ) );
		$this->assertEquals( " &#8212;$nbsp", wptexturize( " --$nbsp" ) );
		$this->assertEquals( "$nbsp&#8212; ", wptexturize( "$nbsp-- ") );
	}

	/**
	 * @covers ::wptexturize
	 * @ticket 31030
	 */
	function test_wptexturize_hyphens_at_start_and_end() {
		$this->assertEquals( '&#8211; ', wptexturize( '- ' ) );
		$this->assertEquals( '&#8211; &#8211;', wptexturize( '- -' ) );
		$this->assertEquals( ' &#8211;', wptexturize( ' -' ) );

		$this->assertEquals( '&#8212; ', wptexturize( '-- ' ) );
		$this->assertEquals( '&#8212; &#8212;', wptexturize( '-- --' ) );
		$this->assertEquals( ' &#8212;', wptexturize( ' --' ) );
	}

	/**
	 * Test spaces around quotes.
	 *
	 * These should never happen, even if the desired output changes some day.
	 *
	 * @covers ::wptexturize
	 * @ticket 22692
	 */
	function test_wptexturize_spaces_around_quotes_never() {
		$nbsp = "\xC2\xA0";

		$problem_input  = "$nbsp\"A";
		$problem_output = "$nbsp&#8221;A";

		$this->assertNotEquals( $problem_output, wptexturize( $problem_input ) );
	}

	/**
	 * Test spaces around quotes.
	 *
	 * These are desirable outputs for the current design.
	 *
	 * @covers ::wptexturize
	 * @ticket 22692
	 * @dataProvider wptexturize_data_spaces_around_quotes
	 */
	function test_wptexturize_spaces_around_quotes( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_spaces_around_quotes() {
		$nbsp = "\xC2\xA0";
		$pi   = "\xCE\xA0";

		return array(
			array(
				"stop. $nbsp\"A quote after 2 spaces.\"",
				"stop. $nbsp&#8220;A quote after 2 spaces.&#8221;",
			),
			array(
				"stop.$nbsp$nbsp\"A quote after 2 spaces.\"",
				"stop.$nbsp$nbsp&#8220;A quote after 2 spaces.&#8221;",
			),
			array(
				"stop. $nbsp'A quote after 2 spaces.'",
				"stop. $nbsp&#8216;A quote after 2 spaces.&#8217;",
			),
			array(
				"stop.$nbsp$nbsp'A quote after 2 spaces.'",
				"stop.$nbsp$nbsp&#8216;A quote after 2 spaces.&#8217;",
			),
			array(
				"stop. &nbsp;\"A quote after 2 spaces.\"",
				"stop. &nbsp;&#8220;A quote after 2 spaces.&#8221;",
			),
			array(
				"stop.&nbsp;&nbsp;\"A quote after 2 spaces.\"",
				"stop.&nbsp;&nbsp;&#8220;A quote after 2 spaces.&#8221;",
			),
			array(
				"stop. &nbsp;'A quote after 2 spaces.'",
				"stop. &nbsp;&#8216;A quote after 2 spaces.&#8217;",
			),
			array(
				"stop.&nbsp;&nbsp;'A quote after 2 spaces.'",
				"stop.&nbsp;&nbsp;&#8216;A quote after 2 spaces.&#8217;",
			),
			array(
				"Contraction: $pi's",
				"Contraction: $pi&#8217;s",
			),
		);
	}

	/**
	 * Apostrophe before a number always becomes &#8217 (apos);
	 *
	 * Checks all baseline patterns. If anything ever changes in wptexturize(), these tests may fail.
	 *
	 * @covers ::wptexturize
	 * @ticket 22692
	 * @dataProvider wptexturize_data_apos_before_digits
	 */
	function test_wptexturize_apos_before_digits( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_apos_before_digits() {
		return array(
			array(
				"word '99 word",
				"word &#8217;99 word",
			),
			array(
				"word'99 word",
				"word&#8217;99 word",
			),
			array(
				"word '99word",
				"word &#8217;99word",
			),
			array(
				"word'99word",
				"word&#8217;99word",
			),
			array(
				"word '99&#8217;s word", // Appears as a separate but logically superfluous pattern in 3.8.
				"word &#8217;99&#8217;s word",
			),
			array(
				"according to our source, '33 students scored less than 50' on the test.", // Apostrophes and primes have priority over quotes
				"according to our source, &#8217;33 students scored less than 50&#8242; on the test.",
			),
		);
	}

	/**
	 * Apostrophe after a space or ([{<" becomes &#8216; (opening_single_quote)
	 *
	 * Checks all baseline patterns. If anything ever changes in wptexturize(), these tests may fail.
	 *
	 * @covers ::wptexturize
	 * @ticket 22692
	 * @dataProvider wptexturize_data_opening_single_quote
	 */
	function test_wptexturize_opening_single_quote( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_opening_single_quote() {
		return array(
			array(
				"word 'word word",
				"word &#8216;word word",
			),
			array(
				"word ('word word",
				"word (&#8216;word word",
			),
			array(
				"word ['word word",
				"word [&#8216;word word",
			),
			array(
				"word <'word word", // Invalid HTML input triggers the apos in a word pattern.
				"word <&#8217;word word",
			),
			array(
				"word &lt;'word word", // Valid HTML input makes curly quotes.
				"word &lt;&#8216;word word",
			),
			array(
				"word {'word word",
				"word {&#8216;word word",
			),
			array(
				"word \"'word word",
				"word &#8220;&#8216;word word", // Two opening quotes
			),
			array(
				"'word word",
				"&#8216;word word",
			),
			array(
				"word('word word",
				"word(&#8216;word word",
			),
			array(
				"word['word word",
				"word[&#8216;word word",
			),
			array(
				"word<'word word",
				"word<&#8217;word word",
			),
			array(
				"word&lt;'word word",
				"word&lt;&#8216;word word",
			),
			array(
				"word{'word word",
				"word{&#8216;word word",
			),
			array(
				"word\"'word word",
				"word&#8221;&#8216;word word", // Closing quote, then opening quote
			),
			array(
				"word ' word word",
				"word &#8216; word word",
			),
			array(
				"word (' word word",
				"word (&#8216; word word",
			),
			array(
				"word [' word word",
				"word [&#8216; word word",
			),
			array(
				"word <' word word",
				"word <&#8217; word word",
			),
			array(
				"word &lt;' word word",
				"word &lt;&#8216; word word",
			),
			array(
				"word {' word word",
				"word {&#8216; word word",
			),
			array(
				"word \"' word word",
				"word &#8220;&#8216; word word", // Two opening quotes
			),
			array(
				"' word word",
				"&#8216; word word",
			),
			array(
				"word(' word word",
				"word(&#8216; word word",
			),
			array(
				"word[' word word",
				"word[&#8216; word word",
			),
			array(
				"word<' word word",
				"word<&#8217; word word",
			),
			array(
				"word&lt;' word word",
				"word&lt;&#8216; word word",
			),
			array(
				"word{' word word",
				"word{&#8216; word word",
			),
			array(
				"word\"' word word",
				"word&#8221;&#8216; word word", // Closing quote, then opening quote
			),
		);
	}

	/**
	 * Double quote after a number becomes &#8243; (double_prime)
	 *
	 * Checks all baseline patterns. If anything ever changes in wptexturize(), these tests may fail.
	 *
	 * @covers ::wptexturize
	 * @ticket 22692
	 * @dataProvider wptexturize_data_double_prime
	 */
	function test_wptexturize_double_prime( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_double_prime() {
		return array(
			array(
				'word 99" word',
				'word 99&#8243; word',
			),
			array(
				'word 99"word',
				'word 99&#8243;word',
			),
			array(
				'word99" word',
				'word99&#8243; word',
			),
			array(
				'word99"word',
				'word99&#8243;word',
			),
		);
	}

	/**
	 * Apostrophe after a number becomes &#8242; (prime)
	 *
	 * Checks all baseline patterns. If anything ever changes in wptexturize(), these tests may fail.
	 *
	 * @covers ::wptexturize
	 * @ticket 22692
	 * @dataProvider wptexturize_data_single_prime
	 */
	function test_wptexturize_single_prime( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_single_prime() {
		return array(
			array(
				"word 99' word",
				"word 99&#8242; word",
			),
			array(
				"word 99'word", // Not a prime anymore. Apostrophes get priority.
				"word 99&#8217;word",
			),
			array(
				"word99' word",
				"word99&#8242; word",
			),
			array(
				"word99'word", // Not a prime anymore.
				"word99&#8217;word",
			),
		);
	}

	/**
	 * Apostrophe "in a word" becomes &#8217; (apos)
	 *
	 * Checks all baseline patterns. If anything ever changes in wptexturize(), these tests may fail.
	 *
	 * @covers ::wptexturize
	 * @ticket 22692
	 * @dataProvider wptexturize_data_contractions
	 */
	function test_wptexturize_contractions( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_contractions() {
		return array(
			array(
				"word word's word",
				"word word&#8217;s word",
			),
			array(
				"word'[ word", // Apostrophes are never followed by opening punctuation.
				"word'[ word",
			),
			array(
				"word'( word",
				"word'( word",
			),
			array(
				"word'{ word",
				"word'{ word",
			),
			array(
				"word'&lt; word",
				"word'&lt; word",
			),
			array(
				"word'< word", // Invalid HTML input does trigger the apos pattern.
				"word&#8217;< word",
			),
		);
	}

	/**
	 * Double quote after a space or ([-{< becomes &#8220; (opening_quote) if not followed by spaces
	 *
	 * Checks all baseline patterns. If anything ever changes in wptexturize(), these tests may fail.
	 *
	 * @covers ::wptexturize
	 * @ticket 22692
	 * @dataProvider wptexturize_data_opening_quote
	 */
	function test_wptexturize_opening_quote( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_opening_quote() {
		return array(
			array(
				'word "word word',
				'word &#8220;word word',
			),
			array(
				'word ("word word',
				'word (&#8220;word word',
			),
			array(
				'word ["word word',
				'word [&#8220;word word',
			),
			array(
				'word <"word word', // Invalid HTML input triggers the closing quote pattern.
				'word <&#8221;word word',
			),
			array(
				'word &lt;"word word',
				'word &lt;&#8220;word word',
			),
			array(
				'word {"word word',
				'word {&#8220;word word',
			),
			array(
				'word -"word word',
				'word -&#8220;word word',
			),
			array(
				'word-"word word',
				'word-&#8220;word word',
			),
			array(
				'"word word',
				'&#8220;word word',
			),
			array(
				'word("word word',
				'word(&#8220;word word',
			),
			array(
				'word["word word',
				'word[&#8220;word word',
			),
			array(
				'word<"word word',
				'word<&#8221;word word',
			),
			array(
				'word&lt;"word word',
				'word&lt;&#8220;word word',
			),
			array(
				'word{"word word',
				'word{&#8220;word word',
			),
			array(
				'word "99 word',
				'word &#8220;99 word',
			),
		);
	}

	/**
	 * Double quote becomes &#8221; (closing_quote) unless it is already converted to double_prime or opening_quote.
	 *
	 * Checks all baseline patterns. If anything ever changes in wptexturize(), these tests may fail.
	 *
	 * @covers ::wptexturize
	 * @ticket 22692
	 * @dataProvider wptexturize_data_closing_quote
	 */
	function test_wptexturize_closing_quote( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_closing_quote() {
		return array(
			array(
				'word word" word',
				'word word&#8221; word',
			),
			array(
				'word word") word',
				'word word&#8221;) word',
			),
			array(
				'word word"] word',
				'word word&#8221;] word',
			),
			array(
				'word word"} word',
				'word word&#8221;} word',
			),
			array(
				'word word"> word', // Invalid HTML input?
				'word word&#8221;> word',
			),
			array(
				'word word"&gt; word', // Valid HTML should work
				'word word&#8221;&gt; word',
			),
			array(
				'word word"',
				'word word&#8221;',
			),
			array(
				'word word"word',
				'word word&#8221;word',
			),
			array(
				'word"word"word',
				'word&#8221;word&#8221;word',
			),
			array(
				'test sentence".',
				'test sentence&#8221;.',
			),
			array(
				'test sentence."',
				'test sentence.&#8221;',
			),
			array(
				'test sentence". word',
				'test sentence&#8221;. word',
			),
			array(
				'test sentence." word',
				'test sentence.&#8221; word',
			),
		);
	}

	/**
	 * Test that single quotes followed by a space or .,-)}]> become &#8217; (closing_single_quote)
	 *
	 * Checks all baseline patterns. If anything ever changes in wptexturize(), these tests may fail.
	 *
	 * @covers ::wptexturize
	 * @ticket 22692
	 * @dataProvider wptexturize_data_closing_single_quote
	 */
	function test_wptexturize_closing_single_quote( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_closing_single_quote() {
		return array(
			array(
				"word word' word",
				"word word&#8217; word",
			),
			array(
				"word word'. word",
				"word word&#8217;. word",
			),
			array(
				"word word'.word",
				"word word&#8217;.word",
			),
			array(
				"word word', she said",
				"word word&#8217;, she said",
			),
			array(
				"word word'- word",
				"word word&#8217;- word",
			),
			array(
				"word word') word",
				"word word&#8217;) word",
			),
			array(
				"word word'} word",
				"word word&#8217;} word",
			),
			array(
				"word word'] word",
				"word word&#8217;] word",
			),
			array(
				"word word'&gt; word",
				"word word&#8217;&gt; word",
			),
			array(
				"word word'",
				"word word&#8217;",
			),
			array(
				"test sentence'.",
				"test sentence&#8217;.",
			),
			array(
				"test sentence.'",
				"test sentence.&#8217;",
			),
			array(
				"test sentence'. word",
				"test sentence&#8217;. word",
			),
			array(
				"test sentence.' word",
				"test sentence.&#8217; word",
			),
		);
	}

	/**
	 * Tests multiplication.
	 *
	 * Checks all baseline patterns. If anything ever changes in wptexturize(), these tests may fail.
	 *
	 * @covers ::wptexturize
	 * @ticket 22692
	 * @dataProvider wptexturize_data_multiplication
	 */
	function test_wptexturize_multiplication( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_multiplication() {
		return array(
			array(
				"9x9",
				"9&#215;9",
			),
			array(
				"12x34",
				"12&#215;34",
			),
			array(
				"-123x1=-123",
				"-123&#215;1=-123",
			),
			// @ticket 30445
			array(
				"-123x-1",
				"-123x-1",
			),
			array(
				"0.675x1=0.675",
				"0.675&#215;1=0.675",
			),
			array(
				"9 x 9",
				"9 x 9",
			),
			array(
				"0x70",
				"0x70",
			),
			array(
				"3x2x1x0",
				"3x2x1x0",
			),
		);
	}

	/**
	 * Test ampersands. & always becomes &#038; unless it is followed by # or ;
	 *
	 * Checks all baseline patterns. If anything ever changes in wptexturize(), these tests may fail.
	 *
	 * @covers ::wptexturize
	 * @ticket 22692
	 * @dataProvider wptexturize_data_ampersand
	 */
	function test_wptexturize_ampersand( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_ampersand() {
		return array(
			array(
				"word & word",
				"word &#038; word",
			),
			array(
				"word&word",
				"word&#038;word",
			),
			array(
				"word &nbsp; word",
				"word &nbsp; word",
			),
			array(
				"word &#038; word",
				"word &#038; word",
			),
			array(
				"word &#xabc; word",
				"word &#xabc; word",
			),
			array(
				"word &#X394; word",
				"word &#X394; word",
			),
			array(
				"word &# word",
				"word &#038;# word",
			),
			array(
				"word &44; word",
				"word &44; word",
			),
			array(
				"word &&amp; word",
				"word &#038;&amp; word",
			),
			array(
				"word &!amp; word",
				"word &#038;!amp; word",
			),
			array(
				"word &#",
				"word &#038;#",
			),
			array(
				"word &",
				"word &#038;",
			),
		);
	}

	/**
	 * Test "cockney" phrases, which begin with an apostrophe instead of an opening single quote.
	 *
	 * Checks all baseline patterns. If anything ever changes in wptexturize(), these tests may fail.
	 *
	 * @covers ::wptexturize
	 * @ticket 22692
	 * @dataProvider wptexturize_data_cockney
	 */
	function test_wptexturize_cockney( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_cockney() {
		return array(
			array(
				"word 'tain't word",
				"word &#8217;tain&#8217;t word",
			),
			array(
				"word 'twere word",
				"word &#8217;twere word",
			),
			array(
				"word 'twas word",
				"word &#8217;twas word",
			),
			array(
				"word 'tis word",
				"word &#8217;tis word",
			),
			array(
				"word 'twill word",
				"word &#8217;twill word",
			),
			array(
				"word 'til word",
				"word &#8217;til word",
			),
			array(
				"word 'bout word",
				"word &#8217;bout word",
			),
			array(
				"word 'nuff word",
				"word &#8217;nuff word",
			),
			array(
				"word 'round word",
				"word &#8217;round word",
			),
			array(
				"word 'cause word",
				"word &#8217;cause word",
			),
			array(
				"word 'em word",
				"word &#8217;em word",
			),
		);
	}

	/**
	 * Test smart dashes.
	 *
	 * Checks all baseline patterns. If anything ever changes in wptexturize(), these tests may fail.
	 *
	 * @covers ::wptexturize
	 * @ticket 22692
	 * @dataProvider wptexturize_data_smart_dashes
	 */
	function test_wptexturize_smart_dashes( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_smart_dashes() {
		return array(
			array(
				"word --- word",
				"word &#8212; word",
			),
			array(
				"word---word",
				"word&#8212;word",
			),
			array(
				"word -- word",
				"word &#8212; word",
			),
			array(
				"word--word",
				"word&#8211;word",
			),
			array(
				"word - word",
				"word &#8211; word",
			),
			array(
				"word-word",
				"word-word",
			),
			array(
				"word xn&#8211; word",
				"word xn&#8211; word",
			),
			array(
				"wordxn&#8211;word",
				"wordxn&#8211;word",
			),
			array(
				"wordxn--word",
				"wordxn--word",
			),
		);
	}

	/**
	 * Test miscellaneous static replacements.
	 *
	 * Checks all baseline patterns. If anything ever changes in wptexturize(), these tests may fail.
	 *
	 * @covers ::wptexturize
	 * @ticket 22692
	 * @dataProvider wptexturize_data_misc_static_replacements
	 */
	function test_wptexturize_misc_static_replacements( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_misc_static_replacements() {
		return array(
			array(
				"word ... word",
				"word &#8230; word",
			),
			array(
				"word...word",
				"word&#8230;word",
			),
			array(
				"word `` word",
				"word &#8220; word",
			),
			array(
				"word``word",
				"word&#8220;word",
			),
			array(
				"word '' word",
				"word &#8221; word",
			),
			array(
				"word''word",
				"word&#8221;word",
			),
			array(
				"word (tm) word",
				"word &#8482; word",
			),
			array(
				"word (tm)word",
				"word &#8482;word",
			),
			array(
				"word(tm) word",
				"word(tm) word",
			),
			array(
				"word(tm)word",
				"word(tm)word",
			),
		);
	}

	/**
	 * Numbers inside of matching quotes get curly quotes instead of apostrophes and primes.
	 *
	 * @covers ::wptexturize
	 * @ticket 8775
	 * @dataProvider wptexturize_data_quoted_numbers
	 */
	function test_wptexturize_quoted_numbers( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_quoted_numbers() {
		return array(
			array(
				'word "42.00" word',
				'word &#8220;42.00&#8221; word',
			),
			array(
				'word "42.00"word',
				'word &#8220;42.00&#8221;word',
			),
			array(
				"word '42.00' word",
				"word &#8216;42.00&#8217; word",
			),
			array(
				"word '42.00'word",
				"word &#8216;42.00&#8217;word",
			),
			array(
				'word "42" word',
				'word &#8220;42&#8221; word',
			),
			array(
				'word "42,00" word',
				'word &#8220;42,00&#8221; word',
			),
			array(
				'word "4,242.00" word',
				'word &#8220;4,242.00&#8221; word',
			),
			array(
				"word '99's word",
				"word &#8217;99&#8217;s word",
			),
			array(
				"word '99'samsonite",
				"word &#8217;99&#8217;samsonite",
			),
		);
	}

	/**
	 * Quotations should be allowed to have dashes around them.
	 *
	 * @covers ::wptexturize
	 * @ticket 20342
	 * @dataProvider wptexturize_data_quotes_and_dashes
	 */
	function test_wptexturize_quotes_and_dashes( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_quotes_and_dashes() {
		return array(
			array(
				'word---"quote"',
				'word&#8212;&#8220;quote&#8221;',
			),
			array(
				'word--"quote"',
				'word&#8211;&#8220;quote&#8221;',
			),
			array(
				'word-"quote"',
				'word-&#8220;quote&#8221;',
			),
			array(
				"word---'quote'",
				"word&#8212;&#8216;quote&#8217;",
			),
			array(
				"word--'quote'",
				"word&#8211;&#8216;quote&#8217;",
			),
			array(
				"word-'quote'",
				"word-&#8216;quote&#8217;",
			),
			array(
				'"quote"---word',
				'&#8220;quote&#8221;&#8212;word',
			),
			array(
				'"quote"--word',
				'&#8220;quote&#8221;&#8211;word',
			),
			array(
				'"quote"-word',
				'&#8220;quote&#8221;-word',
			),
			array(
				"'quote'---word",
				"&#8216;quote&#8217;&#8212;word",
			),
			array(
				"'quote'--word",
				"&#8216;quote&#8217;&#8211;word",
			),
			array(
				"'quote'-word",
				"&#8216;quote&#8217;-word",
			),
		);
	}

	/**
	 * Test HTML and shortcode avoidance.
	 *
	 * @covers ::wptexturize
	 * @ticket 12690
	 * @dataProvider wptexturize_data_tag_avoidance
	 */
	function test_wptexturize_tag_avoidance( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_tag_avoidance() {
		return array(
			array(
				'[ ... ]',
				'[ &#8230; ]',
			),
			array(
				'[ is it wise to <a title="allow user content ] here? hmm"> maybe </a> ]',
				'[ is it wise to <a title="allow user content ] here? hmm"> maybe </a> ]',
			),
			array(
				'[is it wise to <a title="allow user content ] here? hmm"> maybe </a> ]',
				'[is it wise to <a title="allow user content ] here? hmm"> maybe </a> ]',
			),
			array(
				'[caption - is it wise to <a title="allow user content ] here? hmm"> maybe </a> ]',
				'[caption &#8211; is it wise to <a title="allow user content ] here? hmm"> maybe </a> ]',
			),
			array(
				'[ photos by <a href="http://example.com/?a[]=1&a[]=2"> this guy </a> ]',
				'[ photos by <a href="http://example.com/?a[]=1&#038;a[]=2"> this guy </a> ]',
			),
			array(
				'[photos by <a href="http://example.com/?a[]=1&a[]=2"> this guy </a>]',
				'[photos by <a href="http://example.com/?a[]=1&#038;a[]=2"> this guy </a>]',
			),
			array(
				'[gallery ...]',
				'[gallery ...]',
			),
			array(
				'[[gallery ...]', // This tag is still valid.
				'[[gallery ...]',
			),
			array(
				'[gallery ...]]', // This tag is also valid.
				'[gallery ...]]',
			),
			array(
				'[/gallery ...]', // This would actually be ignored by the shortcode system.  The decision to not texturize it is intentional, if not correct.
				'[/gallery ...]',
			),
			array(
				'[[gallery]]...[[/gallery]]', // Shortcode parsing will ignore the inner ]...[ part and treat this as a single escaped shortcode.
				'[[gallery]]&#8230;[[/gallery]]',
			),
			array(
				'[[[gallery]]]...[[[/gallery]]]', // Again, shortcode parsing matches, but only the [[gallery] and [/gallery]] parts.
				'[[[gallery]]]&#8230;[[[/gallery]]]',
			),
			array(
				'[gallery ...',
				'[gallery &#8230;',
			),
			array(
				'[gallery <br ... /> ...]', // This tag is still valid. Shortcode 'attributes' are not considered in the initial parsing of shortcodes, and HTML is allowed.
				'[gallery <br ... /> ...]',
			),
			array(
				'<br [gallery ...] ... />',
				'<br [gallery ...] ... />',
			),
			array(
				'<br [gallery ...] ... /',
				'<br [gallery ...] &#8230; /',
			),
			array(
				'<br ... />',
				'<br ... />',
			),
			array(
				'<br ... />...<br ... />',
				'<br ... />&#8230;<br ... />',
			),
			array(
				'[gallery ...]...[gallery ...]',
				'[gallery ...]&#8230;[gallery ...]',
			),
			array(
				'[[gallery ...]]',
				'[[gallery ...]]',
			),
			array(
				'[[gallery ...]',
				'[[gallery ...]',
			),
			array(
				'[gallery ...]]',
				'[gallery ...]]',
			),
			array(
				'[/gallery ...]]',
				'[/gallery ...]]',
			),
			array(
				'[[gallery <br ... /> ...]]', // This gets parsed as an escaped shortcode with embedded HTML.  Brains may explode.
				'[[gallery <br ... /> ...]]',
			),
			array(
				'<br [[gallery ...]] ... />',
				'<br [[gallery ...]] ... />',
			),
			array(
				'<br [[gallery ...]] ... /',
				'<br [[gallery ...]] &#8230; /',
			),
			array(
				'[[gallery ...]]...[[gallery ...]]',
				'[[gallery ...]]&#8230;[[gallery ...]]',
			),
			array(
				'[[gallery ...]...[/gallery]]',
				'[[gallery ...]&#8230;[/gallery]]',
			),
			array(
				'<!-- ... -->',
				'<!-- ... -->',
			),
			array(
				'<!--...-->',
				'<!--...-->',
			),
			array(
				'<!-- ... -- > ...',
				'<!-- ... -- > ...',
			),
			array(
				'<!-- ...', // An unclosed comment is still a comment.
				'<!-- ...',
			),
			array(
				'a<!-->b', // Browsers seem to allow this.
				'a<!-->b',
			),
			array(
				'a<!--->b',
				'a<!--->b',
			),
			array(
				'a<!---->b',
				'a<!---->b',
			),
			array(
				'a<!----->b',
				'a<!----->b',
			),
			array(
				'a<!-- c --->b',
				'a<!-- c --->b',
			),
			array(
				'a<!-- c -- d -->b',
				'a<!-- c -- d -->b',
			),
			array(
				'a<!-- <!-- c --> -->b<!-- close -->',
				'a<!-- <!-- c --> &#8211;>b<!-- close -->',
			),
			array(
				'<!-- <br /> [gallery] ... -->',
				'<!-- <br /> [gallery] ... -->',
			),
			array(
				'...<!-- ... -->...',
				'&#8230;<!-- ... -->&#8230;',
			),
			array(
				'[gallery ...]...<!-- ... -->...<br ... />',
				'[gallery ...]&#8230;<!-- ... -->&#8230;<br ... />',
			),
			array(
				'<ul><li>Hello.</li><!--<li>Goodbye.</li>--></ul>',
				'<ul><li>Hello.</li><!--<li>Goodbye.</li>--></ul>',
			),
			array(
				'word <img src="http://example.com/wp-content/uploads/2014/06/image-300x216.gif" /> word', // Ensure we are not corrupting image URLs.
				'word <img src="http://example.com/wp-content/uploads/2014/06/image-300x216.gif" /> word',
			),
			array(
				'[ do texturize "[quote]" here ]',
				'[ do texturize &#8220;[quote]&#8221; here ]',
			),
			array(
				'[ regex catches this <a href="[quote]">here</a> ]',
				'[ regex catches this <a href="[quote]">here</a> ]',
			),
			array(
				'[ but also catches the <b>styled "[quote]" here</b> ]',
				'[ but also catches the <b>styled &#8220;[quote]&#8221; here</b> ]',
			),
			array(
				'[Let\'s get crazy<input>[caption code="<a href=\'?a[]=100\'>hello</a>"]</input>world]', // caption shortcode is invalid here because it contains [] chars.
				'[Let&#8217;s get crazy<input>[caption code=&#8221;<a href=\'?a[]=100\'>hello</a>&#8220;]</input>world]',
			),
		);
	}

	/**
	 * Year abbreviations consist of exactly two digits.
	 *
	 * @covers ::wptexturize
	 * @ticket 26850
	 * @dataProvider wptexturize_data_year_abbr
	 */
	function test_wptexturize_year_abbr( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_year_abbr() {
		return array(
			array(
				"word '99 word",
				"word &#8217;99 word",
			),
			array(
				"word '99. word",
				"word &#8217;99. word",
			),
			array(
				"word '99, word",
				"word &#8217;99, word",
			),
			array(
				"word '99; word",
				"word &#8217;99; word",
			),
			array(
				"word '99' word", // For this pattern, prime doesn't make sense.  Should get apos and a closing quote.
				"word &#8217;99&#8217; word",
			),
			array(
				"word '99'. word",
				"word &#8217;99&#8217;. word",
			),
			array(
				"word '99', word",
				"word &#8217;99&#8217;, word",
			),
			array(
				"word '99.' word",
				"word &#8217;99.&#8217; word",
			),
			array(
				"word '99",
				"word &#8217;99",
			),
			array(
				"'99 word",
				"&#8217;99 word",
			),
			array(
				"word '999 word", // Does not match the apos pattern, should be opening quote.
				"word &#8216;999 word",
			),
			array(
				"word '99% word",
				"word &#8216;99% word",
			),
			array(
				"word '9 word",
				"word &#8216;9 word",
			),
			array(
				"word '99.9 word",
				"word &#8216;99.9 word",
			),
			array(
				"word '999",
				"word &#8216;999",
			),
			array(
				"word '9",
				"word &#8216;9",
			),
			array(
				"in '4 years, 3 months,' Obama cut the deficit",
				"in &#8216;4 years, 3 months,&#8217; Obama cut the deficit",
			),
			array(
				"testing's '4' through 'quotes'",
				"testing&#8217;s &#8216;4&#8217; through &#8216;quotes&#8217;",
			),
		);
	}

	/**
	 * Make sure translation actually works.
	 *
	 * Also make sure apostrophes and closing quotes aren't being confused by default.
	 *
	 * @covers ::wptexturize
	 * @ticket 27426
	 * @dataProvider wptexturize_data_translate
	 */
	function test_wptexturize_translate( $input, $output ) {
		add_filter( 'gettext_with_context', array( $this, 'wptexturize_filter_translate' ), 10, 4 );

		$result = wptexturize( $input, true );

		remove_filter( 'gettext_with_context', array( $this, 'wptexturize_filter_translate' ), 10, 4 );
		wptexturize( 'reset', true );

		return $this->assertEquals( $output, $result );
	}

	function wptexturize_filter_translate( $translations, $text, $context, $domain ) {
		switch ($text) {
			case '&#8211;' : return '!endash!';
			case '&#8212;' : return '!emdash!';
			case '&#8216;' : return '!openq1!';
			case '&#8217;' :
				if ( 'apostrophe' == $context ) {
					return '!apos!';
				} else {
					return '!closeq1!';
				}
			case '&#8220;' : return '!openq2!';
			case '&#8221;' : return '!closeq2!';
			case '&#8242;' : return '!prime1!';
			case '&#8243;' : return '!prime2!';
			default : return $translations;
		}
	}

	function wptexturize_data_translate() {
		return array(
			array(
				"word '99 word",
				"word !apos!99 word",
			),
			array(
				"word'99 word",
				"word!apos!99 word",
			),
			array(
				"word 'test sentence' word",
				"word !openq1!test sentence!closeq1! word",
			),
			array(
				"'test sentence'",
				"!openq1!test sentence!closeq1!",
			),
			array(
				'word "test sentence" word',
				'word !openq2!test sentence!closeq2! word',
			),
			array(
				'"test sentence"',
				'!openq2!test sentence!closeq2!',
			),
			array(
				"word 'word word",
				"word !openq1!word word",
			),
			array(
				"word ('word word",
				"word (!openq1!word word",
			),
			array(
				"word ['word word",
				"word [!openq1!word word",
			),
			array(
				'word 99" word',
				'word 99!prime2! word',
			),
			array(
				'word 99"word',
				'word 99!prime2!word',
			),
			array(
				'word99" word',
				'word99!prime2! word',
			),
			array(
				'word99"word',
				'word99!prime2!word',
			),
			array(
				"word 99' word",
				"word 99!prime1! word",
			),
			array(
				"word99' word",
				"word99!prime1! word",
			),
			array(
				"word word's word",
				"word word!apos!s word",
			),
			array(
				"word word'. word",
				"word word!closeq1!. word",
			),
			array(
				"word ]'. word",
				"word ]!closeq1!. word",
			),
			array(
				'word "word word',
				'word !openq2!word word',
			),
			array(
				'word ("word word',
				'word (!openq2!word word',
			),
			array(
				'word ["word word',
				'word [!openq2!word word',
			),
			array(
				'word word" word',
				'word word!closeq2! word',
			),
			array(
				'word word") word',
				'word word!closeq2!) word',
			),
			array(
				'word word"] word',
				'word word!closeq2!] word',
			),
			array(
				'word word"',
				'word word!closeq2!',
			),
			array(
				'word word"word',
				'word word!closeq2!word',
			),
			array(
				'test sentence".',
				'test sentence!closeq2!.',
			),
			array(
				'test sentence."',
				'test sentence.!closeq2!',
			),
			array(
				'test sentence." word',
				'test sentence.!closeq2! word',
			),
			array(
				"word word' word",
				"word word!closeq1! word",
			),
			array(
				"word word'. word",
				"word word!closeq1!. word",
			),
			array(
				"word word'.word",
				"word word!closeq1!.word",
			),
			array(
				"word word'",
				"word word!closeq1!",
			),
			array(
				"test sentence'.",
				"test sentence!closeq1!.",
			),
			array(
				"test sentence.'",
				"test sentence.!closeq1!",
			),
			array(
				"test sentence'. word",
				"test sentence!closeq1!. word",
			),
			array(
				"test sentence.' word",
				"test sentence.!closeq1! word",
			),
			array(
				"word 'tain't word",
				"word !apos!tain!apos!t word",
			),
			array(
				"word 'twere word",
				"word !apos!twere word",
			),
			array(
				'word "42.00" word',
				'word !openq2!42.00!closeq2! word',
			),
			array(
				"word '42.00' word",
				"word !openq1!42.00!closeq1! word",
			),
			array(
				"word word'. word",
				"word word!closeq1!. word",
			),
			array(
				"word word'.word",
				"word word!closeq1!.word",
			),
			array(
				"word word', she said",
				"word word!closeq1!, she said",
			),
		);
	}

	/**
	 * Extra sanity checks for _wptexturize_pushpop_element()
	 *
	 * @covers ::wptexturize
	 * @ticket 28483
	 * @dataProvider wptexturize_data_element_stack
	 */
	function test_wptexturize_element_stack( $input, $output ) {
		return $this->assertEquals( $output, wptexturize( $input ) );
	}

	function wptexturize_data_element_stack() {
		return array(
			array(
				'<span>hello</code>---</span>',
				'<span>hello</code>&#8212;</span>',
			),
			array(
				'</code>hello<span>---</span>',
				'</code>hello<span>&#8212;</span>',
			),
			array(
				'<code>hello</code>---</span>',
				'<code>hello</code>&#8212;</span>',
			),
			array(
				'<span>hello</span>---<code>',
				'<span>hello</span>&#8212;<code>',
			),
			array(
				'<span>hello<code>---</span>',
				'<span>hello<code>---</span>',
			),
			array(
				'<code>hello<span>---</span>',
				'<code>hello<span>---</span>',
			),
			array(
				'<code>hello</span>---</span>',
				'<code>hello</span>---</span>',
			),
			array(
				'<span><code>hello</code>---</span>',
				'<span><code>hello</code>&#8212;</span>',
			),
			array(
				'<code>hello</code>world<span>---</span>',
				'<code>hello</code>world<span>&#8212;</span>',
			),
		);
	}

	/**
	 * Test disabling shortcode texturization.
	 *
	 * @covers ::wptexturize
	 * @ticket 29557
	 * @dataProvider wptexturize_data_unregistered_shortcodes
	 */
	function test_wptexturize_unregistered_shortcodes( $input, $output ) {
		add_filter( 'no_texturize_shortcodes', array( $this, 'wptexturize_filter_shortcodes' ), 10, 1 );
	
		$output = $this->assertEquals( $output, wptexturize( $input ) );
	
		remove_filter( 'no_texturize_shortcodes', array( $this, 'wptexturize_filter_shortcodes' ), 10, 1 );
		return $output;
	}
	
	function wptexturize_filter_shortcodes( $disabled ) {
		$disabled[] = 'audio';
		return $disabled;
	}

	function wptexturize_data_unregistered_shortcodes() {
		return array(
			array(
				'[a]a--b[audio]---[/audio]a--b[/a]',
				'[a]a&#8211;b[audio]---[/audio]a&#8211;b[/a]',
			),
			array(
				'[code ...]...[/code]', // code is not a registered shortcode.
				'[code &#8230;]&#8230;[/code]',
			),
			array(
				'[hello ...]...[/hello]', // hello is not a registered shortcode.
				'[hello &#8230;]&#8230;[/hello]',
			),
			array(
				'[...]...[/...]', // These are potentially usable shortcodes.
				'[&#8230;]&#8230;[/&#8230;]',
			),
			array(
				'[gal>ery ...]',
				'[gal>ery &#8230;]',
			),
			array(
				'[randomthing param="test"]',
				'[randomthing param=&#8221;test&#8221;]',
			),
			array(
				'[[audio]...[/audio]...', // These are potentially usable shortcodes.  Unfortunately, the meaning of [[audio] is ambiguous unless we run the entire shortcode regexp.
				'[[audio]&#8230;[/audio]&#8230;',
			),
			array(
				'[audio]...[/audio]]...', // These are potentially usable shortcodes.  Unfortunately, the meaning of [/audio]] is ambiguous unless we run the entire shortcode regexp.
				'[audio]...[/audio]]...', // This test would not pass in 3.9 because the extra brace was always ignored by texturize.
			),
			array(
				'<span>hello[/audio]---</span>',
				'<span>hello[/audio]&#8212;</span>',
			),
			array(
				'[/audio]hello<span>---</span>',
				'[/audio]hello<span>&#8212;</span>',
			),
			array(
				'[audio]hello[/audio]---</span>',
				'[audio]hello[/audio]&#8212;</span>',
			),
			array(
				'<span>hello</span>---[audio]',
				'<span>hello</span>&#8212;[audio]',
			),
			array(
				'<span>hello[audio]---</span>',
				'<span>hello[audio]---</span>',
			),
			array(
				'[audio]hello<span>---</span>',
				'[audio]hello<span>---</span>',
			),
			array(
				'[audio]hello</span>---</span>',
				'[audio]hello</span>---</span>',
			),
		);
	}

	/**
	 * @covers ::wp_trim_excerpt
	 * @ticket 25349
	 */
	public function test_wp_trim_excerpt_secondary_loop_respect_more() {
		$post1 = $this->factory->post->create( array(
			'post_content' => 'Post 1 Page 1<!--more-->Post 1 Page 2',
		) );
		$post2 = $this->factory->post->create( array(
			'post_content' => 'Post 2 Page 1<!--more-->Post 2 Page 2',
		) );

		$this->go_to( '/?p=' . $post1 );
		setup_postdata( get_post( $post1 ) );

		$q = new WP_Query( array(
			'post__in' => array( $post2 ),
		) );
		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$this->assertSame( 'Post 2 Page 1', wp_trim_excerpt() );
			}
		}
	}

	/**
	 * @covers ::wp_trim_excerpt
	 * @ticket 25349
	 */
	public function test_wp_trim_excerpt_secondary_loop_respect_nextpage() {
		$post1 = $this->factory->post->create( array(
			'post_content' => 'Post 1 Page 1<!--nextpage-->Post 1 Page 2',
		) );
		$post2 = $this->factory->post->create( array(
			'post_content' => 'Post 2 Page 1<!--nextpage-->Post 2 Page 2',
		) );

		$this->go_to( '/?p=' . $post1 );
		setup_postdata( get_post( $post1 ) );

		$q = new WP_Query( array(
			'post__in' => array( $post2 ),
		) );
		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$this->assertSame( 'Post 2 Page 1', wp_trim_excerpt() );
			}
		}
	}

	private $wp_trim_words_long_text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce varius lacinia vehicula. Etiam sapien risus, ultricies ac posuere eu, convallis sit amet augue. Pellentesque urna massa, lacinia vel iaculis eget, bibendum in mauris. Aenean eleifend pulvinar ligula, a convallis eros gravida non. Suspendisse potenti. Pellentesque et odio tortor. In vulputate pellentesque libero, sed dapibus velit mollis viverra. Pellentesque id urna euismod dolor cursus sagittis.';

	/**
	 * @covers ::wp_trim_words
	 */
	function test_wp_trim_words_trims_to_55_by_default() {
		$trimmed = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce varius lacinia vehicula. Etiam sapien risus, ultricies ac posuere eu, convallis sit amet augue. Pellentesque urna massa, lacinia vel iaculis eget, bibendum in mauris. Aenean eleifend pulvinar ligula, a convallis eros gravida non. Suspendisse potenti. Pellentesque et odio tortor. In vulputate pellentesque libero, sed dapibus velit&hellip;';
		$this->assertEquals( $trimmed, wp_trim_words( $this->wp_trim_words_long_text ) );
	}

	/**
	 * @covers ::wp_trim_words
	 */
	function test_wp_trim_words_trims_to_10() {
		$trimmed = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce varius&hellip;';
		$this->assertEquals( $trimmed, wp_trim_words( $this->wp_trim_words_long_text, 10 ) );
	}

	/**
	 * @covers ::wp_trim_words
	 */
	function test_wp_trim_words_trims_to_5_and_uses_custom_more() {
		$trimmed = 'Lorem ipsum dolor sit amet,[...] Read on!';
		$this->assertEquals( $trimmed, wp_trim_words( $this->wp_trim_words_long_text, 5, '[...] Read on!' ) );
	}

	/**
	 * @covers ::wp_trim_words
	 */
	function test_wp_trim_words_strips_tags_before_trimming() {
		$text = 'This text contains a <a href="http://wordpress.org"> link </a> to WordPress.org!';
		$trimmed = 'This text contains a link&hellip;';
		$this->assertEquals( $trimmed, wp_trim_words( $text, 5 ) );
	}

	/**
	 * @covers ::wp_trim_words
	 * @ticket 18726
	 */
	function test_wp_trim_words_strips_script_and_style_content() {
		$trimmed = 'This text contains. It should go.';

		$text = 'This text contains<script>alert(" Javascript");</script>. It should go.';
		$this->assertEquals( $trimmed, wp_trim_words( $text ) );

		$text = 'This text contains<style>#css { width:expression(alert("css")) }</style>. It should go.';
		$this->assertEquals( $trimmed, wp_trim_words( $text ) );
	}

	/**
	 * @covers ::wp_trim_words
	 */
	function test_wp_trim_words_doesnt_trim_short_text() {
		$text = 'This is some short text.';
		$this->assertEquals( $text, wp_trim_words( $text ) );
	}

	/**
	 * @covers ::zeroise
	 */
	function test_pads_with_leading_zeroes() {
		$this->assertEquals("00005", zeroise(5, 5));
	}

	/**
	 * @covers ::zeroise
	 */
	function test_does_nothing_if_input_is_already_longer() {
		$this->assertEquals("5000000", zeroise(5000000, 2));
	}

}
