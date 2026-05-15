<?php
/**
 * Plugin Name: Social Sharing Block
 * Description: Just add "share" to the classes of a social links block with permalink context (such as in an FSE single post template).
 * Version: 1.0.2
 */

class SocialSharingBlock {

	public function __construct()
	{
		add_filter( 'render_block_data', [ $this, 'add_share_url'], 10, 3 );
	}

	public function add_share_url( array $parsed_block, array $source_block, \WP_Block|null $parent_block ) {
		
		/**
		 * Skip non-social-link blocks.
		 */
		if($parsed_block['blockName'] !== 'core/social-link') {
			return $parsed_block;
		}

		/**
		 * Check for context and trigger class ("share")
		 */
		if ( is_null($parent_block)
			|| $parent_block->name !== 'core/social-links'
			|| strpos( $parent_block->attributes['className'], 'share' ) === false
		) {
			return $parsed_block;
		}

		/**
		 * If there's no permalink, there's nothing to share.
		 */
		if(!get_permalink()) {
			return $parsed_block;
		}

		$service = $parsed_block['attrs']['service'] ?? '';
		$url = get_permalink();
		$parsed_block['attrs']['url'] = $this->get_share_url( $service, $url );
		return $parsed_block;
	}

	/**
	 * Get the share URL for a given service and page URL.
	 */
	private function get_share_url( string $service, string $url ) {
		switch ( $service ) {
			case 'facebook':
				return 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode( $url );
			case 'x':
			case 'twitter':
				return 'https://twitter.com/intent/tweet?url=' . urlencode( $url );
			case 'linkedin':
				return 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode( $url );
			default:
				/**
				 * Don't show an icon if sharing is not configured for this service.
				 */
				return '';
		}
	}

	public function register_block() {
		register_block_type( __DIR__ . '/build/social-links' );
		register_block_type( __DIR__ . '/build/social-link' );
	}
}

new SocialSharingBlock();

/**
 * WIP
 * Improved UI with dedicated blocks or variations.
 * Use the same interface as the core social links block but preset the URL behind-the-scenes.
 */
// add_filter( 'get_block_type_variations', 'social_share_block_variation', 10, 2 );

function social_share_block_variation( $variations, $block_type ) {

	if ( 'core/social-links' === $block_type->name ) {
		$variations[] = [
			'name'       => 'social-share-block/share-links',
			'title'      => __( 'Social Share Links', 'social_share_block' ),
			'isActive'   => ['className'],
			'isDefault'  => false,
			'attributes' => [
				'className' => 'is-style-social-share-links',
			],
			'allowedBlocks' => [ "social-share-block/share-link" ],
		];
	}

	if( 'core/social-link' === $block_type->name ) {
		$variations[] = [
			'name'       => 'social-share-block/share-link',
			'title'      => __( 'Social Share Link', 'social_share_block' ),
			'isActive'   => ['className'],
			'isDefault'  => false,
			'attributes' => [
				'className' => 'is-style-social-share-link',
				'url' => get_permalink(),
			]
		];
	}

	return $variations;
}