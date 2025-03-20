<?php
/**
 * Title: Photo blog archive
 * Slug: blank-theme/template-archive-photo-blog
 * Template Types: archive
 * Viewport width: 1400
 * Inserter: no
 *
 * @package Blank-Theme
 */

?>

<!-- wp:template-part {"slug":"header"} /-->

<!-- wp:group {"tagName":"main","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="margin-top:var(--wp--preset--spacing--60)">
	<!-- wp:query-title {"type":"archive","textAlign":"center"} /-->
	<!-- wp:term-description {"textAlign":"center"} /-->
	<!-- wp:pattern {"slug":"blank-theme/template-query-loop-photo-blog"} /-->
</main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer"} /-->
